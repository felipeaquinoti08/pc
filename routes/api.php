<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LicenseValidationController;
use App\Http\Controllers\Api\MercadoPagoWebhookController;
use App\Http\Controllers\Api\ContactLeadController;

// Called by licensed plugins (e.g. Entrasso) every ~10 minutes to check
// whether their serial is still active and within the contracted
// installation quantity. Stateless, no session - the license_key itself
// is the credential, rate-limited to prevent brute-forcing keys.
Route::post('/licenses/validate', [LicenseValidationController::class, 'validateLicense'])
    ->middleware('throttle:30,1')
    ->name('api.licenses.validate');

// Mercado Pago payment notifications (Checkout Pro). Public, signature
// verified against Setting::mp_webhook_secret when configured.
Route::post('/mercadopago/webhook', [MercadoPagoWebhookController::class, 'handle'])
    ->middleware('throttle:60,1')
    ->name('api.mercadopago.webhook');

// Public contact form on the CCITI site (cciti.com.br). Rate-limited per IP
// to slow down spam floods; a honeypot field in the controller catches
// naive bots on top of that.
Route::post('/contact-leads', [ContactLeadController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('api.contact-leads.store');
