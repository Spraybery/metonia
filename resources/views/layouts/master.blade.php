<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('page_title') | {{ Qs::getSystemName() }}</title>

    <link rel="icon" href="{{ Qs::getSystemLogo() }}">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    <link href="{{ asset('global_assets/css/icons/icomoon/styles.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/bootstrap_limitless.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/layout.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/components.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/colors.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/qs.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/theme-green-white.css') }}" rel="stylesheet" type="text/css">

    <style>
        .navbar-brand img { height: 32px; }
        .sidebar { z-index: 990; }
        .badge-role { font-size: 80%; padding: 4px 8px; border-radius: 4px; text-transform: uppercase; font-weight: 700; }
        .badge-admin { background-color: #064e3b; color: #ffffff; }
        .badge-manager { background-color: #059669; color: #ffffff; }
        .badge-storekeeper { background-color: #d97706; color: #ffffff; }
        .badge-accountant { background-color: #10b981; color: #ffffff; }
        .table td, .table th { vertical-align: middle; }
    </style>

    @stack('styles')
</head>
<body>

    <!-- Top Navigation Bar (White & Green Enterprise Theme) -->
    <div class="navbar navbar-expand-md navbar-light bg-white border-bottom shadow-sm py-1">
        <div class="navbar-brand py-1 px-3 d-flex align-items-center" style="background: #064e3b; margin: -0.25rem 0 -0.25rem -1.25rem; height: 100%;">
            <a href="{{ route('dashboard') }}" class="d-inline-flex align-items-center text-white font-weight-bold" style="font-size: 16px; letter-spacing: 0.5px; text-decoration: none;">
                <img src="{{ Qs::getSystemLogo() }}" alt="Metonia Logo" style="height: 38px; background:#fff; border-radius: 4px; padding: 2px 8px; max-width: 220px; object-fit: contain;">
            </a>
        </div>

        <div class="d-md-none">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-mobile">
                <i class="icon-tree5 text-success"></i>
            </button>
            <button class="navbar-toggler sidebar-mobile-main-toggle" type="button">
                <i class="icon-paragraph-justify3 text-success"></i>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="navbar-mobile">
            <span class="badge badge-success ml-md-3 mr-md-auto font-weight-semibold" style="background-color: #ecfdf5 !important; color: #065f46 !important; border: 1px solid #a7f3d0;">
                <i class="icon-pulse mr-1 text-success"></i> Nairobi Plant #1 Online
            </span>

            <ul class="navbar-nav align-items-center">
                @auth
                <li class="nav-item mr-3">
                    @php
                        $roleClass = match(Auth::user()->role) {
                            'Admin' => 'badge-admin',
                            'Manager' => 'badge-manager',
                            'General Supervisor' => 'badge-gensupervisor',
                            'Shopkeeper', 'Store Keeper' => 'badge-storekeeper',
                            'Accountant' => 'badge-accountant',
                            default => 'badge-secondary'
                        };
                    @endphp
                    <span class="badge badge-role {{ $roleClass }}">{{ Auth::user()->role }}</span>
                </li>

                <li class="nav-item dropdown dropdown-user">
                    <a href="#" class="navbar-nav-link d-flex align-items-center dropdown-toggle text-dark font-weight-semibold" data-toggle="dropdown">
                        <div class="rounded-circle text-white font-weight-bold d-inline-flex align-items-center justify-content-center mr-2 shadow-sm" style="width: 32px; height: 32px; background: #10b981;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <span>{{ Auth::user()->name }}</span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-password">
                            <i class="icon-lock2 text-success"></i> Change Password
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                        </form>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dropdown-item text-danger">
                            <i class="icon-switch2 text-danger"></i> Sign Out
                        </a>
                    </div>
                </li>
                @endauth
            </ul>
        </div>
    </div>
    <!-- /top navigation bar -->

    <!-- Page Content Container -->
    <div class="page-content">

        <!-- Main Sidebar -->
        <div class="sidebar sidebar-dark sidebar-main sidebar-expand-md">
            <div class="sidebar-content">
                <!-- User menu in sidebar -->
                <div class="sidebar-user">
                    <div class="card-body">
                        <div class="media">
                            <div class="mr-3">
                                <span class="btn btn-outline-light btn-icon btn-sm rounded-round">
                                    <i class="icon-user"></i>
                                </span>
                            </div>
                            <div class="media-body">
                                <div class="media-title font-weight-semibold">{{ Auth::user()->name ?? 'Guest' }}</div>
                                <div class="font-size-xs opacity-75">
                                    <i class="icon-pin font-size-sm mr-1"></i> Nairobi Assembly Plant #1
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation links -->
                <div class="card card-sidebar-mobile">
                    <ul class="nav nav-sidebar" data-nav-type="accordion">
                        <li class="nav-item-header"><div class="text-uppercase font-size-xs line-height-xs">Operations Floor</div></li>

                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="icon-home4"></i>
                                <span>Dashboard Analytics</span>
                            </a>
                        </li>

                        @if(Auth::user() && Auth::user()->canView('vehicles'))
                        <li class="nav-item">
                            <a href="{{ route('vehicles.index') }}" class="nav-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
                                <i class="icon-truck"></i>
                                <span>Build Pipeline (Job Cards)</span>
                            </a>
                        </li>
                        @endif

                        @if(Auth::user() && Auth::user()->canView('materials'))
                        <li class="nav-item nav-item-submenu {{ request()->routeIs('materials.*') ? 'nav-item-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('materials.*') ? 'active' : '' }}">
                                <i class="icon-boxes"></i>
                                <span>Store Inventory</span>
                            </a>
                            <ul class="nav nav-group-sub" data-submenu-title="Store Inventory" style="{{ request()->routeIs('materials.*') ? 'display: block;' : '' }}">
                                <li class="nav-item">
                                    <a href="{{ route('materials.index') }}" class="nav-link {{ request()->routeIs('materials.index') ? 'active' : '' }}">
                                        <i class="icon-list mr-2"></i> Store Inventory Catalog
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('materials.issuance') }}" class="nav-link {{ request()->routeIs('materials.issuance') ? 'active' : '' }}">
                                        <i class="icon-arrow-up5 mr-2"></i> Outward Material Issuance
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('materials.restock') }}" class="nav-link {{ request()->routeIs('materials.restock') ? 'active' : '' }}">
                                        <i class="icon-arrow-down5 mr-2"></i> Supplier Restock Data
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('materials.safety_stock') }}" class="nav-link {{ request()->routeIs('materials.safety_stock') ? 'active' : '' }}">
                                        <i class="icon-shield-check mr-2 text-primary"></i> Worker Safety &amp; PPE Stock
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif

                        @if(Auth::user() && Auth::user()->canView('supervisors'))
                        <li class="nav-item">
                            <a href="{{ route('supervisors.index') }}" class="nav-link {{ request()->routeIs('supervisors.*') ? 'active' : '' }}">
                                <i class="icon-users4"></i>
                                <span>Supervisors Roster</span>
                            </a>
                        </li>
                        @endif

                        @if(Auth::user() && Auth::user()->canView('tools'))
                        <li class="nav-item">
                            <a href="{{ route('tools.index') }}" class="nav-link {{ request()->routeIs('tools.*') ? 'active' : '' }}">
                                <i class="icon-wrench"></i>
                                <span>Tools &amp; Equipment Register</span>
                            </a>
                        </li>
                        @endif

                        @if(Auth::user() && Auth::user()->isAdmin())
                        <li class="nav-item-header"><div class="text-uppercase font-size-xs line-height-xs">Governance</div></li>
                        <li class="nav-item">
                            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <i class="icon-user-lock"></i>
                                <span>System Users &amp; RBAC</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <!-- /main sidebar -->

        <!-- Main Content Area -->
        <div class="content-wrapper">
            <!-- Page Header Bar -->
            <div class="page-header page-header-light border-bottom">
                <div class="page-header-content header-elements-md-inline py-2">
                    <div class="page-title d-flex py-1">
                        <h4><span class="font-weight-semibold">@yield('page_title', 'Metonia WMS')</span></h4>
                    </div>
                    <div class="header-elements d-none d-md-block">
                        <div class="breadcrumb justify-content-center">
                            <a href="{{ route('dashboard') }}" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                            <span class="breadcrumb-item active">@yield('page_title')</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /page header bar -->

            <!-- Content Area -->
            <div class="content">
                @include('partials.flash_message')
                @yield('content')
            </div>
            <!-- /content area -->

            <!-- Footer -->
            <div class="navbar navbar-expand-lg navbar-light border-top">
                <div class="text-center d-lg-none w-100">
                    <button type="button" class="navbar-toggler dropdown-toggle" data-toggle="collapse" data-target="#navbar-footer">
                        <i class="icon-unfold mr-2"></i> Footer
                    </button>
                </div>
                <div class="navbar-collapse collapse" id="navbar-footer">
                    <span class="navbar-text">
                        &copy; {{ date('Y') }} <strong>Metonia Enterprise Limited</strong> — Nairobi Assembly Plant #1.
                    </span>
                    <ul class="navbar-nav ml-lg-auto">
                        <li class="nav-item"><span class="navbar-text text-muted font-size-sm">System Ver 1.0.0 (Data-First Architecture)</span></li>
                    </ul>
                </div>
            </div>
            <!-- /footer -->
        </div>
        <!-- /main content area -->
    </div>
    <!-- /page content container -->

    <!-- Change Password Modal -->
    <div id="modal-password" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-slate-800 text-white">
                    <h6 class="modal-title font-weight-bold"><i class="icon-lock2 mr-2"></i> Change Account Password</h6>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('password.change') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-semibold">Current Password <span class="text-danger">*</span></label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-semibold">New Password (min 6) <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-semibold">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm font-weight-semibold">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Core JavaScript -->
    <script src="{{ asset('global_assets/js/main/jquery.min.js') }}"></script>
    <script src="{{ asset('global_assets/js/main/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('global_assets/js/plugins/loaders/blockui.min.js') }}"></script>

    <!-- DataTables & HTML5 Export Buttons -->
    <script src="{{ asset('global_assets/js/plugins/tables/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('global_assets/js/plugins/tables/datatables/extensions/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('global_assets/js/plugins/tables/datatables/extensions/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('global_assets/js/plugins/tables/datatables/extensions/pdfmake/vfs_fonts.min.js') }}"></script>
    <script src="{{ asset('global_assets/js/plugins/tables/datatables/extensions/buttons.min.js') }}"></script>

    <!-- Forms & Select2 -->
    <script src="{{ asset('global_assets/js/plugins/forms/selects/select2.min.js') }}"></script>

    <!-- Limitless Theme App JS -->
    <script src="{{ asset('assets/js/app.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTables with HTML5 export buttons matching SkullU standard
            $('.datatable-button-html5-columns').DataTable({
                autoWidth: false,
                dom: '<"datatable-header"fBl><"datatable-scroll-wrap"t><"datatable-footer"ip>',
                language: {
                    search: '<span>Filter:</span> _INPUT_',
                    searchPlaceholder: 'Type to filter...',
                    lengthMenu: '<span>Show:</span> _MENU_',
                    paginate: { 'first': 'First', 'last': 'Last', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
                },
                buttons: {
                    dom: {
                        button: {
                            className: 'btn btn-light btn-sm'
                        }
                    },
                    buttons: [
                        { extend: 'copyHtml5', className: 'btn btn-light btn-sm', text: '<i class="icon-copy3 mr-1"></i> Copy' },
                        { extend: 'csvHtml5', className: 'btn btn-light btn-sm', text: '<i class="icon-file-spreadsheet mr-1"></i> CSV' },
                        { extend: 'excelHtml5', className: 'btn btn-light btn-sm', text: '<i class="icon-file-excel mr-1"></i> Excel' },
                        { extend: 'pdfHtml5', className: 'btn btn-light btn-sm', text: '<i class="icon-file-pdf mr-1"></i> PDF' },
                        { extend: 'print', className: 'btn btn-light btn-sm', text: '<i class="icon-printer mr-1"></i> Print' }
                    ]
                }
            });

            // Initialize Select2 dropdowns
            if ($('.select-search').length > 0) {
                $('.select-search').select2();
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
