<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SlotMaker | Dashboard</title>

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/jqvmap/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    @yield('style')


</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">



        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('home') }}" class="nav-link">Home</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link"
                        href="{{ route('admin.changePassword', \Illuminate\Support\Facades\Auth::id()) }}">
                        {{ auth()->user()->name }}

                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        | Balance: {{ number_format(auth()->user()->wallet->balanceFloat, 2) }}
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link" href="#"
                        onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>

                </li>

            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            {{-- <a href="{{ route('home') }}" class="brand-link">
            <img src="{{ asset('img/slot_maker.jpg') }}" alt="AdminLTE Logo"
                class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">GoldenJack</span>
            </a> --}}
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="brand-link">
                <img src="{{ $adminLogo }}" alt="Admin Logo" class="brand-image img-circle elevation-3"
                    style="opacity: .8">
                {{-- <span class="brand-text font-weight-light">GoldenJack</span> --}}
                <span class="brand-text font-weight-light">{{ $siteName }}</span>
            </a>


            <!-- Sidebar -->
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <li class="nav-item menu-open">
                            <a href="{{ route('home') }}" class="nav-link active">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    Dashboard
                                </p>
                            </a>
                        </li>
                        @can('senior_access')
                            <li class="nav-item">
                                <a href="{{ route('admin.owner.index') }}" class="nav-link">
                                    <i class="fas fa-users"></i>
                                    <p>
                                        Owner List
                                    </p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('admin/slot/daily-summaries') }}" class="nav-link">
                                    <i class="fas fa-users"></i>
                                    <p>
                                        Daily W/L Report
                                    </p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('admin/slot/report') }}" class="nav-link">
                                    <i class="fas fa-users"></i>
                                    <p>
                                        W/L Report V1
                                    </p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('admin/slot/reports/senior') }}" class="nav-link">
                                    <i class="fas fa-users"></i>
                                    <p>
                                        W/L Report V2
                                    </p>
                                </a>
                            </li>
                 
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-tools"></i>
                                <p>
                                    Shan
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ url('admin/shan-report') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Win/Lose</p>
                                    </a>
                                </li>

                            </ul>
                        </li>
                        @endcan
                        @can('owner_access')
                            <li class="nav-item">
                                <a href="{{ url('admin/slot/report') }}" class="nav-link">
                                    <i class="fas fa-users"></i>
                                    <p>
                                        W/L Report V1
                                    </p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ url('admin/slot/reports/owner') }}" class="nav-link">
                                    <i class="fas fa-users"></i>
                                    <p>
                                        W/L Report V2
                                    </p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.GetOwnerPlayerList') }}" class="nav-link">
                                    <i class="fas fa-users"></i>
                                    <p>
                                        Player List
                                    </p>
                                </a>
                            </li>
                        @endcan
                        @can('agent_index')
                            <li class="nav-item">
                                <a href="{{ route('admin.agent.index') }}" class="nav-link">
                                    <i class="fas fa-users"></i>
                                    <p>
                                        Agent List
                                    </p>
                                </a>
                            </li>
                        @endcan
                        @can('player_index')
                            <li class="nav-item">
                                <a href="{{ route('admin.player.index') }}" class="nav-link">
                                    <i class="fas fa-users"></i>
                                    <p>
                                        Player List
                                    </p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('admin/slot/report') }}" class="nav-link">
                                    <i class="fas fa-users"></i>
                                    <p>
                                        W/L Report V1
                                    </p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ url('admin/slot/reports/agent') }}" class="nav-link">
                                    <i class="fas fa-users"></i>
                                    <p>
                                        W/L Report V2
                                    </p>
                                </a>
                            </li>
                        @endcan
                        @can('contact')
                            <li class="nav-item">
                                <a href="{{ route('admin.contact.index') }}" class="nav-link">
                                    <i class="fas fa-address-book"></i>
                                    <p>
                                        Contact
                                    </p>
                                </a>
                            </li>
                        @endcan
                        @can('bank')
                            <li class="nav-item">
                                <a href="{{ route('admin.bank.index') }}" class="nav-link">
                                    <i class="fas fa-address-book"></i>
                                    <p>
                                        Bank
                                    </p>
                                </a>
                            </li>
                        @endcan
                        @can('withdraw')
                            <li class="nav-item">
                                <a href="{{ route('admin.agent.withdraw') }}" class="nav-link">
                                    <i class="fas fa-address-book"></i>
                                    <p>
                                        WithDraw Request
                                    </p>
                                </a>
                            </li>
                        @endcan
                        @can('deposit')
                            <li class="nav-item">
                                <a href="{{ route('admin.agent.deposit') }}" class="nav-link">
                                    <i class="fas fa-address-book"></i>
                                    <p>
                                        Deposit Request
                                    </p>
                                </a>
                            </li>
                        @endcan
                        <li class="nav-item">
                            <a href="{{ route('admin.transferLog') }}" class="nav-link">
                                <i class="fas fa-address-book"></i>
                                <p>
                                    Transaction Log
                                </p>
                            </a>
                        </li>
                        @can('senior_access')
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon fas fa-tools"></i>
                                    <p>
                                        GSC Settings
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.gameLists.index') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>GSC GameList</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.gametypes.index') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>GSC GameProvider</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endcan
                        @can('owner_access')
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon fas fa-tools"></i>
                                    <p>
                                        General Settings
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.text.index') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>BannerText</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.banners.index') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Banner</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.adsbanners.index') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Banner Ads</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.promotions.index') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Promotions</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endcan

                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <div class="content-wrapper">

            @yield('content')
        </div>
        <footer class="main-footer">
            <strong>Copyright &copy; 2024 <a href="">SlotMaker</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 3.2.0
            </div>
        </footer>

        <aside class="control-sidebar control-sidebar-dark">
        </aside>
    </div>

    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('js/adminlte.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    @yield('script')
    <script>
        var errorMessage = @json(session('error'));
        var successMessage = @json(session('success'));

        @if (session()->has('success'))
            toastr.success(successMessage)
        @elseif (session()->has('error'))
            toastr.error(errorMessage)
        @endif
    </script>
    <script>
        $(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });
            $("#mytable").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        });
    </script>
</body>

</html>
