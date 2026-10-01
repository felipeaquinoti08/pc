<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\InvoicePaid;
use App\Models\Invoice;
use App\Models\Setting;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MercadoPagoWebhookController extends Controller
{
    /**
     * Mercado Pago's payment-notification webhook. Always responds 200 (so
     * MP doesn't endlessly retry) even when we can't act on a notification -
     * failures are logged instead.
     */
    public function handle(Request $request): JsonResponse
    {
        $setting = Setting::current();
        $dataId = $request->query('data.id') ?? data_get($request->all(), 'data.id');

        if (! $dataId) {
            return response()->json(['ok' => true]);
        }

        // The signature check is defense-in-depth, not the actual proof of
        // payment - that comes from fetchPayment() below (an authenticated
        // call to Mercado Pago using OUR OWN credentials; only a genuinely
        // approved payment matching a real invoice's external_reference can
        // ever mark anything paid, so a forged/unsigned webhook can't fake
        // that part - it can only waste an API lookup). So a signature
        // mismatch is logged (see matchSignature()) but never blocks
        // processing on its own.
        $secrets = $setting->mercadoPagoWebhookSecrets();
        $signatureIsTest = $secrets ? $this->matchSignature($request, $dataId, $secrets) : null;

        try {
            [$payment, $isTest] = app(MercadoPagoService::class)->fetchPaymentDetectingEnvironment((int) $dataId, $signatureIsTest);
        } catch (\Throwable $e) {
            Log::warning("Mercado Pago webhook: falha ao buscar pagamento {$dataId}: {$e->getMessage()}");

            return response()->json(['ok' => false], 200);
        }

        if ($payment->status !== 'approved' || ! $payment->external_reference) {
            return response()->json(['ok' => true]);
        }

        $invoice = Invoice::find((int) $payment->external_reference);

        if (! $invoice || $invoice->status === Invoice::STATUS_PAID) {
            return response()->json(['ok' => true]);
        }

        $invoice->markAsPaid((string) $payment->id, $isTest);

        $customer = $invoice->license?->customer;

        if (Setting::current()->mailConfigured() && $customer?->email) {
            try {
                Mail::to($customer->email)->send(new InvoicePaid($invoice));
            } catch (\Throwable $e) {
                Log::warning("Mercado Pago webhook: falha ao enviar e-mail de pagamento confirmado para a fatura {$invoice->id}: {$e->getMessage()}");
            }
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Tries every configured webhook secret (test and production can both
     * receive real traffic regardless of which one is "active" for creating
     * new payment links) and returns whether the one that matched is the
     * test secret, or null if none of them validate.
     *
     * @param  array<int, array{secret: string, is_test: bool}>  $secrets
     *
     * @see https://www.mercadopago.com/developers/en/docs/your-integrations/notifications/webhooks#editor_5
     */
    private function matchSignature(Request $request, string $dataId, array $secrets): ?bool
    {
        $signatureHeader = $request->header('x-signature');
        $requestId = $request->header('x-request-id');

        if (! $signatureHeader) {
            Log::warning('Mercado Pago webhook: notificação sem header x-signature.', [
                'headers' => $request->headers->all(),
                'raw_body' => $request->getContent(),
            ]);

            return null;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $chunk) {
            [$key, $value] = array_pad(explode('=', trim($chunk), 2), 2, null);
            $parts[$key] = $value;
        }

        $timestamp = $parts['ts'] ?? null;
        $providedHash = $parts['v1'] ?? null;

        if (! $timestamp || ! $providedHash) {
            Log::warning('Mercado Pago webhook: header x-signature em formato inesperado.', [
                'x_signature_header' => $signatureHeader,
            ]);

            return null;
        }

        $manifest = 'id:'.strtolower($dataId).';request-id:'.$requestId.';ts:'.$timestamp.';';

        foreach ($secrets as $entry) {
            $computedHash = hash_hmac('sha256', $manifest, $entry['secret']);

            if (hash_equals($computedHash, $providedHash)) {
                return $entry['is_test'];
            }
        }

        // Temporary diagnostic logging - nothing here is secret (neither
        // webhook secret itself gets logged), just the inputs that go into
        // the manifest, so a mismatch can be pinpointed instead of guessed at.
        Log::warning('Mercado Pago webhook: assinatura não bateu com nenhum segredo configurado.', [
            'data_id' => $dataId,
            'x_request_id' => $requestId,
            'x_signature_header' => $signatureHeader,
            'manifest' => $manifest,
            'secrets_tried' => count($secrets),
            'live_mode' => data_get($request->all(), 'live_mode'),
        ]);

        return null;
    }
}
