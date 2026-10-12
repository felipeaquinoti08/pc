@extends('layouts.simple.master')

@section('title', 'Dashboard')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/animate.css') }}">
    <style>
        .service-status-summary { font-size: 13px; padding: 6px 12px; }
        .service-status-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            height: 100%;
            padding: 16px;
            border-radius: 14px;
            background-color: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .service-status-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.25);
        }
        .service-status-icon {
            flex-shrink: 0;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .service-status-icon svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
        }
        .service-status-item.status-ok .service-status-icon { background-color: rgba(101, 193, 92, 0.15); color: #65c15c; }
        .service-status-item.status-down .service-status-icon { background-color: rgba(252, 86, 74, 0.15); color: #fc564a; }
        .service-status-item.status-unconfigured .service-status-icon { background-color: rgba(131, 131, 131, 0.2); color: #838383; }
        .service-status-item.status-down { border-color: rgba(252, 86, 74, 0.3); }
        .service-status-body h6 { margin-bottom: 6px; font-size: 14px; font-weight: 600; }
        .service-status-meta { margin: 4px 0 0; font-size: 12px; opacity: .65; }

        .stat-caption { font-size: 12px; opacity: .6; margin-top: 2px; }

        .chart-summary-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
        }
        .chart-summary-pill.is-up { background-color: rgba(101, 193, 92, 0.15); color: #65c15c; }
        .chart-summary-pill.is-down { background-color: rgba(252, 86, 74, 0.15); color: #fc564a; }
        .chart-summary-pill.is-flat { background-color: rgba(131, 131, 131, 0.2); color: #838383; }

        .avatar-initials {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7366FF, #33B1EE);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .dashboard-list-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 8px;
            border-radius: 10px;
            transition: background-color .15s ease;
        }
        .dashboard-list-row:hover { background-color: rgba(255, 255, 255, 0.05); }

        .recent-table tbody tr { transition: background-color .15s ease; }
        .recent-table tbody tr:hover { background-color: rgba(255, 255, 255, 0.05); }
        .recent-table td { vertical-align: middle; }
        .recent-table .table-name-cell { display: flex; align-items: center; gap: 10px; }
        /* badge-light-* is a translucent fill close to the card's own dark
           background - reads fine as text but the "pill" shape disappears.
           A border in the badge's own color keeps it a visible chip. */
        .recent-table .badge { border: 1px solid currentColor; }

        /* Ambient glow behind the whole page - purely decorative, sits
           behind everything (z-index) and never intercepts clicks. */
        .default-dashboard { position: relative; }
        .default-dashboard::before,
        .default-dashboard::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(30px);
            pointer-events: none;
            z-index: 0;
        }
        .default-dashboard::before {
            top: -140px;
            right: -60px;
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, rgba(115, 102, 255, 0.16), transparent 70%);
        }
        .default-dashboard::after {
            bottom: -120px;
            left: -60px;
            width: 380px;
            height: 380px;
            background: radial-gradient(circle, rgba(51, 177, 238, 0.12), transparent 70%);
        }
        .default-dashboard > .row { position: relative; z-index: 1; }

        /* Quick actions */
        .quick-actions-bar { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; position: relative; z-index: 1; }
        .quick-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 18px 8px 8px;
            border-radius: 40px;
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(6px);
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            color: inherit;
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease, background-color .15s ease;
        }
        .quick-action-btn:hover {
            transform: translateY(-2px);
            background-color: rgba(115, 102, 255, 0.14);
            border-color: rgba(115, 102, 255, 0.4);
            box-shadow: 0 10px 22px rgba(115, 102, 255, 0.22);
            color: inherit;
        }
        .quick-action-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #7366FF, #33B1EE);
            flex-shrink: 0;
        }
        .quick-action-icon svg { width: 14px; height: 14px; stroke: #fff; fill: none; }

        /* Metric cards (topo) - mesmo padrão visual do Insights/Status do
           Sistema (ícone em caixa colorida sólida), pra tudo na página
           seguir uma linguagem só. */
        .metric-card {
            display: flex;
            align-items: center;
            gap: 16px;
            height: 100%;
            padding: 20px;
            border-radius: 16px;
            background-color: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .metric-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(0, 0, 0, 0.25); }
        .metric-icon {
            flex-shrink: 0;
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .metric-icon svg { width: 22px; height: 22px; stroke: currentColor; fill: none; }
        .metric-icon.primary { background-color: rgba(115, 102, 255, 0.15); color: #7366FF; }
        .metric-icon.success { background-color: rgba(101, 193, 92, 0.15); color: #65c15c; }
        .metric-icon.warning { background-color: rgba(255, 184, 41, 0.15); color: #ffb829; }
        .metric-icon.secondary { background-color: rgba(131, 131, 131, 0.2); color: #838383; }
        .metric-card h3 { margin-bottom: 2px; font-weight: 700; }
        .metric-label { font-size: 13px; opacity: .75; }
        .metric-caption { font-size: 12px; opacity: .55; margin: 2px 0 0; }

        /* Insights */
        .insight-item { display: flex; align-items: center; gap: 14px; height: 100%; }
        .insight-icon {
            flex-shrink: 0;
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(115, 102, 255, .25), rgba(51, 177, 238, .18));
        }
        .insight-icon svg { width: 20px; height: 20px; stroke: #fff; fill: none; }
        .insight-item h4 { margin-bottom: 2px; }
    </style>
@endsection

@section('main_content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Dashboard</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.default_dashboard') }}"> <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                                </svg></a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div><!-- Container-fluid starts-->
    <div class="container-fluid default-dashboard">
        <div class="quick-actions-bar">
            @can('customer.create')
                <a href="{{ route('admin.customer.create') }}" class="quick-action-btn">
                    <span class="quick-action-icon"><svg><use href="{{ asset('assets/svg/icon-sprite.svg#stroke-user') }}"></use></svg></span>
                    Novo Cliente
                </a>
            @endcan
            @can('proposal.create')
                <a href="{{ route('admin.proposal.create') }}" class="quick-action-btn">
                    <span class="quick-action-icon"><svg><use href="{{ asset('assets/svg/icon-sprite.svg#stroke-file') }}"></use></svg></span>
                    Nova Proposta
                </a>
            @endcan
            @can('license.create')
                <a href="{{ route('admin.license.create') }}" class="quick-action-btn">
                    <span class="quick-action-icon"><svg><use href="{{ asset('assets/svg/icon-sprite.svg#stroke-note') }}"></use></svg></span>
                    Novo Licenciamento
                </a>
            @endcan
            @can('campaign.create')
                <a href="{{ route('admin.campaign.create') }}" class="quick-action-btn">
                    <span class="quick-action-icon"><svg><use href="{{ asset('assets/svg/icon-sprite.svg#stroke-subscribe') }}"></use></svg></span>
                    Nova Campanha
                </a>
            @endcan
        </div>
        <div class="row widget-grid align-items-start">
            <div class="col-xxl-4 col-sm-6 box-col-6">
                <div class="card profile-box">
                    <div class="card-body">
                        <div class="d-flex media-wrapper justify-content-between">
                            <div class="flex-grow-1">
                                <div class="greeting-user">
                                    <h2 class="f-w-600">Olá, {{ \Illuminate\Support\Str::title(auth()->user()->first_name ?? '') }} {{ \Illuminate\Support\Str::title(auth()->user()->last_name ?? '') }}!</h2>
                                    <p>Aqui está um resumo do que está acontecendo agora.</p>
                                    <div class="whatsnew-btn"><a class="btn btn-outline-white" href="{{ route('admin.user_profile') }}"
                                            target="_blank">Ver Perfil</a></div>
                                </div>
                            </div>
                            <div>
                                <div class="clockbox"><svg id="clock" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 600 600">
                                        <g id="face">
                                            <circle class="circle" cx="300" cy="300" r="253.9"></circle>
                                            <path class="hour-marks"
                                                d="M300.5 94V61M506 300.5h32M300.5 506v33M94 300.5H60M411.3 107.8l7.9-13.8M493 190.2l13-7.4M492.1 411.4l16.5 9.5M411 492.3l8.9 15.3M189 492.3l-9.2 15.9M107.7 411L93 419.5M107.5 189.3l-17.1-9.9M188.1 108.2l-9-15.6">
                                            </path>
                                            <circle class="mid-circle" cx="300" cy="300" r="16.2"></circle>
                                        </g>
                                        <g id="hour">
                                            <path class="hour-hand" d="M300.5 298V142"></path>
                                            <circle class="sizing-box" cx="300" cy="300" r="253.9"></circle>
                                        </g>
                                        <g id="minute">
                                            <path class="minute-hand" d="M300.5 298V67"> </path>
                                            <circle class="sizing-box" cx="300" cy="300" r="253.9"></circle>
                                        </g>
                                        <g id="second">
                                            <path class="second-hand" d="M300.5 350V55"></path>
                                            <circle class="sizing-box" cx="300" cy="300" r="253.9">
                                            </circle>
                                        </g>
                                    </svg></div>
                                <div class="badge f-10 p-0" id="txt"></div>
                            </div>
                        </div>
                        <div class="cartoon"><img class="img-fluid" src="{{ asset('assets/images/dashboard/cartoon.svg') }}"
                                alt="vector women with leptop">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-8 col-sm-6 box-col-6">
                <div class="row g-3">
                    <div class="col-xl-3 col-sm-6">
                        <div class="metric-card">
                            <div class="metric-icon primary">
                                <svg><use href="{{ asset('assets/svg/icon-sprite.svg#stroke-user') }}"></use></svg>
                            </div>
                            <div>
                                <h3><span class="counter" data-target="{{ $stats['customers'] }}">0</span></h3>
                                <span class="metric-label">Clientes</span>
                                <p class="metric-caption">Total cadastrado</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6">
                        <div class="metric-card">
                            <div class="metric-icon success">
                                <svg><use href="{{ asset('assets/svg/icon-sprite.svg#stroke-note') }}"></use></svg>
                            </div>
                            <div>
                                <h3><span class="counter" data-target="{{ $stats['active_licenses'] }}">0</span></h3>
                                <span class="metric-label">Licenças Ativas</span>
                                <p class="metric-caption">Em uso agora</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6">
                        <div class="metric-card">
                            <div class="metric-icon warning">
                                <svg><use href="{{ asset('assets/svg/icon-sprite.svg#stroke-price') }}"></use></svg>
                            </div>
                            <div>
                                <h3>R$ {{ number_format($stats['open_invoices_amount'], 2, ',', '.') }}</h3>
                                <span class="metric-label">Faturas em Aberto</span>
                                <p class="metric-caption">{{ $stats['open_invoices_count'] }} fatura(s) a receber</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6">
                        <div class="metric-card">
                            <div class="metric-icon secondary">
                                <svg><use href="{{ asset('assets/svg/icon-sprite.svg#stroke-msg') }}"></use></svg>
                            </div>
                            <div>
                                <h3><span class="counter" data-target="{{ $stats['new_contact_leads'] }}">0</span></h3>
                                <span class="metric-label">Contatos Novos</span>
                                <p class="metric-caption">Aguardando retorno</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-12 box-col-12 ord-xl-1">
                <div class="card">
                    <div class="card-header card-no-border">
                        <div class="header-top">
                            <h5>Insights</h5>
                            <p class="f-light mb-0">Leitura rápida da saúde financeira</p>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="insight-item">
                                    <span class="insight-icon">
                                        <svg><use href="{{ asset('assets/svg/icon-sprite.svg#stroke-price') }}"></use></svg>
                                    </span>
                                    <div>
                                        <h4>R$ {{ number_format($insights['ticket_medio'], 2, ',', '.') }}</h4>
                                        <p class="f-light mb-0">Ticket médio (faturas pagas)</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="insight-item">
                                    <span class="insight-icon">
                                        <svg><use href="{{ asset('assets/svg/icon-sprite.svg#stroke-error') }}"></use></svg>
                                    </span>
                                    <div>
                                        <h4>{{ $insights['inadimplencia'] !== null ? number_format($insights['inadimplencia'], 1, ',', '.') . '%' : '—' }}</h4>
                                        <p class="f-light mb-0">Inadimplência (faturas em aberto)</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="insight-item">
                                    <span class="insight-icon">
                                        <svg><use href="{{ asset('assets/svg/icon-sprite.svg#stroke-client') }}"></use></svg>
                                    </span>
                                    <div>
                                        <h4>{{ $insights['top_customer']?->name ?? '—' }}</h4>
                                        <p class="f-light mb-0">Maior cliente{{ $insights['top_customer'] ? ' — R$ ' . number_format($insights['top_customer']->paid_total, 2, ',', '.') . ' pagos' : '' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-12 box-col-12 ord-xl-1">
                <div class="card service-status-card">
                    <div class="card-header card-no-border d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="header-top mb-0">
                            <h5>Status do Sistema</h5>
                            <p class="f-light mb-0">Verificação automática a cada 5 minutos</p>
                        </div>
                        @php
                            $downCount = $serviceStatuses->where('status', \App\Models\ServiceStatus::STATUS_DOWN)->count();
                            $lastChecked = $serviceStatuses->max('checked_at');
                        @endphp
                        <div class="text-end">
                            @if ($downCount > 0)
                                <span class="badge badge-light-danger service-status-summary"><i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $downCount }} serviço(s) indisponível</span>
                            @else
                                <span class="badge badge-light-success service-status-summary"><i class="fa-solid fa-circle-check me-1"></i>Tudo operacional</span>
                            @endif
                            @if ($lastChecked)
                                <p class="f-light mb-0 mt-1">Última checagem {{ $lastChecked->diffForHumans() }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-3">
                            @forelse ($serviceStatuses as $service)
                                <div class="col-xxl-2 col-lg-3 col-sm-4 col-6">
                                    <div class="service-status-item status-{{ $service->status }}">
                                        <div class="service-status-icon">
                                            <svg>
                                                <use href="{{ asset('assets/svg/icon-sprite.svg#' . $service->icon()) }}"></use>
                                            </svg>
                                        </div>
                                        <div class="service-status-body">
                                            <h6>{{ $service->label }}</h6>
                                            <span class="badge {{ $service->badgeClass() }}">{{ $service->statusLabel() }}</span>
                                            @if ($service->checked_at)
                                                <p class="service-status-meta">{{ $service->checked_at->diffForHumans() }}</p>
                                            @endif
                                            @if ($service->status === \App\Models\ServiceStatus::STATUS_DOWN && $service->message)
                                                <p class="service-status-meta text-danger">{{ $service->message }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="f-light mb-0">Nenhuma verificação ainda — a próxima rodada agendada (a cada 5 min) preenche isso automaticamente.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-8 col-lg-7 box-col-7 ord-xl-2">
                <div class="card">
                    <div class="card-header card-no-border d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="header-top mb-0">
                            <h5>Faturamento Mensal</h5>
                            <p class="f-light mb-0">Últimos 12 meses · Total R$ {{ number_format($monthlyRevenue['period_total'], 2, ',', '.') }}</p>
                        </div>
                        @if ($monthlyRevenue['mom_change'] !== null)
                            @php
                                $mom = $monthlyRevenue['mom_change'];
                                $momClass = $mom > 0.5 ? 'is-up' : ($mom < -0.5 ? 'is-down' : 'is-flat');
                                $momIcon = $mom > 0.5 ? 'fa-arrow-trend-up' : ($mom < -0.5 ? 'fa-arrow-trend-down' : 'fa-minus');
                            @endphp
                            <span class="chart-summary-pill {{ $momClass }}">
                                <i class="fa-solid {{ $momIcon }}"></i>
                                {{ ($mom >= 0 ? '+' : '') . number_format($mom, 1, ',', '.') }}% vs mês anterior
                            </span>
                        @endif
                    </div>
                    <div class="card-body pt-0">
                        <div id="monthly_revenue_chart"></div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-4 col-lg-5 box-col-5 ord-xl-3">
                <div class="card">
                    <div class="card-header card-no-border">
                        <div class="header-top">
                            <h5>Licenças Expirando (30 dias)</h5>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        @forelse ($expiringLicenses as $license)
                            @php
                                $daysLeft = now()->startOfDay()->diffInDays($license->expires_at, false);
                                $urgencyClass = $daysLeft <= 7 ? 'badge-light-danger' : ($daysLeft <= 15 ? 'badge-light-warning' : 'badge-light-primary');
                            @endphp
                            <div class="dashboard-list-row {{ ! $loop->last ? 'mb-2' : '' }}">
                                <div>
                                    <a class="f-w-500" href="{{ route('admin.license.edit', $license->id) }}">{{ $license->customer?->name }}</a>
                                    <p class="mb-0 f-light">{{ $license->services->pluck('name')->implode(', ') }}</p>
                                </div>
                                <div class="text-end">
                                    <span class="badge {{ $urgencyClass }}">{{ $license->expires_at->format('d/m/Y') }}</span>
                                    <p class="stat-caption mb-0">{{ $daysLeft }} dia(s)</p>
                                </div>
                            </div>
                        @empty
                            <p class="f-light mb-0">Nenhuma licença expirando nos próximos 30 dias.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xxl-6 box-col-6 ord-xl-4">
                <div class="card">
                    <div class="card-header card-no-border">
                        <div class="header-top">
                            <h5>Contatos Recentes do Site</h5>
                            <div class="card-header-right-icon">
                                <a href="{{ route('admin.contact_lead.index') }}" class="btn btn-primary btn-sm">Ver todos</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0">
                        <div class="recent-table table-responsive custom-scrollbar">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Empresa</th>
                                        <th>Status</th>
                                        <th>Recebido</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentContactLeads as $lead)
                                        @php
                                            $initials = collect(explode(' ', trim($lead->name)))->filter()->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="table-name-cell">
                                                    <span class="avatar-initials">{{ $initials }}</span>
                                                    <a href="{{ route('admin.contact_lead.show', $lead->id) }}">{{ $lead->name }}</a>
                                                </div>
                                            </td>
                                            <td>{{ $lead->company ?: '-' }}</td>
                                            <td><span class="badge {{ $lead->statusBadgeClass() }}">{{ $lead->statusLabel() }}</span></td>
                                            <td>{{ $lead->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center f-light">Nenhum contato recebido ainda.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-6 box-col-6 ord-xl-5">
                <div class="card">
                    <div class="card-header card-no-border">
                        <div class="header-top">
                            <h5>Faturas em Aberto</h5>
                            <div class="card-header-right-icon">
                                <a href="{{ route('admin.invoice.index') }}" class="btn btn-primary btn-sm">Ver todas</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0">
                        <div class="recent-table table-responsive custom-scrollbar">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Referente a</th>
                                        <th>Vencimento</th>
                                        <th>Valor</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($openInvoicesList as $invoice)
                                        @php
                                            $customerName = $invoice->customer?->name ?: '-';
                                            $initials = $invoice->customer ? collect(explode(' ', trim($customerName)))->filter()->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('') : '-';
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="table-name-cell">
                                                    <span class="avatar-initials">{{ $initials }}</span>
                                                    {{ $customerName }}
                                                </div>
                                            </td>
                                            <td>{{ $invoice->billingPolicy?->label() ?: '-' }}</td>
                                            <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                                            <td>R$ {{ number_format($invoice->amount, 2, ',', '.') }}</td>
                                            <td>
                                                @if ($invoice->status === \App\Models\Invoice::STATUS_OVERDUE)
                                                    <span class="badge badge-light-danger">Atrasada</span>
                                                @else
                                                    <span class="badge badge-light-secondary">Pendente</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center f-light">Nenhuma fatura em aberto.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- Container-fluid Ends-->
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/clock.js') }}"></script>
    <script src="{{ asset('assets/js/chart/apex-chart/apex-chart.js') }}"></script>
    <script src="{{ asset('assets/js/counter/counter-custom.js') }}?v=2"></script>
    <script>
        (function () {
            var revenueChart = new ApexCharts(document.querySelector('#monthly_revenue_chart'), {
                series: [{
                    name: 'Faturamento',
                    data: @json($monthlyRevenue['totals'])
                }],
                chart: {
                    height: 300,
                    type: 'bar',
                    toolbar: { show: false }
                },
                colors: ['#7366FF'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'dark',
                        type: 'vertical',
                        shadeIntensity: 0.3,
                        gradientToColors: ['#33B1EE'],
                        opacityFrom: 1,
                        opacityTo: 0.85,
                        stops: [0, 100]
                    }
                },
                plotOptions: {
                    bar: { borderRadius: 6, columnWidth: '45%' }
                },
                grid: {
                    borderColor: 'rgba(255,255,255,0.08)',
                },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: @json($monthlyRevenue['labels'])
                },
                yaxis: {
                    labels: {
                        formatter: function (val) {
                            return 'R$ ' + val.toLocaleString('pt-BR');
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return 'R$ ' + val.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                        }
                    }
                }
            });
            revenueChart.render();
        })();
    </script>
@endsection
