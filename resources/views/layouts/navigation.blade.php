@php $user = Auth::user(); @endphp

<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Brand/Logo untuk mobile -->
    <a class="navbar-brand d-md-none" href="{{ route('dashboard.index') }}">
        <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" width="30" height="30">
        MDPOWER
    </a>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">

        <!-- Device Status Indicator -->
        @if ($user->device)
            <li class="nav-item dropdown no-arrow mx-1">
                <span class="nav-link">
                    <i
                        class="fas fa-microchip fa-fw {{ $user->device->isOnline() ? 'text-success' : 'text-danger' }}"></i>
                    <span
                        class="d-none d-sm-inline text-xs {{ $user->device->isOnline() ? 'text-success' : 'text-danger' }}">
                        {{ $user->device->status }}
                    </span>
                </span>
            </li>
        @endif

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ $user->name }}</span>
                <img class="img-profile rounded-circle"
                    src="{{ $user->avatar ?? asset('assets/img/undraw_profile.svg') }}">
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <div class="dropdown-header">
                    <strong>{{ $user->name }}</strong><br>
                    <small class="text-muted">{{ $user->email }}</small>
                </div>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                <a class="dropdown-item" href="{{ route('device_settings.index') }}">
                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                    Device Settings
                </a>
                <a class="dropdown-item" href="{{ route('logs.index') }}">
                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logs
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>

    </ul>

</nav>
