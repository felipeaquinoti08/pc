<?php

namespace App\Http\Controllers\Admin;

use App\Models\Customer;
use App\Models\License;
use App\Models\Invoice;
use App\Models\ContactLead;
use App\Models\ServiceStatus;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $openInvoices = Invoice::whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_OVERDUE]);

        $stats = [
            'customers' => Customer::count(),
            'active_licenses' => License::where('status', License::STATUS_ACTIVE)->count(),
            'open_invoices_count' => (clone $openInvoices)->count(),
            'open_invoices_amount' => (clone $openInvoices)->sum('amount'),
            'new_contact_leads' => ContactLead::where('status', ContactLead::STATUS_NEW)->count(),
        ];

        $monthlyRevenue = $this->monthlyRevenue();

        $insights = $this->insights();

        $recentContactLeads = ContactLead::with('customer')->latest()->limit(6)->get();

        $openInvoicesList = Invoice::with(['customer', 'billingPolicy.billable'])
            ->whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_OVERDUE])
            ->orderBy('due_date')
            ->limit(6)
            ->get();

        $expiringLicenses = License::with(['customer', 'services'])
            ->where('status', License::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays(30))
            ->orderBy('expires_at')
            ->limit(6)
            ->get();

        // Só leitura - os valores são atualizados em segundo plano pelo
        // comando `system:check-health` (ver bootstrap/app.php), então essa
        // tela nunca fica lenta esperando um serviço externo responder.
        // Indisponível primeiro (é o que precisa de atenção), depois não
        // configurado, e operacional por último.
        $statusPriority = [ServiceStatus::STATUS_DOWN => 0, ServiceStatus::STATUS_UNCONFIGURED => 1, ServiceStatus::STATUS_OK => 2];
        $serviceStatuses = ServiceStatus::orderBy('label')->get()
            ->sortBy(fn ($service) => $statusPriority[$service->status] ?? 3)
            ->values();

        return view('admin.dashboard.index', [
            'stats' => $stats,
            'monthlyRevenue' => $monthlyRevenue,
            'insights' => $insights,
            'recentContactLeads' => $recentContactLeads,
            'openInvoicesList' => $openInvoicesList,
            'expiringLicenses' => $expiringLicenses,
            'serviceStatuses' => $serviceStatuses,
        ]);
    }

    /**
     * A few numbers that don't fit the "count/sum" widgets above but are
     * cheap to compute and genuinely useful at a glance.
     */
    private function insights(): array
    {
        $ticketMedio = Invoice::where('status', Invoice::STATUS_PAID)->avg('amount');

        $openCount = Invoice::whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_OVERDUE])->count();
        $overdueCount = Invoice::where('status', Invoice::STATUS_OVERDUE)->count();
        $inadimplencia = $openCount > 0 ? ($overdueCount / $openCount) * 100 : null;

        // Postgres won't let HAVING reference a SELECT-list alias (unlike
        // ORDER BY, which does) - filtering the ">0" case in PHP after the
        // fact avoids repeating the whole subquery expression here. Also
        // needs NULLS LAST: Postgres sorts NULL as the "largest" value by
        // default, so a plain "DESC" would put customers with zero paid
        // invoices (paid_total is NULL, not 0, when the subquery matches no
        // rows) ahead of the actual top payer.
        $topCustomer = Customer::withSum(['invoices as paid_total' => function ($query) {
            $query->where('status', Invoice::STATUS_PAID);
        }], 'amount')
            ->orderByRaw('paid_total DESC NULLS LAST')
            ->first();

        if (($topCustomer->paid_total ?? 0) <= 0) {
            $topCustomer = null;
        }

        return [
            'ticket_medio' => (float) ($ticketMedio ?? 0),
            'inadimplencia' => $inadimplencia,
            'top_customer' => $topCustomer,
        ];
    }

    /**
     * Paid invoices summed by month, for the last 12 months (oldest first) -
     * feeds the "Faturamento mensal" chart. Months with no paid invoices
     * still appear, with 0, so the chart doesn't silently skip gaps.
     */
    private function monthlyRevenue(): array
    {
        $start = now()->subMonths(11)->startOfMonth();

        $paid = Invoice::where('status', Invoice::STATUS_PAID)
            ->where('paid_at', '>=', $start)
            ->get()
            ->groupBy(fn ($invoice) => $invoice->paid_at->format('Y-m'));

        $labels = [];
        $totals = [];

        for ($i = 0; $i < 12; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');

            $labels[] = Carbon::parse($key . '-01')->locale('pt_BR')->isoFormat('MMM/YY');
            $totals[] = (float) ($paid->get($key)?->sum('amount') ?? 0);
        }

        // O último balde é sempre o mês corrente, ainda em andamento (ex:
        // dia 8 do mês) - comparar ele com o mês anterior sempre dá uma
        // queda enorme e enganosa, não uma variação real. A comparação
        // válida é entre os dois últimos meses já fechados.
        $lastCompleteMonth = $totals[count($totals) - 2];
        $previousMonth = $totals[count($totals) - 3];
        $momChange = $previousMonth > 0 ? (($lastCompleteMonth - $previousMonth) / $previousMonth) * 100 : null;

        return [
            'labels' => $labels,
            'totals' => $totals,
            'period_total' => array_sum($totals),
            'mom_change' => $momChange,
        ];
    }
}
