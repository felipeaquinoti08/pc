<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateMailSettingRequest;
use App\Http\Requests\Admin\UpdateMercadoPagoSettingRequest;
use App\Http\Requests\Admin\UpdateNfseSettingRequest;
use App\Http\Requests\Admin\UpdateSsoSettingRequest;
use App\Http\Requests\Admin\UpdateStorageSettingRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit(): \Illuminate\Contracts\View\View
    {
        $setting = Setting::current();
        $this->authorize('view', $setting);

        return view('admin.settings.edit', ['setting' => $setting]);
    }

    public function updateSso(UpdateSsoSettingRequest $request): RedirectResponse
    {
        $setting = Setting::current();
        $this->authorize('update', $setting);

        $setting->sso_tenant_id = $request->input('sso_tenant_id');
        $setting->sso_client_id = $request->input('sso_client_id');

        if ($request->filled('sso_client_secret')) {
            $setting->sso_client_secret = $request->input('sso_client_secret');
        }

        $setting->save();

        return redirect()->route('admin.setting.edit')->with('success', __('Configurações de SSO salvas com sucesso.'));
    }

    public function updateStorage(UpdateStorageSettingRequest $request): RedirectResponse
    {
        $setting = Setting::current();
        $this->authorize('update', $setting);

        $setting->blob_account_name = $request->input('blob_account_name');
        $setting->blob_container = $request->input('blob_container');
        $setting->blob_endpoint_suffix = $request->input('blob_endpoint_suffix') ?: 'core.windows.net';

        if ($request->filled('blob_account_key')) {
            $setting->blob_account_key = $request->input('blob_account_key');
        }

        $setting->save();

        return redirect()->route('admin.setting.edit')->with('success', __('Configurações de armazenamento salvas com sucesso.'));
    }

    public function updateMercadoPago(UpdateMercadoPagoSettingRequest $request): RedirectResponse
    {
        $setting = Setting::current();
        $this->authorize('update', $setting);

        $setting->mp_environment = $request->input('mp_environment', 'test');
        $setting->mp_public_key = $request->input('mp_public_key');
        $setting->mp_test_public_key = $request->input('mp_test_public_key');

        if ($request->filled('mp_access_token')) {
            $setting->mp_access_token = $request->input('mp_access_token');
        }

        if ($request->filled('mp_webhook_secret')) {
            $setting->mp_webhook_secret = $request->input('mp_webhook_secret');
        }

        if ($request->filled('mp_test_access_token')) {
            $setting->mp_test_access_token = $request->input('mp_test_access_token');
        }

        if ($request->filled('mp_test_webhook_secret')) {
            $setting->mp_test_webhook_secret = $request->input('mp_test_webhook_secret');
        }

        $setting->save();

        return redirect()->route('admin.setting.edit')->with('success', __('Configurações do Mercado Pago salvas com sucesso.'));
    }

    public function updateMail(UpdateMailSettingRequest $request): RedirectResponse
    {
        $setting = Setting::current();
        $this->authorize('update', $setting);

        $setting->mail_host = $request->input('mail_host');
        $setting->mail_port = $request->input('mail_port');
        $setting->mail_username = $request->input('mail_username');
        $setting->mail_encryption = $request->input('mail_encryption');
        $setting->mail_from_address = $request->input('mail_from_address');
        $setting->mail_from_name = $request->input('mail_from_name');

        if ($request->filled('mail_password')) {
            $setting->mail_password = $request->input('mail_password');
        }

        $setting->save();

        return redirect()->route('admin.setting.edit')->with('success', __('Configurações de e-mail salvas com sucesso.'));
    }

    public function testMail(Request $request): JsonResponse
    {
        $this->authorize('update', Setting::current());

        $request->validate([
            'mail_host' => ['required', 'string'],
            'mail_port' => ['required', 'string'],
            'mail_username' => ['nullable', 'string'],
            'mail_password' => ['nullable', 'string'],
            'mail_encryption' => ['nullable', 'string'],
            'mail_from_address' => ['required', 'email'],
            'mail_from_name' => ['nullable', 'string'],
            'test_to' => ['required', 'email'],
        ]);

        $password = $request->input('mail_password') ?: Setting::current()->mail_password;

        try {
            config([
                'mail.mailers.smtp.host' => $request->input('mail_host'),
                'mail.mailers.smtp.port' => $request->input('mail_port'),
                'mail.mailers.smtp.username' => $request->input('mail_username'),
                'mail.mailers.smtp.password' => $password,
                'mail.mailers.smtp.encryption' => $request->input('mail_encryption') ?: null,
                'mail.from.address' => $request->input('mail_from_address'),
                'mail.from.name' => $request->input('mail_from_name') ?: config('app.name'),
            ]);

            Mail::raw('Este é um e-mail de teste do painel central.', function ($message) use ($request) {
                $message->to($request->input('test_to'))->subject('Teste de SMTP - Painel Central');
            });

            return response()->json(['ok' => true, 'message' => 'E-mail de teste enviado com sucesso.']);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'Falha ao enviar: '.$e->getMessage()], 422);
        }
    }

    public function updateNfse(UpdateNfseSettingRequest $request): RedirectResponse
    {
        $setting = Setting::current();
        $this->authorize('update', $setting);

        $setting->fill($request->only([
            'nfse_environment', 'nfse_dps_serie', 'nfse_cnpj', 'nfse_inscricao_municipal',
            'nfse_op_simples_nacional', 'nfse_reg_esp_trib', 'nfse_telefone', 'nfse_email',
            'nfse_municipio_codigo', 'nfse_municipio_nome', 'nfse_uf', 'nfse_cep',
            'nfse_logradouro', 'nfse_numero', 'nfse_bairro',
            'nfse_codigo_tributacao_nacional', 'nfse_codigo_tributacao_municipal', 'nfse_codigo_nbs',
            'nfse_aliquota_iss', 'nfse_aliquota_pis', 'nfse_aliquota_cofins',
        ]));

        if ($request->filled('nfse_certificate_password')) {
            $setting->nfse_certificate_password = $request->input('nfse_certificate_password');
        }

        if ($request->hasFile('nfse_certificate')) {
            if (! $setting->blobConfigured()) {
                return redirect()->route('admin.setting.edit')->with('error', __('Configure o Azure Blob Storage antes de enviar o certificado.'));
            }

            // Always the same path - a new upload replaces the previous
            // certificate rather than accumulating old ones on the blob.
            $path = 'nfse/certificado.pfx';
            Storage::disk('azure')->put($path, file_get_contents($request->file('nfse_certificate')->getRealPath()));
            $setting->nfse_certificate_path = $path;
        }

        $setting->save();

        return redirect()->route('admin.setting.edit')->with('success', __('Configurações de Nota Fiscal salvas com sucesso.'));
    }

    /**
     * Only confirms the .pfx + password actually open and belong to the
     * expected CNPJ - never talks to the government API. That happens the
     * first time an invoice is actually issued.
     */
    public function testNfseCertificate(Request $request): JsonResponse
    {
        $setting = Setting::current();
        $this->authorize('update', $setting);

        if (! $setting->blobConfigured() || blank($setting->nfse_certificate_path)) {
            return response()->json(['ok' => false, 'message' => 'Nenhum certificado enviado ainda.'], 422);
        }

        $password = $request->input('nfse_certificate_password') ?: $setting->nfse_certificate_password;

        if (blank($password)) {
            return response()->json(['ok' => false, 'message' => 'Informe a senha do certificado.'], 422);
        }

        try {
            $pfx = Storage::disk('azure')->get($setting->nfse_certificate_path);
            $certs = [];

            if (! openssl_pkcs12_read($pfx, $certs, $password)) {
                return response()->json(['ok' => false, 'message' => 'Não foi possível abrir o certificado com a senha informada.'], 422);
            }

            $data = openssl_x509_parse($certs['cert']);
            $cn = $data['subject']['CN'] ?? 'desconhecido';
            $validTo = date('d/m/Y', $data['validTo_time_t']);

            return response()->json(['ok' => true, 'message' => "Certificado válido: {$cn} (expira em {$validTo})."]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'Falha ao ler o certificado: '.$e->getMessage()], 422);
        }
    }

    public function testStorage(Request $request): JsonResponse
    {
        $this->authorize('update', Setting::current());

        $request->validate([
            'blob_account_name' => ['required', 'string'],
            'blob_account_key' => ['nullable', 'string'],
            'blob_container' => ['required', 'string'],
            'blob_endpoint_suffix' => ['nullable', 'string'],
        ]);

        $accountKey = $request->input('blob_account_key') ?: Setting::current()->blob_account_key;

        if (blank($accountKey)) {
            return response()->json(['ok' => false, 'message' => 'Informe a chave de acesso (Account Key).'], 422);
        }

        try {
            $disk = Storage::build([
                'driver' => 'azure',
                'name' => $request->input('blob_account_name'),
                'key' => $accountKey,
                'container' => $request->input('blob_container'),
                'endpointSuffix' => $request->input('blob_endpoint_suffix') ?: 'core.windows.net',
            ]);

            // A cheap call that only succeeds with valid credentials and an
            // existing container - doesn't write or read any blob content.
            $disk->directoryExists('');

            return response()->json(['ok' => true, 'message' => 'Conexão bem-sucedida! O container foi encontrado.']);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'Falha ao conectar: '.$e->getMessage()], 422);
        }
    }
}
