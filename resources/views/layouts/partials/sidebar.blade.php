<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/') }}">
        <div class="sidebar-brand-icon">
            <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" width="50" height="50">
        </div>
        <!-- <div class="sidebar-brand-text mx-3">PLTS Monitoring</div> -->
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard.index') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Nav Item - Device -->
    <li class="nav-item {{ request()->is('device-settings*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('device_settings.index') }}">
            <i class="fas fa-fw fa-microchip"></i>
            <span>Device Settings</span>
        </a>
    </li>

    <!-- Nav Item - Log -->
    <li class="nav-item {{ request()->is('logs*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('logs.index') }}">
            <i class="fas fa-fw fa-list"></i>
            <span>Log</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        User
    </div>

    <!-- Nav Item - Profile (Laravel Breeze) -->
    <li class="nav-item {{ request()->is('profile*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('profile.edit') }}">
            <i class="fas fa-fw fa-user"></i>
            <span>Profile</span>
        </a>
    </li>

    <!-- Nav Item - Logout (dengan modal konfirmasi) -->
    <li class="nav-item">
        <a class="nav-link" href="#" data-toggle="modal" data-target="#logoutModal">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>

        <!-- Form logout tetap ada (disubmit saat konfirmasi) -->
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">
                    <i class="fas fa-sign-out-alt mr-2"></i>Confirm Logout
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                Apakah Anda yakin ingin keluar dari aplikasi?
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Batal
                </button>
                <button type="button" class="btn btn-primary"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-check mr-1"></i>Keluar
                </button>
            </div>
        </div>
    </div>
</div>
