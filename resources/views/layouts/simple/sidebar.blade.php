<!-- Page Sidebar Start-->
<div class="sidebar-wrapper" data-sidebar-layout="stroke-svg">
    <div>
        <div class="logo-wrapper"><a href="{{ route('admin.default_dashboard') }}"><img class="img-fluid for-light"
                    src="{{ asset('assets/images/logo/logo.svg') }}" alt=""><img class="img-fluid for-dark"
                    src="{{ asset('assets/images/logo/logo_dark.svg') }}" alt=""></a>
            <div class="back-btn"><i class="fa-solid fa-angle-left"></i></div>
            <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid">
                </i></div>
        </div>
        <div class="logo-icon-wrapper"><a href="{{ route('admin.default_dashboard') }}"><img class="img-fluid"
                    src="{{ asset('assets/images/logo/logo-icon.svg') }}" alt=""></a></div>
        <nav class="sidebar-main">
            <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
            <div id="sidebar-menu">
                <ul class="sidebar-links" id="simple-bar">
                    <li class="back-btn"><a href="{{ route('admin.default_dashboard') }}"><img class="img-fluid"
                                src="{{ asset('assets/images/logo/logo-icon.svg') }}" alt=""></a>
                        <div class="mobile-back text-end"><span>Back</span><i class="fa-solid fa-angle-right ps-2"
                                aria-hidden="true"></i></div>
                    </li>
                    {{-- Required by public/assets/js/sidebar-pin.js (pin/favorite
                         feature, not used in this app) - it does
                         document.querySelector('.pin-title') unconditionally and
                         throws if it's missing, so this stays as an empty,
                         permanently-hidden placeholder rather than editing the
                         vendor script. --}}
                    <li class="pin-title sidebar-main-title" style="display:none">
                        <div><h6>Pinned</h6></div>
                    </li>
                    <li class="sidebar-main-title">
                        <div>
                            <h6>Geral</h6>
                        </div>
                    </li>
                    <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav"
                            href="{{ route('admin.default_dashboard') }}"><svg class="stroke-icon">
                                <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                            </svg><svg class="fill-icon">
                                <use href="{{ asset('assets/svg/icon-sprite.svg#fill-home') }}"></use>
                            </svg><span>Dashboard</span></a></li>

                    <li class="sidebar-main-title">
                        <div>
                            <h6>Gestão</h6>
                        </div>
                    </li>

                    @canany(['customer.index', 'contact_lead.index', 'service.index', 'license.index', 'campaign.index', 'proposal.index'])
                        <li class="sidebar-list"><a class="sidebar-link sidebar-title" href="javascript:void(0)">
                                <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-contact') }}"></use>
                                </svg><svg class="fill-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#fill-contact') }}"></use>
                                </svg><span>Comercial</span></a>
                            <ul class="sidebar-submenu">
                                @can('customer.index')
                                    <li><a href="{{ route('admin.customer.index') }}">Clientes</a></li>
                                @endcan
                                @can('contact_lead.index')
                                    <li><a href="{{ route('admin.contact_lead.index') }}">Contatos do Site</a></li>
                                @endcan
                                @can('service.index')
                                    <li><a href="{{ route('admin.service.index') }}">Serviços</a></li>
                                @endcan
                                @can('license.index')
                                    <li><a href="{{ route('admin.license.index') }}">Licenciamento</a></li>
                                @endcan
                                @can('campaign.index')
                                    <li><a href="{{ route('admin.campaign.index') }}">Campanhas</a></li>
                                @endcan
                                @can('proposal.index')
                                    <li><a href="{{ route('admin.proposal.index') }}">Propostas Comerciais</a></li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    @canany(['billing_policy.index', 'invoice.index'])
                        <li class="sidebar-list"><a class="sidebar-link sidebar-title" href="javascript:void(0)">
                                <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-price') }}"></use>
                                </svg><svg class="fill-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#fill-price') }}"></use>
                                </svg><span>Financeiro</span></a>
                            <ul class="sidebar-submenu">
                                @can('billing_policy.index')
                                    <li><a href="{{ route('admin.billing_policy.index') }}">Políticas de Faturamento</a></li>
                                @endcan
                                @can('invoice.index')
                                    <li><a href="{{ route('admin.invoice.index') }}">Faturas</a></li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    @canany(['glpi_instance.index', 'glpi_asset_import.create'])
                        <li class="sidebar-list"><a class="sidebar-link sidebar-title" href="javascript:void(0)">
                                <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-api') }}"></use>
                                </svg><svg class="fill-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#fill-api') }}"></use>
                                </svg><span>Integrações</span></a>
                            <ul class="sidebar-submenu">
                                @can('glpi_instance.index')
                                    <li><a href="{{ route('admin.glpi_instance.index') }}">Instâncias GLPI</a></li>
                                @endcan
                                @can('glpi_asset_import.create')
                                    <li><a href="{{ route('admin.glpi_asset_import.create') }}">Importar Ativos (GLPI)</a></li>
                                    <li><a href="{{ route('admin.glpi_asset_import.index') }}">Importações (GLPI)</a></li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    @canany(['user.index', 'role.index'])
                        <li class="sidebar-list"><a class="sidebar-link sidebar-title" href="javascript:void(0)">
                                <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-user') }}"></use>
                                </svg><svg class="fill-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#fill-user') }}"></use>
                                </svg><span>Acessos</span></a>
                            <ul class="sidebar-submenu">
                                @can('user.index')
                                    <li><a href="{{ route('admin.user.index') }}">Usuários</a></li>
                                @endcan
                                @can('role.index')
                                    <li><a href="{{ route('admin.role.index') }}">Roles</a></li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                </ul>
            </div>
            <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
        </nav>
    </div>
</div>
<!-- Page Sidebar Ends-->
