<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Setting;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Net\MPSearchRequest;
use MercadoPago\Resources\Payment;

class MercadoPagoService
{
    /**
     * Creates a Checkout Pro preference for the invoice and stores the
     * payment link on it. Throws on failure - callers decide whether to
     * swallow the error (invoice just stays without a link, admin can
     * retry from the invoice screen).
     */
    public function attachPreference(Invoice $invoice): void
    {
        MercadoPagoConfig::setAccessToken(Setting::current()->activeMercadoPagoAccessToken());

        $customer = $invoice->customer;
        $title = trim(sprintf(
            'Fatura %02d/%d - %s - %s',
            $invoice->reference_month,
            $invoice->reference_year,
            $invoice->billingPolicy?->label() ?? 'Cobrança',
            $customer?->name ?? 'Cliente'
        ));

        $preference = (new PreferenceClient)->create([
            'items' => [[
                'title' => $title,
                'quantity' => 1,
                'unit_price' => (float) $invoice->amount,
                'currency_id' => 'BRL',
            ]],
            'external_reference' => (string) $invoice->id,
            'notification_url' => route('api.mercadopago.webhook'),
        ]);

        $invoice->mp_preference_id = $preference->id;
        $invoice->mp_payment_link = $preference->init_point;
        $invoice->save();
    }

    /**
     * @param  bool  $isTest  which credential set signed the webhook that
     *                        reported this payment id - test and production
     *                        payments live under different access tokens.
     */
    public function fetchPayment(int $paymentId, bool $isTest = false): Payment
    {
        $setting = Setting::current();
        MercadoPagoConfig::setAccessToken($isTest ? $setting->mp_test_access_token : $setting->mp_access_token);

        return (new PaymentClient)->get($paymentId);
    }

    /**
     * Same "try both, whichever works is the real environment" trick as
     * findApprovedPayment(), but for the webhook's case: it already knows
     * the specific payment id (from the notification) and just needs to
     * fetch its details.
     *
     * @return array{0: Payment, 1: bool} [payment, isTest]
     *
     * @throws \Throwable if neither credential set can fetch this payment id
     */
    public function fetchPaymentDetectingEnvironment(int $paymentId, ?bool $preferredIsTest): array
    {
        $order = $preferredIsTest === null ? [true, false] : [$preferredIsTest, ! $preferredIsTest];
        $lastException = null;

        foreach ($order as $isTest) {
            try {
                return [$this->fetchPayment($paymentId, $isTest), $isTest];
            } catch (\Throwable $e) {
                $lastException = $e;
            }
        }

        throw $lastException;
    }

    /**
     * A test access token can only ever find test-mode payments (and errors
     * on real ones, and vice versa) - so whichever token actually succeeds
     * tells us, with certainty, which environment a payment belongs to.
     * More reliable than trusting the notification's own live_mode flag,
     * which this integration has seen behave inconsistently for sandbox
     * payments. Shared by both the webhook (given a preferred guess from a
     * matched signature) and the pending-payment poll (no guess at all).
     *
     * @return array{0: Payment, 1: bool}|null [payment, isTest] of the first
     *                                          approved payment found for
     *                                          this invoice, or null.
     */
    public function findApprovedPayment(Invoice $invoice, ?bool $preferredIsTest = null): ?array
    {
        $order = $preferredIsTest === null ? [true, false] : [$preferredIsTest, ! $preferredIsTest];

        foreach ($order as $isTest) {
            $setting = Setting::current();
            $accessToken = $isTest ? $setting->mp_test_access_token : $setting->mp_access_token;

            if (blank($accessToken)) {
                continue;
            }

            try {
                MercadoPagoConfig::setAccessToken($accessToken);
                $results = (new PaymentClient)->search(
                    new MPSearchRequest(10, 0, ['external_reference' => (string) $invoice->id])
                );
            } catch (\Throwable $e) {
                continue;
            }

            foreach ($results->results ?? [] as $payment) {
                if ($payment->status === 'approved') {
                    return [$payment, $isTest];
                }
            }
        }

        return null;
    }
}
