@extends('layouts.template')

@section('content')
    @php
        // Define sensor ranges directly in code
        $ranges = [
            'battery_a' => ['min' => 10.5, 'max' => 14.4],
            'battery_b' => ['min' => 10.5, 'max' => 14.4],
            'battery_c' => ['min' => 10.5, 'max' => 14.4],
            'battery_d' => ['min' => 10.5, 'max' => 14.4],

            'pln_volt' => ['min' => 180, 'max' => 240],
            'pln_current' => ['min' => 0, 'max' => 32],
            'pln_power' => ['min' => 0, 'max' => 100],

            'temperature_1' => ['min' => 0, 'max' => 60],
            'temperature_2' => ['min' => 0, 'max' => 60],
        ];

        // Helper to compute percent with clamping
        if (!function_exists('sensor_pct')) {
            function sensor_pct($key, $value, $ranges)
            {
                $range = $ranges[$key] ?? ['min' => 0, 'max' => 100];
                $min = (float) ($range['min'] ?? 0);
                $max = (float) ($range['max'] ?? 100);
                $den = max($max - $min, 0.000001);
                $pct = (($value - $min) / $den) * 100;
                return max(0, min(100, $pct));
            }
        }

        // Helper to check if value is out of range
        if (!function_exists('is_alert')) {
            function is_alert($key, $value, $ranges)
            {
                $range = $ranges[$key] ?? null;
                if (!$range) return false;
                return $value < $range['min'] || $value > $range['max'];
            }
        }

        // Helper to get alert type
        if (!function_exists('get_alert_type')) {
            function get_alert_type($key, $value, $ranges)
            {
                $range = $ranges[$key] ?? null;
                if (!$range) return null;
                if ($value < $range['min']) return 'LOW';
                if ($value > $range['max']) return 'HIGH';
                return null;
            }
        }

        // Inisialisasi status fan dari data terakhir
        $relay1Init = (string) ($latestData->relay_1 ?? '0');
        $relay2Init = (string) ($latestData->relay_2 ?? '0');
        $relayChargerInit = (string) ($latestData->relay_charger ?? '0');
        $fan1On = $relay1Init === '1' || strtolower($relay1Init) === 'on' || strtolower($relay1Init) === 'true';
        $fan2On = $relay2Init === '1' || strtolower($relay2Init) === 'on' || strtolower($relay2Init) === 'true';
        $chargerOn = $relayChargerInit === '1' || strtolower($relayChargerInit) === 'on' || strtolower($relayChargerInit) === 'true';
    @endphp

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard Monitoring PLTS</h1>

            <!-- Alert Summary Badge -->
            <div id="alert-summary" class="d-none">
                <span class="badge badge-danger p-2">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <span id="alert-count">0</span> Alert(s) Active
                </span>
            </div>
        </div>

        <!-- ═══════ Row 1: Device Status ═══════ -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Device Status
                                </div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                            <span
                                                class="badge badge-{{ $device->status == 'online' ? 'success' : 'danger' }}"
                                                id="device_status">
                                                {{ $device->status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 mb-1">
                                    Last seen: <span
                                        id="last_seen">{{ $device->last_seen?->diffForHumans() ?? 'Never' }}</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-wifi fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════ Row 2: Fan 1, Fan 2, Charger Status (read-only) ═══════ -->
        <div class="row">
            <!-- Fan 1 Status -->
            <div class="col-md-4 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Fan 1 Status
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <span id="fan1_badge"
                                        class="badge {{ $fan1On ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $fan1On ? 'ON' : 'OFF' }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <span id="fan1_text">{{ $fan1On ? 'Running' : 'Stopped' }}</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i id="fan1_icon"
                                    class="fas fa-fan fa-2x text-gray-300 {{ $fan1On ? 'fa-spin' : '' }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fan 2 Status -->
            <div class="col-md-4 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Fan 2 Status
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <span id="fan2_badge"
                                        class="badge {{ $fan2On ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $fan2On ? 'ON' : 'OFF' }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <span id="fan2_text">{{ $fan2On ? 'Running' : 'Stopped' }}</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i id="fan2_icon"
                                    class="fas fa-fan fa-2x text-gray-300 {{ $fan2On ? 'fa-spin' : '' }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charger Status (Read-Only — no toggle for user role) -->
            <!--<div class="col-md-4 mb-4">-->
            <!--    <div class="card border-left-warning shadow h-100 py-2">-->
            <!--        <div class="card-body">-->
            <!--            <div class="row no-gutters align-items-center">-->
            <!--                <div class="col">-->
            <!--                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">-->
            <!--                        Charger Battery A-->
            <!--                    </div>-->
            <!--                    <div class="h5 mb-0 font-weight-bold text-gray-800">-->
            <!--                        <span id="charger_badge"-->
            <!--                            class="badge {{ $chargerOn ? 'badge-success' : 'badge-secondary' }}">-->
            <!--                            {{ $chargerOn ? 'ON' : 'OFF' }}-->
            <!--                        </span>-->
            <!--                    </div>-->
            <!--                    <div class="text-xs text-gray-500 mt-1">-->
            <!--                        <span id="charger_text">{{ $chargerOn ? 'Charging' : 'Stopped' }}</span>-->
            <!--                    </div>-->
            <!--                </div>-->
            <!--                <div class="col-auto">-->
            <!--                    <i class="fas fa-charging-station fa-2x text-gray-300"></i>-->
            <!--                </div>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--</div>-->
        </div>

        <!-- ═══════ Row 3: Battery A, B, C, D ═══════ -->
        <div class="row">
            @foreach (['a', 'b', 'c', 'd'] as $batt)
                @php
                    $key = 'battery_' . $batt;
                    $val = (float) ($latestData?->$key ?? 0);
                    $pct = sensor_pct($key, $val, $ranges);
                    $range = $ranges[$key];
                    $isAlert = is_alert($key, $val, $ranges);
                    $alertType = get_alert_type($key, $val, $ranges);
                @endphp
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card shadow h-100 py-2 sensor-card {{ $isAlert ? 'sensor-alert sensor-alert-pulse border-left-danger' : 'border-left-primary' }}"
                        id="card_battery_{{ $batt }}"
                        data-sensor="battery_{{ $batt }}">
                        <div class="card-body">
                            <button class="btn btn-sm btn-link position-absolute settings-btn"
                                    style="top: 5px; right: 5px; z-index: 10; color: #858796; padding: 2px 6px;"
                                    onclick="openSettingsModal('battery_{{ $batt }}')"
                                    title="Atur Threshold Alert">
                                <i class="fas fa-cog"></i>
                            </button>
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold {{ $isAlert ? 'text-danger' : 'text-primary' }} text-uppercase mb-1">
                                        Battery {{ strtoupper($batt) }}
                                        <i class="fas fa-exclamation-triangle alert-icon {{ $isAlert ? '' : 'd-none' }}"></i>
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold {{ $isAlert ? 'sensor-value-danger' : 'text-gray-800' }}"
                                        id="battery_{{ $batt }}">
                                        {{ number_format($val, 2) }} V
                                        @if ($isAlert)
                                            <span class="badge badge-danger alert-badge">{{ $alertType }}</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-muted mt-1" id="range_battery_{{ $batt }}">
                                        Range: <span class="range-min">{{ $range['min'] }}</span>V - <span class="range-max">{{ $range['max'] }}</span>V
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="progress progress-sm mr-2" style="width: 90px;">
                                        <div class="progress-bar {{ $isAlert ? 'bg-danger' : 'bg-primary' }}"
                                            id="battery_{{ $batt }}_bar"
                                            style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- ═══════ Row 4: PLN Parameters (3 columns) ═══════ -->
        <div class="row">
            <!-- PLN Voltage -->
            @php
                $plnVoltVal = (float) ($latestData->pln_volt ?? 0);
                $plnVoltPct = sensor_pct('pln_volt', $plnVoltVal, $ranges);
                $plnVoltAlert = is_alert('pln_volt', $plnVoltVal, $ranges);
                $plnVoltAlertType = get_alert_type('pln_volt', $plnVoltVal, $ranges);
            @endphp
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 sensor-card {{ $plnVoltAlert ? 'sensor-alert sensor-alert-pulse border-left-danger' : 'border-left-success' }}"
                    id="card_pln_volt" data-sensor="pln_volt">
                    <div class="card-body">
                        <button class="btn btn-sm btn-link position-absolute settings-btn"
                                style="top: 5px; right: 5px; z-index: 10; color: #858796; padding: 2px 6px;"
                                onclick="openSettingsModal('pln_volt')"
                                title="Atur Threshold Alert">
                            <i class="fas fa-cog"></i>
                        </button>
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold {{ $plnVoltAlert ? 'text-danger' : 'text-success' }} text-uppercase mb-1">
                                    PLN Voltage
                                    <i class="fas fa-exclamation-triangle alert-icon {{ $plnVoltAlert ? '' : 'd-none' }}"></i>
                                </div>
                                <div class="h5 mb-0 font-weight-bold {{ $plnVoltAlert ? 'sensor-value-danger' : 'text-gray-800' }}"
                                    id="pln_volt">
                                    {{ number_format($plnVoltVal, 2) }} V
                                    @if ($plnVoltAlert)
                                        <span class="badge badge-danger alert-badge">{{ $plnVoltAlertType }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-muted mt-1" id="range_pln_volt">
                                    Range: <span class="range-min">{{ $ranges['pln_volt']['min'] }}</span>V - <span class="range-max">{{ $ranges['pln_volt']['max'] }}</span>V
                                </div>
                                <div class="progress progress-sm mt-2">
                                    <div class="progress-bar {{ $plnVoltAlert ? 'bg-danger' : 'bg-success' }}"
                                        id="pln_volt_bar" style="width: {{ $plnVoltPct }}%"></div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-bolt fa-2x {{ $plnVoltAlert ? 'text-danger blink-icon' : 'text-gray-300' }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PLN Current -->
            @php
                $plnCurrentVal = (float) ($latestData->pln_current ?? 0);
                $plnCurrentPct = sensor_pct('pln_current', $plnCurrentVal, $ranges);
                $plnCurrentAlert = is_alert('pln_current', $plnCurrentVal, $ranges);
                $plnCurrentAlertType = get_alert_type('pln_current', $plnCurrentVal, $ranges);
            @endphp
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 sensor-card {{ $plnCurrentAlert ? 'sensor-alert sensor-alert-pulse border-left-danger' : 'border-left-warning' }}"
                    id="card_pln_current" data-sensor="pln_current">
                    <div class="card-body">
                        <button class="btn btn-sm btn-link position-absolute settings-btn"
                                style="top: 5px; right: 5px; z-index: 10; color: #858796; padding: 2px 6px;"
                                onclick="openSettingsModal('pln_current')"
                                title="Atur Threshold Alert">
                            <i class="fas fa-cog"></i>
                        </button>
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold {{ $plnCurrentAlert ? 'text-danger' : 'text-warning' }} text-uppercase mb-1">
                                    PLN Current
                                    <i class="fas fa-exclamation-triangle alert-icon {{ $plnCurrentAlert ? '' : 'd-none' }}"></i>
                                </div>
                                <div class="h5 mb-0 font-weight-bold {{ $plnCurrentAlert ? 'sensor-value-danger' : 'text-gray-800' }}"
                                    id="pln_current">
                                    {{ number_format($plnCurrentVal, 2) }} A
                                    @if ($plnCurrentAlert)
                                        <span class="badge badge-danger alert-badge">{{ $plnCurrentAlertType }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-muted mt-1" id="range_pln_current">
                                    Range: <span class="range-min">{{ $ranges['pln_current']['min'] }}</span>A - <span class="range-max">{{ $ranges['pln_current']['max'] }}</span>A
                                </div>
                                <div class="progress progress-sm mt-2">
                                    <div class="progress-bar {{ $plnCurrentAlert ? 'bg-danger' : 'bg-warning' }}"
                                        id="pln_current_bar" style="width: {{ $plnCurrentPct }}%"></div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-bolt fa-2x {{ $plnCurrentAlert ? 'text-danger blink-icon' : 'text-gray-300' }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PLN Power -->
            @php
                $plnPowerVal = (float) ($latestData->pln_power ?? 0);
                $plnPowerPct = sensor_pct('pln_power', $plnPowerVal, $ranges);
                $plnPowerAlert = is_alert('pln_power', $plnPowerVal, $ranges);
                $plnPowerAlertType = get_alert_type('pln_power', $plnPowerVal, $ranges);
            @endphp
            <div class="col-xl-4 col-md-12 mb-4">
                <div class="card shadow h-100 py-2 sensor-card {{ $plnPowerAlert ? 'sensor-alert sensor-alert-pulse border-left-danger' : 'border-left-info' }}"
                    id="card_pln_power" data-sensor="pln_power">
                    <div class="card-body">
                        <button class="btn btn-sm btn-link position-absolute settings-btn"
                                style="top: 5px; right: 5px; z-index: 10; color: #858796; padding: 2px 6px;"
                                onclick="openSettingsModal('pln_power')"
                                title="Atur Threshold Alert">
                            <i class="fas fa-cog"></i>
                        </button>
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold {{ $plnPowerAlert ? 'text-danger' : 'text-info' }} text-uppercase mb-1">
                                    PLN Power
                                    <i class="fas fa-exclamation-triangle alert-icon {{ $plnPowerAlert ? '' : 'd-none' }}"></i>
                                </div>
                                <div class="h5 mb-0 font-weight-bold {{ $plnPowerAlert ? 'sensor-value-danger' : 'text-gray-800' }}"
                                    id="pln_power">
                                    {{ number_format($plnPowerVal, 2) }} W
                                    @if ($plnPowerAlert)
                                        <span class="badge badge-danger alert-badge">{{ $plnPowerAlertType }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-muted mt-1" id="range_pln_power">
                                    Range: <span class="range-min">{{ $ranges['pln_power']['min'] }}</span>W - <span class="range-max">{{ $ranges['pln_power']['max'] }}</span>W
                                </div>
                                <div class="progress progress-sm mt-2">
                                    <div class="progress-bar {{ $plnPowerAlert ? 'bg-danger' : 'bg-info' }}"
                                        id="pln_power_bar" style="width: {{ $plnPowerPct }}%"></div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-plug fa-2x {{ $plnPowerAlert ? 'text-danger blink-icon' : 'text-gray-300' }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════ Row 5: Temperature 1 & 2 ═══════ -->
        <div class="row">
            <!-- Temperature 1 -->
            @php
                $t1Val = (float) ($latestData->temperature_1 ?? 0);
                $t1Pct = sensor_pct('temperature_1', $t1Val, $ranges);
                $t1Alert = is_alert('temperature_1', $t1Val, $ranges);
                $t1AlertType = get_alert_type('temperature_1', $t1Val, $ranges);
            @endphp
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 sensor-card {{ $t1Alert ? 'sensor-alert sensor-alert-pulse' : '' }} border-left-danger"
                    id="card_temperature_1" data-sensor="temperature_1">
                    <div class="card-body">
                        <button class="btn btn-sm btn-link position-absolute settings-btn"
                                style="top: 5px; right: 5px; z-index: 10; color: #858796; padding: 2px 6px;"
                                onclick="openSettingsModal('temperature_1')"
                                title="Atur Threshold Alert">
                            <i class="fas fa-cog"></i>
                        </button>
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Temperature 1
                                    <i class="fas fa-exclamation-triangle alert-icon {{ $t1Alert ? '' : 'd-none' }}"></i>
                                </div>
                                <div class="h5 mb-0 font-weight-bold {{ $t1Alert ? 'sensor-value-danger' : 'text-gray-800' }}"
                                    id="temperature_1">
                                    {{ number_format($t1Val, 2) }} °C
                                    @if ($t1Alert)
                                        <span class="badge badge-danger alert-badge">{{ $t1AlertType }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-muted mt-1" id="range_temperature_1">
                                    Range: <span class="range-min">{{ $ranges['temperature_1']['min'] }}</span>°C -
                                    <span class="range-max">{{ $ranges['temperature_1']['max'] }}</span>°C
                                </div>
                                <div class="progress progress-sm mt-2">
                                    <div class="progress-bar bg-danger" id="temperature_1_bar"
                                        style="width: {{ $t1Pct }}%"></div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-thermometer-half fa-2x {{ $t1Alert ? 'text-danger blink-icon' : 'text-gray-300' }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Temperature 2 -->
            @php
                $t2Val = (float) ($latestData->temperature_2 ?? 0);
                $t2Pct = sensor_pct('temperature_2', $t2Val, $ranges);
                $t2Alert = is_alert('temperature_2', $t2Val, $ranges);
                $t2AlertType = get_alert_type('temperature_2', $t2Val, $ranges);
            @endphp
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 sensor-card {{ $t2Alert ? 'sensor-alert sensor-alert-pulse' : '' }} border-left-danger"
                    id="card_temperature_2" data-sensor="temperature_2">
                    <div class="card-body">
                        <button class="btn btn-sm btn-link position-absolute settings-btn"
                                style="top: 5px; right: 5px; z-index: 10; color: #858796; padding: 2px 6px;"
                                onclick="openSettingsModal('temperature_2')"
                                title="Atur Threshold Alert">
                            <i class="fas fa-cog"></i>
                        </button>
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Temperature 2
                                    <i class="fas fa-exclamation-triangle alert-icon {{ $t2Alert ? '' : 'd-none' }}"></i>
                                </div>
                                <div class="h5 mb-0 font-weight-bold {{ $t2Alert ? 'sensor-value-danger' : 'text-gray-800' }}"
                                    id="temperature_2">
                                    {{ number_format($t2Val, 2) }} °C
                                    @if ($t2Alert)
                                        <span class="badge badge-danger alert-badge">{{ $t2AlertType }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-muted mt-1" id="range_temperature_2">
                                    Range: <span class="range-min">{{ $ranges['temperature_2']['min'] }}</span>°C -
                                    <span class="range-max">{{ $ranges['temperature_2']['max'] }}</span>°C
                                </div>
                                <div class="progress progress-sm mt-2">
                                    <div class="progress-bar bg-danger" id="temperature_2_bar"
                                        style="width: {{ $t2Pct }}%"></div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-thermometer-half fa-2x {{ $t2Alert ? 'text-danger blink-icon' : 'text-gray-300' }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Toast Container for Alert Notifications -->
    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

    <!-- Settings Modal (Threshold per Widget) -->
    <div class="modal fade" id="settingsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-cog mr-2"></i>
                        Atur Threshold Alert
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="settingsForm">
                        <input type="hidden" id="settings_sensor_key">

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <small>Konfigurasi nilai min/max untuk <strong id="settings_sensor_name"></strong></small>
                        </div>

                        <div class="form-group">
                            <label for="settings_min">Nilai Minimum <span id="settings_unit"></span></label>
                            <input type="number" class="form-control" id="settings_min" step="0.1" required>
                            <small class="form-text text-muted">Alert jika nilai turun di bawah ini</small>
                        </div>

                        <div class="form-group">
                            <label for="settings_max">Nilai Maximum <span id="settings_unit_max"></span></label>
                            <input type="number" class="form-control" id="settings_max" step="0.1" required>
                            <small class="form-text text-muted">Alert jika nilai melebihi ini</small>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <small>Pengaturan tersimpan khusus untuk akun Anda</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="resetToDefault()">
                        <i class="fas fa-undo mr-1"></i> Reset ke Default
                    </button>
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="saveSettings()">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    @keyframes blink-alert {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    @keyframes pulse-border {
        0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
        20%, 40%, 60%, 80% { transform: translateX(2px); }
    }

    .sensor-alert {
        animation: blink-alert 1s ease-in-out infinite;
        border-left-color: #dc3545 !important;
        border-left-width: 4px !important;
    }

    .sensor-alert-pulse {
        animation: blink-alert 1s ease-in-out infinite, pulse-border 1.5s infinite;
    }

    .alert-icon {
        color: #dc3545;
        margin-left: 8px;
        animation: blink-alert 0.5s ease-in-out infinite;
    }

    .blink-icon {
        animation: blink-alert 0.5s ease-in-out infinite;
    }

    .sensor-value-danger {
        color: #dc3545 !important;
    }

    .alert-badge {
        font-size: 0.65rem;
        padding: 2px 6px;
        margin-left: 5px;
        animation: blink-alert 0.8s ease-in-out infinite;
    }

    .alert-toast {
        min-width: 300px;
        animation: shake 0.5s ease-in-out;
    }

    #alert-summary {
        animation: pulse-border 2s infinite;
        font-size: 0.9rem;
    }

    .sensor-card {
        position: relative;
        transition: all 0.3s ease;
    }

    .progress-bar {
        transition: width 0.5s ease, background-color 0.3s ease;
    }
</style>
@endpush

@push('scripts')
<script>
    const UPDATE_INTERVAL = 1000;
    const MAX_CONSECUTIVE_ERRORS = 3;
    const RETRY_DELAY = 10000;

    // Default ranges — fallback jika DB belum ada data
    const DEFAULT_RANGES = {
        battery_a:    { min: 10.5, max: 14.4 },
        battery_b:    { min: 10.5, max: 14.4 },
        battery_c:    { min: 10.5, max: 14.4 },
        battery_d:    { min: 10.5, max: 14.4 },
        pln_volt:     { min: 180,  max: 240   },
        pln_current:  { min: 0,    max: 32    },
        pln_power:    { min: 0,    max: 100   },
        temperature_1:{ min: 0,    max: 60    },
        temperature_2:{ min: 0,    max: 60    },
    };

    // ranges bersifat mutable — di-override dari DB setelah load
    let ranges = Object.assign({}, DEFAULT_RANGES);

    const originalColors = {
        battery_a: { border: 'border-left-primary', bar: 'bg-primary', text: 'text-primary' },
        battery_b: { border: 'border-left-primary', bar: 'bg-primary', text: 'text-primary' },
        battery_c: { border: 'border-left-primary', bar: 'bg-primary', text: 'text-primary' },
        battery_d: { border: 'border-left-primary', bar: 'bg-primary', text: 'text-primary' },
        pln_volt: { border: 'border-left-success', bar: 'bg-success', text: 'text-success' },
        pln_current: { border: 'border-left-warning', bar: 'bg-warning', text: 'text-warning' },
        pln_power: { border: 'border-left-info', bar: 'bg-info', text: 'text-info' },
        temperature_1: { border: 'border-left-danger', bar: 'bg-danger', text: 'text-danger' },
        temperature_2: { border: 'border-left-danger', bar: 'bg-danger', text: 'text-danger' },
    };

    const units = {
        battery_a: 'V', battery_b: 'V', battery_c: 'V', battery_d: 'V',
        pln_volt: 'V', pln_current: 'A', pln_power: 'W',
        temperature_1: '°C', temperature_2: '°C',
    };

    const sensorNames = {
        battery_a: 'Battery A', battery_b: 'Battery B',
        battery_c: 'Battery C', battery_d: 'Battery D',
        pln_volt: 'PLN Voltage', pln_current: 'PLN Current', pln_power: 'PLN Power',
        temperature_1: 'Temperature 1', temperature_2: 'Temperature 2',
    };

    let activeAlerts = {};
    let updateInterval = null;
    let isUpdating = false;
    let consecutiveErrors = 0;
    let alertSound = null;
    let soundEnabled = false;

    function initAlertSound() {
        if (soundEnabled) {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                alertSound = {
                    play: function () {
                        const oscillator = audioContext.createOscillator();
                        const gainNode = audioContext.createGain();
                        oscillator.connect(gainNode);
                        gainNode.connect(audioContext.destination);
                        oscillator.frequency.value = 800;
                        oscillator.type = 'sine';
                        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                        oscillator.start(audioContext.currentTime);
                        oscillator.stop(audioContext.currentTime + 0.5);
                    }
                };
            } catch (e) { /* Web Audio API not supported */ }
        }
    }

    function playAlertSound() {
        if (alertSound && soundEnabled) {
            try { alertSound.play(); } catch (e) { /* ignore */ }
        }
    }

    function clamp(v, lo, hi) { return Math.max(lo, Math.min(hi, v)); }

    function pct(key, value) {
        const r = ranges[key] || { min: 0, max: 100 };
        const den = Math.max(r.max - r.min, 1e-6);
        return clamp(((value - r.min) / den) * 100, 0, 100);
    }

    function isOutOfRange(key, value) {
        const r = ranges[key];
        if (!r) return false;
        return value < r.min || value > r.max;
    }

    function getAlertType(key, value) {
        const r = ranges[key];
        if (!r) return null;
        if (value < r.min) return 'LOW';
        if (value > r.max) return 'HIGH';
        return null;
    }

    function toOnOff(v) {
        if (v === undefined || v === null) return false;
        const s = String(v).trim().toLowerCase();
        return s === '1' || s === 'on' || s === 'true' || s === 'yes';
    }

    function updateAlertSummary() {
        const count = Object.keys(activeAlerts).length;
        const summary = $('#alert-summary');
        const countEl = $('#alert-count');

        if (count > 0) {
            summary.removeClass('d-none');
            countEl.text(count);
        } else {
            summary.addClass('d-none');
        }
    }

    function showAlertNotification(sensorKey, value, alertType, unit) {
        const sensorName = sensorNames[sensorKey] || sensorKey;
        const range = ranges[sensorKey];

        const toastHtml = `
            <div class="toast alert-toast" role="alert" data-delay="5000">
                <div class="toast-header bg-danger text-white">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong class="mr-auto">⚠️ Sensor Alert!</strong>
                    <button type="button" class="ml-2 close text-white" data-dismiss="toast">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="toast-body">
                    <strong>${sensorName}</strong> is <span class="badge badge-${alertType === 'HIGH' ? 'danger' : 'warning'}">${alertType}</span><br>
                    Current: <strong class="text-danger">${value.toFixed(2)} ${unit}</strong><br>
                    Normal Range: ${range.min} - ${range.max} ${unit}
                </div>
            </div>
        `;

        const toast = $(toastHtml);
        $('#toast-container').append(toast);
        toast.toast('show');
        toast.on('hidden.bs.toast', function () { $(this).remove(); });
    }

    function updateSensorAlert(sensorKey, value) {
        const card = $(`#card_${sensorKey}`);
        if (!card.length) return;

        const valueEl = $(`#${sensorKey}`);
        const barEl = $(`#${sensorKey}_bar`);
        const titleEl = card.find('.text-xs.font-weight-bold').first();
        const alertIcon = card.find('.alert-icon');
        const iconEl = card.find('.fa-2x');
        const unit = units[sensorKey];
        const colors = originalColors[sensorKey];
        const range = ranges[sensorKey];

        if (!range) return;

        const isAlert = isOutOfRange(sensorKey, value);
        const alertType = getAlertType(sensorKey, value);

        if (isAlert) {
            if (!activeAlerts[sensorKey]) {
                activeAlerts[sensorKey] = true;
                playAlertSound();
                showAlertNotification(sensorKey, value, alertType, unit);
            }

            card.addClass('sensor-alert sensor-alert-pulse border-left-danger');
            card.removeClass(colors.border);
            titleEl.removeClass(colors.text).addClass('text-danger');
            alertIcon.removeClass('d-none');
            valueEl.removeClass('text-gray-800').addClass('sensor-value-danger');
            valueEl.html(`${value.toFixed(2)} ${unit} <span class="badge badge-danger alert-badge">${alertType}</span>`);
            barEl.removeClass(colors.bar).addClass('bg-danger');
            iconEl.removeClass('text-gray-300').addClass('text-danger blink-icon');
        } else {
            if (activeAlerts[sensorKey]) {
                delete activeAlerts[sensorKey];
            }

            card.removeClass('sensor-alert sensor-alert-pulse border-left-danger');
            card.addClass(colors.border);
            titleEl.removeClass('text-danger').addClass(colors.text);
            alertIcon.addClass('d-none');
            valueEl.removeClass('sensor-value-danger').addClass('text-gray-800');
            valueEl.html(`${value.toFixed(2)} ${unit}`);
            barEl.removeClass('bg-danger').addClass(colors.bar);
            iconEl.removeClass('text-danger blink-icon').addClass('text-gray-300');
        }

        barEl.css('width', pct(sensorKey, value) + '%');
        updateAlertSummary();
    }

    function setFanUI(fanIndex, on) {
        const badge = $(`#fan${fanIndex}_badge`);
        const text = $(`#fan${fanIndex}_text`);
        const icon = $(`#fan${fanIndex}_icon`);

        badge.removeClass('badge-success badge-secondary');
        badge.addClass(on ? 'badge-success' : 'badge-secondary');
        badge.text(on ? 'ON' : 'OFF');
        text.text(on ? 'Running' : 'Stopped');

        if (on) icon.addClass('fa-spin');
        else icon.removeClass('fa-spin');
    }

    function setChargerUI(on) {
        const badge = $('#charger_badge');
        const text = $('#charger_text');

        badge.removeClass('badge-success badge-secondary');
        badge.addClass(on ? 'badge-success' : 'badge-secondary');
        badge.text(on ? 'ON' : 'OFF');
        text.text(on ? 'Charging' : 'Stopped');
    }

    function updateConnectionIndicator(status) {
        // No connection indicator in user dashboard — function kept for compatibility
    }

    function updateDashboard() {
        if (isUpdating) return;

        isUpdating = true;
        updateConnectionIndicator('updating');

        $.ajax({
            url: '{{ route("dashboard.realtime") }}',
            method: 'GET',
            timeout: 8000,
            dataType: 'json',
            cache: false,

            success: function (response) {
                consecutiveErrors = 0;
                updateConnectionIndicator('connected');

                if (response.data) {
                    ['a', 'b', 'c', 'd'].forEach(function (batt) {
                        const key = 'battery_' + batt;
                        const value = parseFloat(response.data[key]) || 0;
                        updateSensorAlert(key, value);
                    });

                    updateSensorAlert('pln_volt', parseFloat(response.data.pln_volt) || 0);
                    updateSensorAlert('pln_current', parseFloat(response.data.pln_current) || 0);
                    updateSensorAlert('pln_power', parseFloat(response.data.pln_power) || 0);
                    updateSensorAlert('temperature_1', parseFloat(response.data.temperature_1) || 0);
                    updateSensorAlert('temperature_2', parseFloat(response.data.temperature_2) || 0);

                    const fan1On = toOnOff(response.data.relay_1 ?? response.relay_1);
                    const fan2On = toOnOff(response.data.relay_2 ?? response.relay_2);
                    const chargerOn = toOnOff(response.data.relay_charger ?? response.relay_charger);

                    setFanUI(1, fan1On);
                    setFanUI(2, fan2On);
                    setChargerUI(chargerOn);

                    if (response.device_status) {
                        const statusBadge = $('#device_status');
                        statusBadge.text(response.device_status);
                        statusBadge.removeClass('badge-success badge-danger');
                        statusBadge.addClass(response.device_status === 'online' ? 'badge-success' : 'badge-danger');
                    }

                    if (response.last_seen) {
                        $('#last_seen').text(response.last_seen);
                    }
                }
            },

            error: function () {
                consecutiveErrors++;
                updateConnectionIndicator('error');

                if (consecutiveErrors >= MAX_CONSECUTIVE_ERRORS) {
                    stopUpdates();
                    setTimeout(function () {
                        consecutiveErrors = 0;
                        startUpdates();
                    }, RETRY_DELAY);
                }
            },

            complete: function () {
                isUpdating = false;
            }
        });
    }

    function startUpdates() {
        if (updateInterval) clearInterval(updateInterval);
        updateDashboard();
        updateInterval = setInterval(updateDashboard, UPDATE_INTERVAL);
    }

    function stopUpdates() {
        if (updateInterval) {
            clearInterval(updateInterval);
            updateInterval = null;
        }
    }

    function handleVisibilityChange() {
        if (document.hidden) {
            stopUpdates();
        } else {
            startUpdates();
        }
    }

    async function loadRanges() {
        try {
            const response = await $.ajax({
                url: '{{ route("sensor-ranges.index") }}',
                method: 'GET',
                dataType: 'json',
                cache: false
            });
            if (response.success && response.data) {
                ranges = Object.assign({}, DEFAULT_RANGES, response.data);
                updateDisplayedRanges();
            }
        } catch (e) {
            console.warn('Gagal load ranges dari DB, menggunakan default.');
        }
    }

    function updateDisplayedRanges() {
        Object.keys(ranges).forEach(function(key) {
            const el = $('#range_' + key);
            if (el.length) {
                el.find('.range-min').text(ranges[key].min);
                el.find('.range-max').text(ranges[key].max);
            }
        });
    }

    window.openSettingsModal = function(sensorKey) {
        $('#settings_sensor_key').val(sensorKey);
        $('#settings_sensor_name').text(sensorNames[sensorKey] || sensorKey);
        const unitText = '(' + (units[sensorKey] || '') + ')';
        $('#settings_unit').text(unitText);
        $('#settings_unit_max').text(unitText);

        const currentRange = ranges[sensorKey];
        if (currentRange) {
            $('#settings_min').val(currentRange.min);
            $('#settings_max').val(currentRange.max);
        }
        $('#settingsModal').modal('show');
    };

    window.saveSettings = function() {
        const sensorKey = $('#settings_sensor_key').val();
        const minVal    = parseFloat($('#settings_min').val());
        const maxVal    = parseFloat($('#settings_max').val());

        if (isNaN(minVal) || isNaN(maxVal)) {
            alert('Masukkan angka yang valid.');
            return;
        }
        if (minVal >= maxVal) {
            alert('Nilai minimum harus lebih kecil dari maksimum.');
            return;
        }

        const saveBtn = $('#settingsModal').find('.btn-primary');
        saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: '{{ route("sensor-ranges.store") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                sensor_key: sensorKey,
                min_value:  minVal,
                max_value:  maxVal
            },
            success: function(response) {
                if (response.success) {
                    ranges[sensorKey] = { min: minVal, max: maxVal };
                    updateDisplayedRanges();
                    $('#settingsModal').modal('hide');
                    showSuccessToast('Threshold ' + (sensorNames[sensorKey] || sensorKey) + ' berhasil disimpan.');
                    updateDashboard();
                } else {
                    alert(response.message || 'Gagal menyimpan.');
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Gagal menyimpan.');
            },
            complete: function() {
                saveBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan');
            }
        });
    };

    window.resetToDefault = function() {
        const sensorKey = $('#settings_sensor_key').val();
        if (!confirm('Reset threshold ' + (sensorNames[sensorKey] || sensorKey) + ' ke default?')) return;

        const resetBtn = $('#settingsModal').find('.btn-secondary');
        resetBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mereset...');

        $.ajax({
            url: '{{ route("sensor-ranges.destroy") }}',
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}',
                sensor_key: sensorKey
            },
            success: function(response) {
                if (response.success) {
                    ranges[sensorKey] = response.data;
                    $('#settings_min').val(ranges[sensorKey].min);
                    $('#settings_max').val(ranges[sensorKey].max);
                    updateDisplayedRanges();
                    showSuccessToast('Threshold ' + (sensorNames[sensorKey] || sensorKey) + ' direset ke default.');
                } else {
                    alert(response.message || 'Gagal reset.');
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Gagal reset.');
            },
            complete: function() {
                resetBtn.prop('disabled', false).html('<i class="fas fa-undo mr-1"></i> Reset ke Default');
            }
        });
    };

    function showSuccessToast(message) {
        const toastHtml = `
            <div class="toast" role="alert" data-delay="3000">
                <div class="toast-header bg-success text-white">
                    <i class="fas fa-check-circle mr-2"></i>
                    <strong class="mr-auto">Berhasil</strong>
                    <button type="button" class="ml-2 close text-white" data-dismiss="toast">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="toast-body">${message}</div>
            </div>`;
        const toast = $(toastHtml);
        $('#toast-container').append(toast);
        toast.toast('show');
        toast.on('hidden.bs.toast', function() { $(this).remove(); });
    }

    $(document).ready(async function () {
        await loadRanges();
        initAlertSound();
        startUpdates();
        document.addEventListener('visibilitychange', handleVisibilityChange);
    });

    $(window).on('beforeunload', function () {
        stopUpdates();
    });
</script>
@endpush