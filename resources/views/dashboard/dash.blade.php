@extends('layouts.template')

@section('content')
    @php
        // Define sensor ranges directly in code (no external config)
        $ranges = [
            'battery_a' => ['min' => 10.5, 'max' => 14.4],
            'battery_b' => ['min' => 10.5, 'max' => 14.4],
            'battery_c' => ['min' => 10.5, 'max' => 14.4],
            'battery_d' => ['min' => 10.5, 'max' => 14. 4],

            'pln_volt' => ['min' => 180, 'max' => 240],
            'pln_current' => ['min' => 0, 'max' => 32],
            'pln_power' => ['min' => 0, 'max' => 100],

            'temperature_1' => ['min' => 0, 'max' => 60],
            'temperature_2' => ['min' => 0, 'max' => 60],
        ];

        // Helper to compute percent with clamping
        function sensor_pct($key, $value, $ranges)
        {
            $range = $ranges[$key] ?? ['min' => 0, 'max' => 100];
            $min = (float) ($range['min'] ??  0);
            $max = (float) ($range['max'] ?? 100);
            $den = max($max - $min, 0.000001);
            $pct = (($value - $min) / $den) * 100;
            return max(0, min(100, $pct));
        }

        // Helper to check if value is out of range
        function is_alert($key, $value, $ranges)
        {
            $range = $ranges[$key] ?? null;
            if (! $range) return false;
            return $value < $range['min'] || $value > $range['max'];
        }

        // Helper to get alert type
        function get_alert_type($key, $value, $ranges)
        {
            $range = $ranges[$key] ?? null;
            if (!$range) return null;
            if ($value < $range['min']) return 'LOW';
            if ($value > $range['max']) return 'HIGH';
            return null;
        }

        // Inisialisasi status fan dari data terakhir
        $relay1Init = (string) ($latestData->relay_1 ?? '0');
        $relay2Init = (string) ($latestData->relay_2 ?? '0');
        $fan1On = $relay1Init === '1' || strtolower($relay1Init) === 'on' || strtolower($relay1Init) === 'true';
        $fan2On = $relay2Init === '1' || strtolower($relay2Init) === 'on' || strtolower($relay2Init) === 'true';
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
                                        id="last_seen">{{ $device->last_seen? ->diffForHumans() ??  'Never' }}</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-wifi fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fan Status Widgets -->
            <div class="col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Fan 1 Status
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <span id="fan1_badge" class="badge {{ $fan1On ?  'badge-success' : 'badge-secondary' }}">
                                        {{ $fan1On ? 'ON' : 'OFF' }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <span id="fan1_text">{{ $fan1On ? 'Running' : 'Stopped' }}</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i id="fan1_icon" class="fas fa-fan fa-2x text-gray-300 {{ $fan1On ?  'fa-spin' : '' }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Fan 2 Status
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <span id="fan2_badge" class="badge {{ $fan2On ? 'badge-success' : 'badge-secondary' }}">
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
            <!-- End Fan Status Widgets -->
        </div>

        <div class="row">
            <!-- Battery Cards -->
            @foreach (['a', 'b', 'c', 'd'] as $batt)
                @php
                    $key = 'battery_' . $batt;
                    $val = (float) ($latestData? ->$key ?? 0);
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
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold {{ $isAlert ? 'text-danger' : 'text-primary' }} text-uppercase mb-1">
                                        Battery {{ strtoupper($batt) }}
                                        <i class="fas fa-exclamation-triangle alert-icon {{ $isAlert ? '' : 'd-none' }}"></i>
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold {{ $isAlert ? 'sensor-value-danger' : 'text-gray-800' }}" id="battery_{{ $batt }}">
                                        {{ number_format($val, 2) }} V
                                        @if($isAlert)
                                            <span class="badge badge-danger alert-badge">{{ $alertType }}</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-muted mt-1">
                                        Range: {{ $range['min'] }}V - {{ $range['max'] }}V
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

            <!-- PLN Volt -->
            @php
                $plnVoltVal = (float) ($latestData->pln_volt ??  0);
                $plnVoltPct = sensor_pct('pln_volt', $plnVoltVal, $ranges);
                $plnVoltAlert = is_alert('pln_volt', $plnVoltVal, $ranges);
                $plnVoltAlertType = get_alert_type('pln_volt', $plnVoltVal, $ranges);
            @endphp
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 sensor-card {{ $plnVoltAlert ? 'sensor-alert sensor-alert-pulse border-left-danger' : 'border-left-success' }}"
                     id="card_pln_volt"
                     data-sensor="pln_volt">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold {{ $plnVoltAlert ? 'text-danger' : 'text-success' }} text-uppercase mb-1">
                                    PLN Volt
                                    <i class="fas fa-exclamation-triangle alert-icon {{ $plnVoltAlert ? '' : 'd-none' }}"></i>
                                </div>
                                <div class="h5 mb-0 font-weight-bold {{ $plnVoltAlert ? 'sensor-value-danger' : 'text-gray-800' }}" id="pln_volt">
                                    {{ number_format($plnVoltVal, 2) }} V
                                    @if($plnVoltAlert)
                                        <span class="badge badge-danger alert-badge">{{ $plnVoltAlertType }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-muted mt-1">
                                    Range: {{ $ranges['pln_volt']['min'] }}V - {{ $ranges['pln_volt']['max'] }}V
                                </div>
                                <div class="progress progress-sm mt-2">
                                    <div class="progress-bar {{ $plnVoltAlert ? 'bg-danger' : 'bg-success' }}" 
                                         id="pln_volt_bar"
                                         style="width: {{ $plnVoltPct }}%"></div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-bolt fa-2x {{ $plnVoltAlert ? 'text-danger' : 'text-gray-300' }}"></i>
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
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 sensor-card {{ $plnCurrentAlert ? 'sensor-alert sensor-alert-pulse border-left-danger' : 'border-left-warning' }}"
                     id="card_pln_current"
                     data-sensor="pln_current">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold {{ $plnCurrentAlert ?  'text-danger' : 'text-warning' }} text-uppercase mb-1">
                                    PLN Current
                                    <i class="fas fa-exclamation-triangle alert-icon {{ $plnCurrentAlert ? '' : 'd-none' }}"></i>
                                </div>
                                <div class="h5 mb-0 font-weight-bold {{ $plnCurrentAlert ? 'sensor-value-danger' : 'text-gray-800' }}" id="pln_current">
                                    {{ number_format($plnCurrentVal, 2) }} A
                                    @if($plnCurrentAlert)
                                        <span class="badge badge-danger alert-badge">{{ $plnCurrentAlertType }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-muted mt-1">
                                    Range: {{ $ranges['pln_current']['min'] }}A - {{ $ranges['pln_current']['max'] }}A
                                </div>
                                <div class="progress progress-sm mt-2">
                                    <div class="progress-bar {{ $plnCurrentAlert ? 'bg-danger' : 'bg-warning' }}" 
                                         id="pln_current_bar"
                                         style="width: {{ $plnCurrentPct }}%"></div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-bolt fa-2x {{ $plnCurrentAlert ? 'text-danger' : 'text-gray-300' }}"></i>
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
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 sensor-card {{ $plnPowerAlert ? 'sensor-alert sensor-alert-pulse border-left-danger' : 'border-left-info' }}"
                     id="card_pln_power"
                     data-sensor="pln_power">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold {{ $plnPowerAlert ? 'text-danger' : 'text-info' }} text-uppercase mb-1">
                                    PLN Watt
                                    <i class="fas fa-exclamation-triangle alert-icon {{ $plnPowerAlert ? '' : 'd-none' }}"></i>
                                </div>
                                <div class="h5 mb-0 font-weight-bold {{ $plnPowerAlert ? 'sensor-value-danger' : 'text-gray-800' }}" id="pln_power">
                                    {{ number_format($plnPowerVal, 2) }} W
                                    @if($plnPowerAlert)
                                        <span class="badge badge-danger alert-badge">{{ $plnPowerAlertType }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-muted mt-1">
                                    Range: {{ $ranges['pln_power']['min'] }}W - {{ $ranges['pln_power']['max'] }}W
                                </div>
                                <div class="progress progress-sm mt-2">
                                    <div class="progress-bar {{ $plnPowerAlert ? 'bg-danger' : 'bg-info' }}" 
                                         id="pln_power_bar"
                                         style="width: {{ $plnPowerPct }}%"></div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-plug fa-2x {{ $plnPowerAlert ? 'text-danger' : 'text-gray-300' }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Temperature 1 -->
            @php
                $t1Val = (float) ($latestData->temperature_1 ?? 0);
                $t1Pct = sensor_pct('temperature_1', $t1Val, $ranges);
                $t1Alert = is_alert('temperature_1', $t1Val, $ranges);
                $t1AlertType = get_alert_type('temperature_1', $t1Val, $ranges);
            @endphp
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 sensor-card {{ $t1Alert ? 'sensor-alert sensor-alert-pulse' : '' }} border-left-danger"
                     id="card_temperature_1"
                     data-sensor="temperature_1">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Temperature 1
                                    <i class="fas fa-exclamation-triangle alert-icon {{ $t1Alert ? '' : 'd-none' }}"></i>
                                </div>
                                <div class="h5 mb-0 font-weight-bold {{ $t1Alert ?  'sensor-value-danger' : 'text-gray-800' }}" id="temperature_1">
                                    {{ number_format($t1Val, 2) }} °C
                                    @if($t1Alert)
                                        <span class="badge badge-danger alert-badge">{{ $t1AlertType }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-muted mt-1">
                                    Range: {{ $ranges['temperature_1']['min'] }}°C - {{ $ranges['temperature_1']['max'] }}°C
                                </div>
                                <div class="progress progress-sm mt-2">
                                    <div class="progress-bar bg-danger" id="temperature_1_bar"
                                        style="width: {{ $t1Pct }}%"></div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-thermometer-half fa-2x {{ $t1Alert ?  'text-danger blink-icon' : 'text-gray-300' }}"></i>
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
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 sensor-card {{ $t2Alert ?  'sensor-alert sensor-alert-pulse' : '' }} border-left-danger"
                     id="card_temperature_2"
                     data-sensor="temperature_2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Temperature 2
                                    <i class="fas fa-exclamation-triangle alert-icon {{ $t2Alert ?  '' : 'd-none' }}"></i>
                                </div>
                                <div class="h5 mb-0 font-weight-bold {{ $t2Alert ? 'sensor-value-danger' : 'text-gray-800' }}" id="temperature_2">
                                    {{ number_format($t2Val, 2) }} °C
                                    @if($t2Alert)
                                        <span class="badge badge-danger alert-badge">{{ $t2AlertType }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-muted mt-1">
                                    Range: {{ $ranges['temperature_2']['min'] }}°C - {{ $ranges['temperature_2']['max'] }}°C
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
@endsection

@push('styles')
<style>
    /* Blinking animation for alert state */
    @keyframes blink-alert {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0. 4;
        }
    }
    
    @keyframes pulse-border {
        0% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0. 7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
        }
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
        20%, 40%, 60%, 80% { transform: translateX(2px); }
    }
    
    .sensor-alert {
        animation: blink-alert 1s ease-in-out infinite;
        border-left-color: #dc3545 !important;
        border-left-width: 4px ! important;
    }
    
    . sensor-alert-pulse {
        animation: blink-alert 1s ease-in-out infinite, pulse-border 1. 5s infinite;
    }
    
    .alert-icon {
        color: #dc3545;
        margin-left: 8px;
        animation: blink-alert 0.5s ease-in-out infinite;
    }
    
    .blink-icon {
        animation: blink-alert 0.5s ease-in-out infinite;
    }
    
    . sensor-value-danger {
        color: #dc3545 !important;
    }
    
    .alert-badge {
        font-size: 0.65rem;
        padding: 2px 6px;
        margin-left: 5px;
        animation: blink-alert 0.8s ease-in-out infinite;
    }

    /* Toast styling */
    .alert-toast {
        min-width: 300px;
        animation: shake 0. 5s ease-in-out;
    }

    .alert-toast . toast-header {
        border-bottom: none;
    }

    /* Alert summary badge */
    #alert-summary . badge {
        animation: pulse-border 2s infinite;
        font-size: 0.9rem;
    }

    /* Smooth transitions */
    .sensor-card {
        transition: all 0.3s ease;
    }

    .progress-bar {
        transition: width 0.5s ease, background-color 0. 3s ease;
    }
</style>
@endpush

@push('scripts')
<script>
    // Ranges defined in code (keep in sync with PHP above)
    const ranges = {
        battery_a: { min: 10. 5, max: 14.4 },
        battery_b: { min: 10.5, max: 14.4 },
        battery_c: { min: 10.5, max: 14. 4 },
        battery_d: { min: 10. 5, max: 14.4 },
        pln_volt: { min: 180, max: 240 },
        pln_current: { min: 0, max: 32 },
        pln_power: { min: 0, max: 100 },
        temperature_1: { min: 0, max: 60 },
        temperature_2: { min: 0, max: 60 },
    };

    // Original colors for each sensor type
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

    // Units for each sensor
    const units = {
        battery_a: 'V', battery_b: 'V', battery_c: 'V', battery_d: 'V',
        pln_volt: 'V', pln_current: 'A', pln_power: 'W',
        temperature_1: '°C', temperature_2: '°C'
    };

    // Track active alerts
    let activeAlerts = {};
    let alertSound = null;
    let alertsEnabled = true;
    let soundEnabled = false; // Set to true to enable sound alerts

    function initAlertSound() {
        if (soundEnabled) {
            // Create a simple beep sound using Web Audio API
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                alertSound = {
                    play: function() {
                        const oscillator = audioContext.createOscillator();
                        const gainNode = audioContext.createGain();
                        oscillator.connect(gainNode);
                        gainNode.connect(audioContext.destination);
                        oscillator.frequency.value = 800;
                        oscillator.type = 'sine';
                        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                        gainNode. gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                        oscillator.start(audioContext.currentTime);
                        oscillator.stop(audioContext.currentTime + 0.5);
                    }
                };
            } catch (e) {
                console.log('Web Audio API not supported');
            }
        }
    }

    function playAlertSound() {
        if (alertSound && alertsEnabled && soundEnabled) {
            try {
                alertSound.play();
            } catch (e) {
                console. log('Audio play failed:', e);
            }
        }
    }

    function clamp(v, lo, hi) {
        return Math.max(lo, Math. min(hi, v));
    }

    function pct(key, value) {
        const r = ranges[key] || { min: 0, max: 100 };
        const den = Math.max(r. max - r.min, 1e-6);
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
        if (value < r. min) return 'LOW';
        if (value > r.max) return 'HIGH';
        return null;
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
        const sensorName = sensorKey. replace(/_/g, ' ').toUpperCase();
        const range = ranges[sensorKey];
        
        const toastHtml = `
            <div class="toast alert-toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
                <div class="toast-header bg-danger text-white">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong class="mr-auto">⚠️ Sensor Alert! </strong>
                    <small class="text-white-50">just now</small>
                    <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="toast-body">
                    <strong>${sensorName}</strong> is <span class="badge badge-${alertType === 'HIGH' ?  'danger' : 'warning'}">${alertType}</span><br>
                    Current: <strong class="text-danger">${value. toFixed(2)} ${unit}</strong><br>
                    Normal Range: ${range. min} - ${range.max} ${unit}
                </div>
            </div>
        `;
        
        const container = $('#toast-container');
        const toast = $(toastHtml);
        container.append(toast);
        toast.toast('show');
        
        toast.on('hidden. bs.toast', function() {
            $(this).remove();
        });
    }

    function updateSensorAlert(sensorKey, value) {
        const card = $(`#card_${sensorKey}`);
        const valueEl = $(`#${sensorKey}`);
        const barEl = $(`#${sensorKey}_bar`);
        const titleEl = card.find('.text-xs. font-weight-bold'). first();
        const alertIcon = card.find('. alert-icon');
        const iconEl = card.find('.fa-2x');
        const unit = units[sensorKey];
        const colors = originalColors[sensorKey];
        const range = ranges[sensorKey];
        
        const isAlert = isOutOfRange(sensorKey, value);
        const alertType = getAlertType(sensorKey, value);
        
        if (isAlert) {
            // Add to active alerts
            if (!activeAlerts[sensorKey]) {
                activeAlerts[sensorKey] = true;
                playAlertSound();
                showAlertNotification(sensorKey, value, alertType, unit);
            }
            
            // Apply alert styles
            card.addClass('sensor-alert sensor-alert-pulse');
            card.removeClass(colors.border). addClass('border-left-danger');
            
            titleEl.removeClass(colors.text).addClass('text-danger');
            alertIcon.removeClass('d-none');
            
            valueEl.removeClass('text-gray-800').addClass('sensor-value-danger');
            valueEl.html(`${value.toFixed(2)} ${unit} <span class="badge badge-danger alert-badge">${alertType}</span>`);
            
            barEl.removeClass(colors.bar).addClass('bg-danger');
            
            iconEl.removeClass('text-gray-300'). addClass('text-danger blink-icon');
            
        } else {
            // Remove from active alerts
            if (activeAlerts[sensorKey]) {
                delete activeAlerts[sensorKey];
            }
            
            // Remove alert styles
            card.removeClass('sensor-alert sensor-alert-pulse border-left-danger');
            card.addClass(colors.border);
            
            titleEl.removeClass('text-danger'). addClass(colors.text);
            alertIcon.addClass('d-none');
            
            valueEl.removeClass('sensor-value-danger'). addClass('text-gray-800');
            valueEl.html(`${value.toFixed(2)} ${unit}`);
            
            barEl.removeClass('bg-danger').addClass(colors.bar);
            
            iconEl.removeClass('text-danger blink-icon').addClass('text-gray-300');
        }
        
        // Update progress bar
        barEl.css('width', pct(sensorKey, value) + '%');
        
        // Update alert summary
        updateAlertSummary();
    }

    let updateInterval;
    let isUpdating = false;
    let consecutiveErrors = 0;
    const maxErrors = 3;

    function updateConnectionIndicator(status) {
        const indicator = $('#connection-indicator');
        const dot = $('#connection-dot');
        const text = $('#connection-text');

        if (! indicator.length) return;

        indicator.removeClass('badge-success badge-warning badge-danger');
        dot.removeClass('text-success text-warning text-danger');

        switch (status) {
            case 'connected':
                indicator. addClass('badge-success');
                dot. addClass('text-success');
                text.text('Connected');
                break;
            case 'updating':
                indicator. addClass('badge-warning');
                dot. addClass('text-warning');
                text. text('Updating.. .');
                break;
            case 'error':
                indicator.addClass('badge-danger');
                dot.addClass('text-danger');
                text.text('Connection Error');
                break;
        }
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

    function toOnOff(v) {
        if (v === undefined || v === null) return false;
        const s = String(v). trim(). toLowerCase();
        return s === '1' || s === 'on' || s === 'true' || s === 'yes';
    }

    function updateDashboard() {
        if (isUpdating) return;

        isUpdating = true;
        updateConnectionIndicator('updating');

        $. ajax({
            url: '{{ route('dashboard.realtime') }}',
            method: 'GET',
            timeout: 8000,
            success: function(response) {
                consecutiveErrors = 0;
                updateConnectionIndicator('connected');

                if (response. data) {
                    // Batteries with alert check
                    ['a', 'b', 'c', 'd'].forEach(function(batt) {
                        const key = 'battery_' + batt;
                        const value = parseFloat(response.data[key]) || 0;
                        updateSensorAlert(key, value);
                    });

                    // PLN Sensors with alert check
                    const plnVolt = parseFloat(response.data.pln_volt) || 0;
                    const plnCurrent = parseFloat(response.data.pln_current) || 0;
                    const plnPower = parseFloat(response.data.pln_power) || 0;

                    updateSensorAlert('pln_volt', plnVolt);
                    updateSensorAlert('pln_current', plnCurrent);
                    updateSensorAlert('pln_power', plnPower);

                    // Temperatures with alert check
                    const temp1 = parseFloat(response.data.temperature_1) || 0;
                    const temp2 = parseFloat(response.data.temperature_2) || 0;

                    updateSensorAlert('temperature_1', temp1);
                    updateSensorAlert('temperature_2', temp2);

                    // Fans (relay_1, relay_2)
                    const fan1On = toOnOff(response.data. relay_1 ??  response.relay_1);
                    const fan2On = toOnOff(response.data. relay_2 ??  response.relay_2);
                    setFanUI(1, fan1On);
                    setFanUI(2, fan2On);

                    // Device status
                    if (response.device_status) {
                        const statusBadge = $('#device_status');
                        statusBadge.text(response.device_status);
                        statusBadge. removeClass('badge-success badge-danger');
                        statusBadge.addClass(response.device_status === 'online' ? 'badge-success' : 'badge-danger');
                    }

                    // Last seen
                    if (response.last_seen) {
                        $('#last_seen').text(response.last_seen);
                    }

                    // Timestamp
                    $('#last_update').text(new Date(). toLocaleTimeString());
                }
            },
            error: function(xhr, status, error) {
                consecutiveErrors++;
                updateConnectionIndicator('error');
                console.error('Dashboard update error:', error);

                if (consecutiveErrors >= maxErrors) {
                    clearInterval(updateInterval);
                    setTimeout(() => {
                        consecutiveErrors = 0;
                        updateInterval = setInterval(updateDashboard, 5000);
                    }, 10000);
                }
            },
            complete: function() {
                isUpdating = false;
            }
        });
    }

    $(document).ready(function() {
        initAlertSound();
        updateDashboard();
        updateInterval = setInterval(updateDashboard, 1000);

        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                if (updateInterval) clearInterval(updateInterval);
                updateInterval = null;
            } else {
                if (! updateInterval) {
                    updateInterval = setInterval(updateDashboard, 1000);
                    updateDashboard();
                }
            }
        });
    });

    $(window).on('beforeunload', function() {
        if (updateInterval) clearInterval(updateInterval);
    });
</script>
@endpush