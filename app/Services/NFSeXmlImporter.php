<?php

namespace App\Services;

use Illuminate\Support\Carbon;

/**
 * Parses a real NFS-e response XML (the document the government returns
 * after a successful emission - same shape whether it came from our own
 * NFSeService::issue() or was emitted by hand on nfse.gov.br) into the
 * plain fields the import review screen needs. Read-only - never talks to
 * the government or touches the database.
 */
class NFSeXmlImporter
{
    /**
     * @throws \RuntimeException if the XML isn't a recognizable NFSe.
     */
    public function parse(string $xml): array
    {
        $doc = new \SimpleXMLElement($xml);
        $doc->registerXPathNamespace('n', 'http://www.sped.fazenda.gov.br/nfse');

        $infNFSe = $doc->xpath('//n:infNFSe')[0] ?? null;

        if (! $infNFSe) {
            throw new \RuntimeException('Este arquivo não parece ser um XML de NFS-e (elemento infNFSe não encontrado).');
        }

        $chave = $this->extractChave($infNFSe);
        $numero = (string) ($infNFSe->nNFSe ?? '');
        $dhProc = (string) ($infNFSe->dhProc ?? '');

        $emitCnpj = (string) ($infNFSe->emit->CNPJ ?? '');

        $infDPS = $doc->xpath('//n:DPS/n:infDPS')[0] ?? null;

        if (! $infDPS) {
            throw new \RuntimeException('XML de NFS-e sem o bloco DPS/infDPS esperado.');
        }

        $dCompet = (string) ($infDPS->dCompet ?? '');
        $vServ = (string) ($infDPS->valores->vServPrest->vServ ?? $infNFSe->valores->vLiq ?? '0');
        $xDescServ = (string) ($infDPS->serv->cServ->xDescServ ?? '');

        $toma = $infDPS->toma ?? null;
        $tomaCnpj = $toma ? (string) ($toma->CNPJ ?? '') : '';
        $tomaCpf = $toma ? (string) ($toma->CPF ?? '') : '';
        $tomaNome = $toma ? (string) ($toma->xNome ?? '') : '';
        $tomaEmail = $toma ? (string) ($toma->email ?? '') : '';

        return [
            'chave_acesso' => $chave,
            'numero' => $numero,
            'issued_at' => $dhProc ? Carbon::parse($dhProc) : null,
            'competencia' => $dCompet ? Carbon::parse($dCompet) : null,
            'amount' => (float) $vServ,
            'description' => $xDescServ,
            'prestador_cnpj' => preg_replace('/\D/', '', $emitCnpj),
            'tomador' => [
                'document_type' => $tomaCnpj ? 'cnpj' : 'cpf',
                'document_number' => preg_replace('/\D/', '', $tomaCnpj ?: $tomaCpf),
                'name' => $tomaNome,
                'email' => $tomaEmail ?: null,
            ],
        ];
    }

    /**
     * infNFSe's Id attribute is "NFS" + the 50-digit chave de acesso - the
     * same chave enviaDps() returns directly as chaveAcesso.
     */
    private function extractChave(\SimpleXMLElement $infNFSe): string
    {
        $id = (string) $infNFSe->attributes()->Id;

        return str_starts_with($id, 'NFS') ? substr($id, 3) : $id;
    }
}
