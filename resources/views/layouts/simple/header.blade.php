        <!-- Page Header Start-->
        <div class="page-header">
            <div class="header-wrapper row m-0">
                <form class="form-inline search-full col" action="#" method="get">
                    <div class="form-group w-100">
                        <div class="Typeahead Typeahead--twitterUsers">
                            <div class="u-posRelative"><input
                                    class="demo-input Typeahead-input form-control-plaintext w-100" type="text"
                                    placeholder="Search Anything Here..." name="q" title="" autofocus>
                                <div class="spinner-border Typeahead-spinner" role="status"><span
                                        class="sr-only">Loading...</span></div><i class="close-search"
                                    data-feather="x"></i>
                            </div>
                            <div class="Typeahead-menu"></div>
                        </div>
                    </div>
                </form>
                <div class="header-logo-wrapper col-auto p-0">
                    <div class="logo-wrapper"><a href="{{ route('admin.default_dashboard') }}"><img class="img-fluid for-light"
                                src="{{ asset('assets/images/logo/logo.svg') }}" alt=""><img
                                class="img-fluid for-dark" src="{{ asset('assets/images/logo/logo_dark.svg') }}"
                                alt=""></a></div>
                    <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle"
                            data-feather="align-center"></i></div>
                </div>
                <div class="nav-right col-xxl-7 col-xl-6 col-md-7 col-8 pull-right right-header p-0 ms-auto">
                    <ul class="nav-menus">
                        <li class="fullscreen-body"> <span><svg id="maximize-screen">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#full-screen') }}"></use>
                                </svg></span></li>
                        @can('viewHorizon')
                            <li class="header-icon-link"><a href="{{ url('/horizon') }}" target="_blank" title="Horizon">
                                    <svg class="stroke-icon">
                                        <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-activity') }}"></use>
                                    </svg></a></li>
                        @endcan
                        @can('setting.index')
                            <li class="header-icon-link"><a href="{{ route('admin.setting.edit') }}" title="Configurações">
                                    <svg class="stroke-icon">
                                        <use href="{{ asset('assets/svg/icon-sprite.svg#setting') }}"></use>
                                    </svg></a></li>
                        @endcan
                        <li class="profile-nav onhover-dropdown pe-0 py-0">
                            @php
                                $headerAvatar = auth()->user()?->getFirstMedia('image');
                            @endphp
                            <div class="d-flex profile-media"><img class="b-r-10"
                                    style="width:35px;height:35px;object-fit:cover;"
                                    src="{{ $headerAvatar ? $headerAvatar->getUrl() : asset('assets/images/dashboard/profile.png') }}" alt="">
                                <div class="flex-grow-1"><span>{{ ucfirst(auth()?->user()?->first_name) }}</span>
                                    <p class="mb-0">{{ auth()?->user()->role->name }} <i class="middle fa-solid fa-angle-down"></i></p>
                                </div>
                            </div>
                            <ul class="profile-dropdown onhover-show-div">
                                <li><a href="{{ route('admin.user.edit-profile',auth()->user()->role->name) }}"><i data-feather="user"></i><span>Meu Perfil</span></a></li>
                                <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i data-feather="log-in"> </i><span>Sair</span></a></li>
                                <form action="{{route('logout')}}" method="POST" class="d-none" id="logout-form">
                                    @csrf
                                </form>
                            </ul>
                        </li>
                    </ul>
                </div>
                <style>
                    /* Keeps the profile dropdown open for a short grace period
                       after the mouse leaves - the template's pure-CSS :hover
                       closes it the instant the pointer exits the icon's
                       bounding box, before it reaches the menu below. */
                    .profile-nav.hover-hold .onhover-show-div {
                        opacity: 1 !important;
                        transform: translateY(0) !important;
                        visibility: visible !important;
                        border-radius: 5px;
                        overflow: hidden;
                    }

                    /* Horizon/Configurações icons next to the profile - outside
                       the sidebar, so the sidebar-scoped .stroke-icon rules
                       don't size these; set it explicitly instead. */
                    .header-icon-link a {
                        display: flex;
                        align-items: center;
                        height: 100%;
                    }
                    .header-icon-link svg.stroke-icon {
                        width: 20px;
                        height: 20px;
                        stroke: var(--theme-default, #7366ff);
                        fill: none;
                    }
                </style>
                <script class="result-template" type="text/x-handlebars-template"><div class="ProfileCard u-cf">                        
<div class="ProfileCard-avatar"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-airplay m-0"><path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"></path><polygon points="12 15 17 21 7 21 12 15"></polygon></svg></div>
<div class="ProfileCard-details">
<div class="ProfileCard-realName">name</div>
</div>
</div></script>
                <script class="empty-template"
                    type="text/x-handlebars-template"><div class="EmptyMessage">Your search turned up 0 results. This most likely means the backend is down, yikes!</div></script>
            </div>
        </div>
        <!-- Page Header Ends -->
