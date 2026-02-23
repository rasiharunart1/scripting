@extends('layouts.template')

@section('title', 'Profile Settings')

@section('content')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user-cog mr-2"></i>Profile Settings
            </h1>
            {{-- <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard.index') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Profile</li>
                </ol>
            </nav> --}}
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Profile Information -->
                @include('profile.partials.update-profile-information-form')

                <!-- Update Password -->
                @include('profile.partials.update-password-form')
            </div>

            <div class="col-lg-4">
                <!-- User Info Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Account Information</h6>
                    </div>
                    <div class="card-body text-center">
                        <img class="img-profile rounded-circle mb-3"
                            src="{{ $user->avatar ?? asset('assets/img/undraw_profile.svg') }}"
                            style="width: 100px; height: 100px;">
                        <h5 class="card-title">{{ $user->name }}</h5>
                        <p class="card-text text-muted">{{ $user->email }}</p>
                        <hr>
                        <div class="row text-center">
                            <div class="col">
                                <strong>Joined</strong><br>
                                <small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small>
                            </div>
                            <!--<div class="col">-->
                            <!--    <strong>Status</strong><br>-->
                            <!--    @if ($user->email_verified_at)-->
                            <!--        <small class="text-success">-->
                            <!--            <i class="fas fa-check-circle"></i> Verified-->
                            <!--        </small>-->
                            <!--    @else-->
                            <!--        <small class="text-warning">-->
                            <!--            <i class="fas fa-exclamation-circle"></i> Unverified-->
                            <!--        </small>-->
                            <!--    @endif-->
                            <!--</div>-->
                        </div>

                        @if ($user->device)
                            <hr>
                            <div class="text-left">
                                <strong>Device Information:</strong><br>
                                <small>
                                    <strong>Code:</strong> {{ $user->device->device_code }}<br>
                                    <strong>Status:</strong>
                                    <span
                                        class="badge badge-{{ $user->device->status === 'online' ? 'success' : 'danger' }}">
                                        {{ ucfirst($user->device->status) }}
                                    </span><br>
                                    <strong>Last Seen:</strong>
                                    {{ $user->device->last_seen ? $user->device->last_seen->diffForHumans() : 'Never' }}
                                </small>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Delete Account -->
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .border-left-danger {
            border-left: 0.25rem solid #e74a3b !important;
        }
    </style>
@endpush
