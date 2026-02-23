@extends('layouts.template')

@section('content')
    <div class="container-fluid">
        {{ $slot ?? '' }}
        @yield('profile-content')
    </div>
@endsection
