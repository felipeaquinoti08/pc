<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        // Runs early morning, in this order: generate last month's invoices
        // (in arrears, due the 5th) before sweeping due dates/grace periods,
        // so a brand-new invoice never gets immediately flagged by the sweep.
        $schedule->command('invoices:generate')->dailyAt('01:00');
        $schedule->command('invoices:sweep')->dailyAt('02:00');

        // Backup for the Mercado Pago webhook - in case a notification is
        // ever missed, this catches it within 10 minutes instead of never.
        $schedule->command('payments:poll-pending')->everyTenMinutes();

        // The ADN's DANFSe endpoint is flaky (503s, sometimes hangs) - an
        // invoice can sit "issued" with no PDF for a while even though the
        // note itself went through fine. Retries every 30 min so it
        // recovers on its own the moment the government's side is back,
        // without anyone having to click "Buscar PDF" repeatedly (there's
        // still a manual-upload fallback on the invoice screen either way).
        $schedule->command('nfse:fetch-pdfs')->everyThirtyMinutes();

        // Feeds the "Status do Sistema" card on the dashboard.
        $schedule->command('system:check-health')->everyFiveMinutes();
    })
    ->withMiddleware(function (Middleware $middleware) {
        // The app is only ever reached through the internal reverse proxy
        // chain (nginx-proxy-manager -> pc-nginx -> php-fpm), never
        // directly from the internet, so trusting all proxies for the
        // X-Forwarded-* headers is safe here. Without this, Laravel never
        // learns the original request was HTTPS (it only sees the plain
        // HTTP connection from pc-nginx) and generates http:// URLs for
        // everything - assets, AJAX calls, redirects - which browsers
        // then block as mixed content on the HTTPS page.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
