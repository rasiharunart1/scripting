
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Error - MDPOWER')</title>
    <link href="{{ asset('assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="{{ asset('assets/css/sb-admin-2.min.css') }}" rel="stylesheet">
</head>

<body id="page-top">

    <div id="wrapper">
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <div class="container-fluid">
                    <div class="text-center" style="margin-top: 100px;">
                        <div class="error mx-auto" data-text="@yield('code')">@yield('code')</div>
                        <p class="lead text-gray-800 mb-5">@yield('message')</p>
                        <p class="text-gray-500 mb-0">@yield('description', 'Sepertinya Anda menemukan masalah...')</p>
                        <a href="{{ url('/') }}">&larr; Kembali ke Dashboard</a>
                    </div>
                </div>
            </div>

            <footer class="sticky-footer bg-white" style="margin-top: auto;">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; MDPOWER {{ date('Y') }}</span>
                    </div>
                </div>
            </footer>

        </div>
    </div>

    <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('assets/js/sb-admin-2.min.js') }}"></script>

</body>

</html>
