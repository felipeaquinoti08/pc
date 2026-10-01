<?php

namespace App\Console\Commands;

use App\Services\SystemHealthChecker;
use Illuminate\Console\Command;

class CheckSystemHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:check-health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes the "Status do Sistema" dashboard card - pings the NFS-e government endpoints and records whether the other integrations (Mercado Pago, e-mail, storage, SSO) are configured.';

    public function handle(SystemHealthChecker $checker): void
    {
        $checker->checkAll();

        $this->info('Status dos serviços atualizado.');
    }
}
