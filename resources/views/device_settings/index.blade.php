@extends('layouts.template')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Pengaturan Device</h1>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="alert alert-info">
            <b>Device Code:</b> {{ $device->device_code }}
        </div>

        <!-- FORM SUBMIT BIASA - TANPA AJAX -->
        <form action="{{ route('device_settings.update') }}" method="POST" id="settings-form">
            @csrf

            <div class="form-group">
                <label>Threshold Suhu 1</label>
                <input type="number" step="0.01" name="temp1_threshold"
                    value="{{ old('temp1_threshold', $settings->temp1_threshold) }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Threshold Suhu 2</label>
                <input type="number" step="0.01" name="temp2_threshold"
                    value="{{ old('temp2_threshold', $settings->temp2_threshold) }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Hysteresis</label>
                <input type="number" step="0.01" name="hysteresis"
                    value="{{ old('hysteresis', $settings->hysteresis) }}" class="form-control" required>
            </div>


            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>


    </div>
@endsection

@section('scripts')
    <script>
        $('#settings-form').on('submit', function(e)) {
            console.log('Form submitted');
            console.log('Form data : ', $(this).serialize());

            var temp1 = $('input[name="temp1_threshold"]').val();
            var temp2 = $('input[name="temp2_threshold"]').val();
            var hysteresis = $('input[name="hysteresis"]').val();

            if (!temp1 || !temp2 || !hysteresis) {
                alert('Threshold suhu dan hysteresis harus diisi');
                e.preventDefault();
                return false;
            }
            console.log('Validation passed');
            return true;
        }
    </script>
@endsection
