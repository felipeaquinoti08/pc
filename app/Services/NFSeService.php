<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Setting;
use Hadder\NfseNacional\Dps;
use Hadder\NfseNacional\Tools;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use NFePHP\Common\Certificate;

/**
 * Emissão manual de NFS-e (Ambiente Nacional / SEFIN) para uma fatura, via
 * hadder/nfse-nacional. O certificado A1 fica só no Azure Blob (nunca em
 * disco local) e é lido direto pra memória a cada emissão.
 *
 * Conhecido incompleto por enquanto: o endereço do tomador (obrigatório em
 * alguns municípios) não é enviado porque não temos o código IBGE do
 * município do cliente cadastrado - só cidade/UF em texto livre. Se a
 * homologação rejeitar por endereço do tomador ausente, esse é o próximo
 * campo a adicionar em Customer.
 */
class NFSeService
{
    public function issue(Invoice $invoice, ?\Illuminate\Support\Carbon $competencia = null): void
    {
        $setting = Setting::current();

        if (! $setting->nfseConfigured()) {
            throw new \RuntimeException('Configuração de Nota Fiscal incompleta. Preencha Configurações → Nota Fiscal antes de emitir.');
        }

        $customer = $invoice->customer;

        if (! $customer || blank($customer->document_number)) {
            throw new \RuntimeException('O cliente desta fatura não tem CPF/CNPJ cadastrado.');
        }

        try {
            $tools = $this->buildTools($setting);

            $std = $this->buildDps($invoice, $customer, $setting, $competencia);
            $dps = new Dps($std);

            $response = $tools->enviaDps($dps->render());

            if (! is_array($response) || isset($response['erro']) || ! isset($response['nfseXmlGZipB64'])) {
                $invoice->update([
                    'nfse_status' => Invoice::NFSE_ERROR,
                    'nfse_error_message' => $this->extractErrorMessage($response, $tools),
                ]);

                return;
            }

            $xml = gzdecode(base64_decode($response['nfseXmlGZipB64']));
            $chave = $response['chaveAcesso'] ?? null;
            $xmlPath = "nfse/invoices/{$invoice->id}/nfse-{$chave}.xml";

            Storage::disk('azure')->put($xmlPath, $xml);

            $invoice->update([
                'nfse_status' => Invoice::NFSE_ISSUED,
                'nfse_chave_acesso' => $chave,
                'nfse_numero' => $this->extractNfseNumber($xml),
                'nfse_xml_path' => $xmlPath,
                'nfse_pdf_path' => $this->fetchAndStoreDanfse($tools, $invoice, $chave),
                'nfse_error_message' => null,
                'nfse_issued_at' => now(),
            ]);

            // Cada DPS precisa de um nDPS sequencial único - avança o
            // contador só depois de confirmar sucesso, pra um erro não
            // "queimar" um número que nunca chegou a ser usado.
            $setting->increment('nfse_next_dps_number');
        } catch (\Throwable $e) {
            Log::warning("NFSe: falha ao emitir nota para a fatura {$invoice->id}: {$e->getMessage()}");

            $invoice->update([
                'nfse_status' => Invoice::NFSE_ERROR,
                'nfse_error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Re-tries fetching the DANFSe PDF for a note that's already issued -
     * the ADN (government PDF endpoint) is flaky enough that "Emitir Nota
     * Fiscal" succeeding (XML in hand) doesn't guarantee the PDF came with
     * it. Only throws when there's no issued note to fetch a PDF for; an
     * ADN failure just returns false (see fetchAndStoreDanfse), same as
     * during issue().
     */
    public function refreshPdf(Invoice $invoice): bool
    {
        if ($invoice->nfse_status !== Invoice::NFSE_ISSUED || blank($invoice->nfse_chave_acesso)) {
            throw new \RuntimeException('Esta fatura ainda não tem uma Nota Fiscal emitida.');
        }

        $setting = Setting::current();
        $tools = $this->buildTools($setting);

        // O ADN não só devolve 503 de vez em quando - às vezes simplesmente
        // não responde, e o padrão do pacote (30s de conexão + 30s de
        // timeout, por chamada, com até 2 chamadas em sequência no
        // fallback) deixa a tela "Buscar PDF" travada por até ~1 minuto.
        // Como já existe o upload manual como alternativa, vale falhar
        // rápido aqui em vez de segurar o usuário esperando.
        $tools->setConnectionTimeout(8);
        $tools->setTimeout(8);

        $path = $this->fetchAndStoreDanfse($tools, $invoice, $invoice->nfse_chave_acesso);

        if (blank($path)) {
            return false;
        }

        $invoice->update(['nfse_pdf_path' => $path]);

        return true;
    }

    /**
     * Fallback manual pro "Buscar PDF": o endpoint do ADN (adn.nfse.gov.br)
     * às vezes fica de fato indisponível (503) por um tempo bem maior do que
     * vale a pena o usuário ficar tentando de novo, mesmo com a nota já
     * emitida e visível/baixável na hora pelo portal humano
     * (nfse.gov.br/EmissorNacional). Deixa anexar aqui o mesmo PDF baixado
     * de lá, guardado no mesmo lugar que fetchAndStoreDanfse() usaria.
     */
    public function storeManualPdf(Invoice $invoice, \Illuminate\Http\UploadedFile $file): void
    {
        if ($invoice->nfse_status !== Invoice::NFSE_ISSUED || blank($invoice->nfse_chave_acesso)) {
            throw new \RuntimeException('Esta fatura ainda não tem uma Nota Fiscal emitida.');
        }

        $pdfPath = "nfse/invoices/{$invoice->id}/nfse-{$invoice->nfse_chave_acesso}.pdf";
        Storage::disk('azure')->put($pdfPath, file_get_contents($file->getRealPath()));

        $invoice->update(['nfse_pdf_path' => $pdfPath]);
    }

    /**
     * Reachability probe for the government's own two endpoints - SEFIN
     * (emissão/consulta) and ADN (DANFSe) - used by
     * App\Services\SystemHealthChecker to feed the dashboard's "Status do
     * Sistema" card. Reuses the same certificate/HTTP path as real usage
     * (a raw unauthenticated request to these hosts may behave completely
     * differently), just against a syntactically-valid but nonexistent
     * chave - any HTTP response below 500 means the endpoint itself is up,
     * regardless of what it says about this specific (fake) chave.
     *
     * @return array{sefin: array{ok: bool, message: string}, adn: array{ok: bool, message: string}}
     */
    public function checkHealth(Setting $setting): array
    {
        $tools = $this->buildTools($setting);
        $tools->setConnectionTimeout(6);
        $tools->setTimeout(6);

        return [
            'sefin' => $this->pingEndpoint($tools, 'consultar_nfse', 1),
            'adn' => $this->pingEndpoint($tools, 'consultar_danfse', 2),
        ];
    }

    private function pingEndpoint(Tools $tools, string $operationKey, int $origem): array
    {
        $fakeChave = str_repeat('0', 50);
        $operacao = str_replace('{chave}', $fakeChave, $tools->getOperation($operationKey));

        try {
            $tools->getData($operacao, null, $origem);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }

        $httpCode = (int) ($tools->soapinfo['http_code'] ?? 0);

        if ($httpCode === 0) {
            return ['ok' => false, 'message' => 'Sem resposta (timeout/conexão)'];
        }

        if ($httpCode >= 500) {
            return ['ok' => false, 'message' => "HTTP {$httpCode}"];
        }

        return ['ok' => true, 'message' => "HTTP {$httpCode}"];
    }

    private function buildTools(Setting $setting): Tools
    {
        $pfxContent = Storage::disk('azure')->get($setting->nfse_certificate_path);
        $cert = Certificate::readPfx($pfxContent, $setting->nfse_certificate_password);

        $config = json_encode(['tpamb' => $setting->nfseIsProduction() ? 1 : 2]);

        return new Tools($config, $cert);
    }

    private function buildDps(Invoice $invoice, Customer $customer, Setting $setting, ?\Illuminate\Support\Carbon $competencia = null): \stdClass
    {
        $std = new \stdClass();
        $std->infDPS = new \stdClass();
        $std->infDPS->tpAmb = $setting->nfseIsProduction() ? 1 : 2;
        $std->infDPS->dhEmi = now()->format('Y-m-d\TH:i:sP');
        // TSVerAplic has a max length the government schema enforces
        // (E1235 rejects anything longer) - keep this short.
        $std->infDPS->verAplic = 'PainelCentral_1.0';
        $std->infDPS->serie = $setting->nfse_dps_serie;
        $std->infDPS->nDPS = (string) $setting->nfse_next_dps_number;
        // dCompet é o período do serviço prestado (um mês antes do
        // reference_month/year da fatura - a fatura cobra o ciclo em
        // avanço, mas o serviço que ela cobre de fato é o mês anterior, ver
        // InvoiceGenerator::serviceDescription()), não o ciclo de cobrança
        // em si nem o mês em que a nota está sendo gerada. Pode ser
        // ajustado manualmente na emissão (tela de fatura) pra casos
        // pontuais.
        $std->infDPS->dCompet = ($competencia ?? $invoice->servicePeriod())->format('Y-m-d');
        $std->infDPS->tpEmit = 1; // 1 - Prestador
        $std->infDPS->cLocEmi = $setting->nfse_municipio_codigo;

        $std->infDPS->prest = new \stdClass();
        $std->infDPS->prest->CNPJ = $this->onlyDigits($setting->nfse_cnpj);

        if (filled($setting->nfse_inscricao_municipal)) {
            $std->infDPS->prest->IM = $setting->nfse_inscricao_municipal;
        }
        if (filled($setting->nfse_telefone)) {
            $std->infDPS->prest->fone = $this->onlyDigits($setting->nfse_telefone);
        }
        if (filled($setting->nfse_email)) {
            $std->infDPS->prest->email = $setting->nfse_email;
        }

        $std->infDPS->prest->regTrib = new \stdClass();
        $std->infDPS->prest->regTrib->opSimpNac = (int) $setting->nfse_op_simples_nacional;
        $std->infDPS->prest->regTrib->regEspTrib = (int) $setting->nfse_reg_esp_trib;

        $std->infDPS->toma = new \stdClass();
        $document = $this->onlyDigits($customer->document_number);

        if ($customer->document_type === 'cnpj') {
            $std->infDPS->toma->CNPJ = $document;
        } else {
            $std->infDPS->toma->CPF = $document;
        }

        $std->infDPS->toma->xNome = $customer->name;

        if (filled($customer->email)) {
            $std->infDPS->toma->email = $customer->email;
        }

        // Endereço do tomador - confirmado necessário numa emissão real
        // (o Emissor Nacional exige/envia isso). Só monta o bloco quando
        // temos o código IBGE cadastrado (Customer::nfse_municipio_codigo);
        // sem ele não dá pra montar endNac.cMun corretamente.
        if (filled($customer->address) && filled($customer->nfse_municipio_codigo)) {
            $std->infDPS->toma->end = new \stdClass();
            $std->infDPS->toma->end->xLgr = $customer->address;
            $std->infDPS->toma->end->nro = $customer->no_number ? 'S/N' : ($customer->number ?: 'S/N');

            if (filled($customer->complement)) {
                $std->infDPS->toma->end->xCpl = $customer->complement;
            }

            $std->infDPS->toma->end->xBairro = $customer->neighborhood ?: 'Não informado';

            $std->infDPS->toma->end->endNac = new \stdClass();
            $std->infDPS->toma->end->endNac->cMun = $customer->nfse_municipio_codigo;
            $std->infDPS->toma->end->endNac->CEP = $this->onlyDigits($customer->postal_code);
        }

        $std->infDPS->serv = new \stdClass();
        $std->infDPS->serv->locPrest = new \stdClass();
        $std->infDPS->serv->locPrest->cLocPrestacao = $setting->nfse_municipio_codigo;

        $std->infDPS->serv->cServ = new \stdClass();
        $std->infDPS->serv->cServ->cTribNac = $setting->nfse_codigo_tributacao_nacional;

        if (filled($setting->nfse_codigo_tributacao_municipal)) {
            $std->infDPS->serv->cServ->cTribMun = $setting->nfse_codigo_tributacao_municipal;
        }
        if (filled($setting->nfse_codigo_nbs)) {
            $std->infDPS->serv->cServ->cNBS = $setting->nfse_codigo_nbs;
        }

        // Reaproveita a mesma descrição/variáveis já resolvidas pra fatura
        // (BillingPolicy::notes com {{competencia}}/{{vencimento}}, ou o
        // texto automático - ver InvoiceGenerator::serviceDescription()).
        // Quebras de linha viram entidades &#13;/&#10; no XML e já foram
        // relatadas causando E0714 ("erro na assinatura") em outra
        // implementação - achata pra uma linha só, sem perder o conteúdo.
        $description = $invoice->notes ?: $invoice->billingPolicy?->label() ?: 'Prestação de serviços de TI';
        $std->infDPS->serv->cServ->xDescServ = trim(preg_replace('/\s*[\r\n]+\s*/', ' | ', $description));

        $amount = (float) $invoice->amount;

        $std->infDPS->valores = new \stdClass();
        $std->infDPS->valores->vServPrest = new \stdClass();
        $std->infDPS->valores->vServPrest->vServ = number_format($amount, 2, '.', '');

        $std->infDPS->valores->trib = new \stdClass();
        $std->infDPS->valores->trib->tribMun = new \stdClass();
        $std->infDPS->valores->trib->tribMun->tribISSQN = 1; // Operação tributável
        $std->infDPS->valores->trib->tribMun->tpRetISSQN = 1; // Não retido

        if (filled($setting->nfse_aliquota_pis) || filled($setting->nfse_aliquota_cofins)) {
            $pAliqPis = (float) ($setting->nfse_aliquota_pis ?? 0);
            $pAliqCofins = (float) ($setting->nfse_aliquota_cofins ?? 0);

            $std->infDPS->valores->trib->tribFed = new \stdClass();
            $std->infDPS->valores->trib->tribFed->piscofins = new \stdClass();
            $std->infDPS->valores->trib->tribFed->piscofins->CST = '01';
            $std->infDPS->valores->trib->tribFed->piscofins->vBCPisCofins = number_format($amount, 2, '.', '');
            $std->infDPS->valores->trib->tribFed->piscofins->pAliqPis = number_format($pAliqPis, 2, '.', '');
            $std->infDPS->valores->trib->tribFed->piscofins->pAliqCofins = number_format($pAliqCofins, 2, '.', '');
            $std->infDPS->valores->trib->tribFed->piscofins->vPis = number_format($amount * $pAliqPis / 100, 2, '.', '');
            $std->infDPS->valores->trib->tribFed->piscofins->vCofins = number_format($amount * $pAliqCofins / 100, 2, '.', '');
            $std->infDPS->valores->trib->tribFed->piscofins->tpRetPisCofins = 0; // Não retido
        }

        // "Percentual aproximado dos tributos" (Lei da Transparência Fiscal)
        // - pTotTrib, not indTotTrib: for this CNPJ/regime the API rejects
        // indTotTrib with E0713 ("não podem ser informado"). Confirmed
        // against a real, already-accepted NFSe that this structure works.
        $std->infDPS->valores->trib->totTrib = new \stdClass();
        $std->infDPS->valores->trib->totTrib->pTotTrib = new \stdClass();
        $std->infDPS->valores->trib->totTrib->pTotTrib->pTotTribFed = number_format((float) $setting->nfse_percentual_trib_federal, 2, '.', '');
        $std->infDPS->valores->trib->totTrib->pTotTrib->pTotTribEst = '0.00';
        $std->infDPS->valores->trib->totTrib->pTotTrib->pTotTribMun = number_format((float) $setting->nfse_aliquota_iss, 2, '.', '');

        return $std;
    }

    /**
     * Best-effort: a note is already fully issued once the XML is stored,
     * so a DANFSe fetch failure (ADN flaky, certificate hiccup, whatever)
     * shouldn't mark the issuance as an error - it just means "Baixar PDF"
     * won't show up on the invoice yet.
     */
    private function fetchAndStoreDanfse(\Hadder\NfseNacional\Tools $tools, Invoice $invoice, ?string $chave): ?string
    {
        if (blank($chave)) {
            return null;
        }

        try {
            $pdf = $tools->consultarDanfse($chave);

            if (! is_string($pdf) || $pdf === '') {
                Log::warning("NFSe: DANFSe não retornou PDF para a fatura {$invoice->id}: ".json_encode($pdf));

                return null;
            }

            $pdfPath = "nfse/invoices/{$invoice->id}/nfse-{$chave}.pdf";
            Storage::disk('azure')->put($pdfPath, $pdf);

            return $pdfPath;
        } catch (\Throwable $e) {
            Log::warning("NFSe: falha ao baixar o DANFSe para a fatura {$invoice->id}: {$e->getMessage()}");

            return null;
        }
    }

    private function onlyDigits(?string $value): string
    {
        return preg_replace('/\D/', '', (string) $value);
    }

    /**
     * The government's actual error shape (confirmed against a real
     * rejection) is {"erros": [{"Codigo", "Descricao", "Complemento"}, ...]}
     * - capitalized keys, plural "erros". Kept the singular "erro" fallback
     * too in case some other endpoint/error path uses it.
     */
    private function extractErrorMessage($response, ?Tools $tools = null): string
    {
        $erros = is_array($response) ? ($response['erros'] ?? $response['erro'] ?? null) : null;

        if (is_array($erros)) {
            return collect($erros)->map(function ($item) {
                if (! is_array($item)) {
                    return (string) $item;
                }

                $codigo = $item['Codigo'] ?? $item['codigo'] ?? null;
                $descricao = $item['Descricao'] ?? $item['descricao'] ?? '';
                $complemento = $item['Complemento'] ?? $item['complemento'] ?? null;

                $message = $codigo ? "{$codigo}: {$descricao}" : $descricao;

                return $complemento ? "{$message} ({$complemento})" : $message;
            })->implode(' | ');
        }

        if (is_string($erros)) {
            return $erros;
        }

        // $response só vem null quando o corpo nem era JSON - geralmente o
        // próprio Ambiente Nacional fora do ar (502/503 em HTML, não um erro
        // de negócio). Sem isso, a mensagem só dizia "null", parecendo bug
        // nosso quando na verdade é o governo que está indisponível.
        if ($response === null && $tools?->responseHead) {
            preg_match('/^HTTP\/[\d.]+\s+(\d+)/', $tools->responseHead, $matches);
            $status = $matches[1] ?? '?';

            return "Ambiente Nacional da NFS-e retornou HTTP {$status} (serviço do governo indisponível no momento - tente novamente em instantes).";
        }

        return 'Resposta inesperada da API de NFS-e: '.json_encode($response);
    }

    private function extractNfseNumber(string $xml): ?string
    {
        try {
            $doc = new \SimpleXMLElement($xml);
            $doc->registerXPathNamespace('n', 'http://www.sped.fazenda.gov.br/nfse');
            $result = $doc->xpath('//n:infNFSe/n:nNFSe');

            return $result[0] ?? null ? (string) $result[0] : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
