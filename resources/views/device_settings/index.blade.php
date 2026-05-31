@extends('layouts.template')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Pengaturan Device</h1>

        <div id="alert-container"></div>

        <div class="alert alert-info">
            <b>Device Code:</b> {{ $device->device_code }}
        </div>

        <form action="{{ route('device_settings.update') }}" method="POST" id="settings-form">
            @csrf

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Parameter Sensor &amp; Logging</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Threshold Suhu 1 (°C)</label>
                        <input type="number" step="0.01" name="temp1_threshold"
                            value="{{ old('temp1_threshold', $settings->temp1_threshold) }}"
                            class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Threshold Suhu 2 (°C)</label>
                        <input type="number" step="0.01" name="temp2_threshold"
                            value="{{ old('temp2_threshold', $settings->temp2_threshold) }}"
                            class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Hysteresis (°C)</label>
                        <input type="number" step="0.01" name="hysteresis"
                            value="{{ old('hysteresis', $settings->hysteresis) }}"
                            class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Interval Record Logging (detik)</label>
                        <input type="number" step="1" name="interval_record"
                            value="{{ old('interval_record', $settings->interval_record) }}"
                            class="form-control" required>
                        <small class="form-text text-muted">Interval penyimpanan histori data sensor ke database (dalam detik).</small>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-battery-full mr-1"></i> Pengaturan Charger Baterai A
                    </h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Mode Charger</label>
                        <select class="form-control" name="charger_mode" id="charger_mode_input">
                            <option value="manual" {{ old('charger_mode', $settings->charger_mode) === 'manual' ? 'selected' : '' }}>
                                Manual &mdash; dikontrol dari dashboard
                            </option>
                            <option value="auto" {{ old('charger_mode', $settings->charger_mode) === 'auto' ? 'selected' : '' }}>
                                Auto &mdash; dikontrol otomatis berdasarkan tegangan baterai
                            </option>
                        </select>
                        <small class="form-text text-muted">
                            <strong>Manual:</strong> Toggle charger dari dashboard secara langsung.<br>
                            <strong>Auto:</strong> ESP8266 mengatur charger secara otomatis berdasarkan threshold tegangan.
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">
                            Batas Tegangan Minimum (V)
                            <small class="text-muted font-weight-normal">— Charger ON saat baterai di bawah ini</small>
                        </label>
                        <div class="input-group">
                            <input type="number" step="0.1" min="10" max="13" name="charger_threshold_min"
                                value="{{ old('charger_threshold_min', $settings->charger_threshold_min) }}"
                                class="form-control" placeholder="contoh: 11.0">
                            <div class="input-group-append"><span class="input-group-text">V</span></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">
                            Batas Tegangan Maksimum (V)
                            <small class="text-muted font-weight-normal">— Charger OFF saat baterai mencapai ini</small>
                        </label>
                        <div class="input-group">
                            <input type="number" step="0.1" min="12" max="14" name="charger_threshold_max"
                                value="{{ old('charger_threshold_max', $settings->charger_threshold_max) }}"
                                class="form-control" placeholder="contoh: 13.5">
                            <div class="input-group-append"><span class="input-group-text">V</span></div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        Untuk sistem baterai 12V, nilai tipikal: Min <strong>11.0V</strong>, Max <strong>13.5V</strong>.
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="save-btn">
                <i class="fas fa-save mr-1"></i> Simpan Pengaturan
            </button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $('#settings-form').on('submit', function(e) {
            e.preventDefault();

            var temp1          = $('input[name="temp1_threshold"]').val();
            var temp2          = $('input[name="temp2_threshold"]').val();
            var hysteresis     = $('input[name="hysteresis"]').val();
            var intervalRecord = $('input[name="interval_record"]').val();
            var chargerMin     = parseFloat($('input[name="charger_threshold_min"]').val());
            var chargerMax     = parseFloat($('input[name="charger_threshold_max"]').val());

            if (!temp1 || !temp2 || !hysteresis || !intervalRecord) {
                showAlert('danger', 'Threshold suhu, hysteresis, dan interval record harus diisi.');
                return false;
            }

            if (!isNaN(chargerMin) && !isNaN(chargerMax) && chargerMin >= chargerMax) {
                showAlert('danger', 'Batas tegangan minimum harus lebih kecil dari maksimum.');
                return false;
            }

            var saveBtn = $('#save-btn');
            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

            $.ajax({
                url: '{{ route("device_settings.update") }}',
                method: 'POST',
                data: $(this).serialize(),
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Pengaturan berhasil disimpan.');
                    } else {
                        showAlert('danger', response.message || 'Gagal menyimpan pengaturan.');
                    }
                },
                error: function(xhr) {
                    var msg = 'Gagal menyimpan pengaturan.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            var errs = [];
                            $.each(xhr.responseJSON.errors, function(k, v) {
                                errs = errs.concat(v);
                            });
                            msg = errs.join('<br>');
                        }
                    }
                    showAlert('danger', msg);
                },
                complete: function() {
                    saveBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Pengaturan');
                }
            });
        });

        function showAlert(type, message) {
            var icon = (type === 'success') ? 'check-circle' : 'exclamation-triangle';
            var html = '<div class="alert alert-' + type + ' alert-dismissible fade show">' +
                       '<i class="fas fa-' + icon + ' mr-2"></i>' + message +
                       '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                       '</div>';
            $('#alert-container').html(html);
            $('html, body').animate({ scrollTop: 0 }, 300);
        }
    </script>
@endsection
