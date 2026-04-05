@extends('layouts.template')

@section('content')
    @php
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
            'server_voltage' => ['min' => 170, 'max' => 260],
        ];

        function sensor_pct($key, $value, $ranges)
        {
            $range = $ranges[$key] ?? ['min' => 0, 'max' => 100];
            $min = (float) ($range['min'] ?? 0);
            $max = (float) ($range['max'] ?? 100);
            $den = max($max - $min, 0.000001);
            $pct = (($value - $min) / $den) * 100;
            return max(0, min(100, $pct));
        }

        function is_alert($key, $value, $ranges)
        {
            $range = $ranges[$key] ?? null;
            if (!$range) return false;
            return $value < $range['min'] || $value > $range['max'];
        }

        function get_alert_type($key, $value, $ranges)
        {
            $range = $ranges[$key] ?? null;
            if (!$range) return null;
            if ($value < $range['min']) return 'LOW';
            if ($value > $range['max']) return 'HIGH';
            return null;
        }

        $relay1Init = (string) ($latestData->relay_1 ?? '0');
        $relay2Init = (string) ($latestData->relay_2 ?? '0');
        $fan1On = $relay1Init === '1' || strtolower($relay1Init) === 'on' || strtolower($relay1Init) === 'true';
        $fan2On = $relay2Init === '1' || strtolower($relay2Init) === 'on' || strtolower($relay2Init) === 'true';
    @endphp

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard Monitoring PLTS</h1>
            
            <!--<div class="d-flex align-items-center">-->
            <!--    <div id="connection-indicator" class="badge badge-secondary mr-2">-->
            <!--        <span id="connection-dot">●</span>-->
            <!--        <span id="connection-text">Connecting...</span>-->
            <!--    </div>-->
                
            <!--    <div id="alert-summary" class="d-none">-->
            <!--        <span class="badge badge-danger p-2">-->
            <!--            <i class="fas fa-exclamation-triangle mr-1"></i>-->
            <!--            <span id="alert-count">0</span> Alert(s)-->
            <!--        </span>-->
            <!--    </div>-->
            <!--</div>-->
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
                                            <span class="badge badge-{{ $device->status == 'online' ? 'success' : 'danger' }}" id="device_status">
                                                {{ $device->status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 mb-1">
                                    Last seen: <span id="last_seen">{{ $device->last_seen?->diffForHumans() ?? 'Never' }}</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-wifi fa-2x text-gray-300"></i>
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
                                    Fan 1 Status (Kipas PLTS)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <span id="fan1_badge" class="badge {{ $fan1On ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $fan1On ? 'ON' : 'OFF' }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <span id="fan1_text">{{ $fan1On ? 'Running' : 'Stopped' }}</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i id="fan1_icon" class="fas fa-fan fa-2x text-gray-300 {{ $fan1On ? 'fa-spin' : '' }}"></i>
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
                                    Fan 2 Status (Kipas RouterBoard)
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
                                <i id="fan2_icon" class="fas fa-fan fa-2x text-gray-300 {{ $fan2On ? 'fa-spin' : '' }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach (['a', 'b', 'c', 'd'] as $batt)
                @php
                    $key = 'battery_'.$batt;
                    $val = (float) ($latestData?->{$key} ?? 0);
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
                                    title="Configure Alert Range">
                                <i class="fas fa-cog"></i>
                            </button>
                            
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

            @php
                $plnVoltVal = (float) ($latestData->pln_volt ?? 0);
                $plnVoltPct = sensor_pct('pln_volt', $plnVoltVal, $ranges);
                $plnVoltAlert = is_alert('pln_volt', $plnVoltVal, $ranges);
                $plnVoltAlertType = get_alert_type('pln_volt', $plnVoltVal, $ranges);
            @endphp
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 sensor-card {{ $plnVoltAlert ? 'sensor-alert sensor-alert-pulse border-left-danger' : 'border-left-success' }}"
                     id="card_pln_volt"
                     data-sensor="pln_volt">
                    <div class="card-body">
                        <button class="btn btn-sm btn-link position-absolute settings-btn" 
                                style="top: 5px; right: 5px; z-index: 10; color: #858796; padding: 2px 6px;"
                                onclick="openSettingsModal('pln_volt')"
                                title="Configure Alert Range">
                            <i class="fas fa-cog"></i>
                        </button>
                        
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
                                <div class="text-xs text-muted mt-1" id="range_pln_volt">
                                    Range: <span class="range-min">{{ $ranges['pln_volt']['min'] }}</span>V - <span class="range-max">{{ $ranges['pln_volt']['max'] }}</span>V
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
                        <button class="btn btn-sm btn-link position-absolute settings-btn" 
                                style="top: 5px; right: 5px; z-index: 10; color: #858796; padding: 2px 6px;"
                                onclick="openSettingsModal('pln_current')"
                                title="Configure Alert Range">
                            <i class="fas fa-cog"></i>
                        </button>
                        
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold {{ $plnCurrentAlert ? 'text-danger' : 'text-warning' }} text-uppercase mb-1">
                                    PLN Current
                                    <i class="fas fa-exclamation-triangle alert-icon {{ $plnCurrentAlert ? '' : 'd-none' }}"></i>
                                </div>
                                <div class="h5 mb-0 font-weight-bold {{ $plnCurrentAlert ? 'sensor-value-danger' : 'text-gray-800' }}" id="pln_current">
                                    {{ number_format($plnCurrentVal, 2) }} A
                                    @if($plnCurrentAlert)
                                        <span class="badge badge-danger alert-badge">{{ $plnCurrentAlertType }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-muted mt-1" id="range_pln_current">
                                    Range: <span class="range-min">{{ $ranges['pln_current']['min'] }}</span>A - <span class="range-max">{{ $ranges['pln_current']['max'] }}</span>A
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
                        <button class="btn btn-sm btn-link position-absolute settings-btn" 
                                style="top: 5px; right: 5px; z-index: 10; color: #858796; padding: 2px 6px;"
                                onclick="openSettingsModal('pln_power')"
                                title="Configure Alert Range">
                            <i class="fas fa-cog"></i>
                        </button>
                        
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
                                <div class="text-xs text-muted mt-1" id="range_pln_power">
                                    Range: <span class="range-min">{{ $ranges['pln_power']['min'] }}</span>W - <span class="range-max">{{ $ranges['pln_power']['max'] }}</span>W
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
                        <button class="btn btn-sm btn-link position-absolute settings-btn" 
                                style="top: 5px; right: 5px; z-index: 10; color: #858796; padding: 2px 6px;"
                                onclick="openSettingsModal('temperature_1')"
                                title="Configure Alert Range">
                            <i class="fas fa-cog"></i>
                        </button>
                        
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Temperature 1
                                    <i class="fas fa-exclamation-triangle alert-icon {{ $t1Alert ? '' : 'd-none' }}"></i>
                                </div>
                                <div class="h5 mb-0 font-weight-bold {{ $t1Alert ? 'sensor-value-danger' : 'text-gray-800' }}" id="temperature_1">
                                    {{ number_format($t1Val, 2) }} °C
                                    @if($t1Alert)
                                        <span class="badge badge-danger alert-badge">{{ $t1AlertType }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-muted mt-1" id="range_temperature_1">
                                    Range: <span class="range-min">{{ $ranges['temperature_1']['min'] }}</span>°C - <span class="range-max">{{ $ranges['temperature_1']['max'] }}</span>°C
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

            @php
                $t2Val = (float) ($latestData->temperature_2 ?? 0);
                $t2Pct = sensor_pct('temperature_2', $t2Val, $ranges);
                $t2Alert = is_alert('temperature_2', $t2Val, $ranges);
                $t2AlertType = get_alert_type('temperature_2', $t2Val, $ranges);
            @endphp
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 sensor-card {{ $t2Alert ? 'sensor-alert sensor-alert-pulse' : '' }} border-left-danger"
                     id="card_temperature_2"
                     data-sensor="temperature_2">
                    <div class="card-body">
                        <button class="btn btn-sm btn-link position-absolute settings-btn" 
                                style="top: 5px; right: 5px; z-index: 10; color: #858796; padding: 2px 6px;"
                                onclick="openSettingsModal('temperature_2')"
                                title="Configure Alert Range">
                            <i class="fas fa-cog"></i>
                        </button>
                        
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Temperature 2
                                    <i class="fas fa-exclamation-triangle alert-icon {{ $t2Alert ? '' : 'd-none' }}"></i>
                                </div>
                                <div class="h5 mb-0 font-weight-bold {{ $t2Alert ? 'sensor-value-danger' : 'text-gray-800' }}" id="temperature_2">
                                    {{ number_format($t2Val, 2) }} °C
                                    @if($t2Alert)
                                        <span class="badge badge-danger alert-badge">{{ $t2AlertType }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-muted mt-1" id="range_temperature_2">
                                    Range: <span class="range-min">{{ $ranges['temperature_2']['min'] }}</span>°C - <span class="range-max">{{ $ranges['temperature_2']['max'] }}</span>°C
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

            @php
                $serverVoltVal = (float) ($latestServerData->server_voltage ?? 0);
                $serverVoltPct = sensor_pct('server_voltage', $serverVoltVal, $ranges);
                $serverVoltAlert = is_alert('server_voltage', $serverVoltVal, $ranges);
                $serverVoltAlertType = get_alert_type('server_voltage', $serverVoltVal, $ranges);
            @endphp
            <!--<div class="col-xl-3 col-md-6 mb-4">-->
            <!--    <div class="card shadow h-100 py-2 sensor-card {{ $serverVoltAlert ? 'sensor-alert sensor-alert-pulse border-left-danger' : 'border-left-success' }}"-->
            <!--         id="card_server_voltage"-->
            <!--         data-sensor="server_voltage">-->
            <!--        <div class="card-body">-->
            <!--            <button class="btn btn-sm btn-link position-absolute settings-btn" -->
            <!--                    style="top: 5px; right: 5px; z-index: 10; color: #858796; padding: 2px 6px;"-->
            <!--                    onclick="openSettingsModal('server_voltage')"-->
            <!--                    title="Configure Alert Range">-->
            <!--                <i class="fas fa-cog"></i>-->
            <!--            </button>-->
                        
            <!--            <div class="row no-gutters align-items-center">-->
            <!--                <div class="col mr-2">-->
            <!--                    <div class="text-xs font-weight-bold {{ $serverVoltAlert ? 'text-danger' : 'text-success' }} text-uppercase mb-1">-->
            <!--                        Server Voltage-->
            <!--                        <i class="fas fa-exclamation-triangle alert-icon {{ $serverVoltAlert ? '' : 'd-none' }}"></i>-->
            <!--                    </div>-->
            <!--                    <div class="h5 mb-0 font-weight-bold {{ $serverVoltAlert ? 'sensor-value-danger' : 'text-gray-800' }}" id="server_voltage">-->
            <!--                        {{ number_format($serverVoltVal, 2) }} V-->
            <!--                        @if($serverVoltAlert)-->
            <!--                            <span class="badge badge-danger alert-badge">{{ $serverVoltAlertType }}</span>-->
            <!--                        @endif-->
            <!--                    </div>-->
            <!--                    <div class="text-xs text-muted mt-1" id="range_server_voltage">-->
            <!--                        Range: <span class="range-min">{{ $ranges['server_voltage']['min'] }}</span>V - <span class="range-max">{{ $ranges['server_voltage']['max'] }}</span>V-->
            <!--                    </div>-->
            <!--                    <div class="progress progress-sm mt-2">-->
            <!--                        <div class="progress-bar {{ $serverVoltAlert ? 'bg-danger' : 'bg-success' }}" -->
            <!--                             id="server_voltage_bar"-->
            <!--                             style="width: {{ $serverVoltPct }}%"></div>-->
            <!--                    </div>-->
            <!--                </div>-->
            <!--                <div class="col-auto">-->
            <!--                    <i class="fas fa-server fa-2x {{ $serverVoltAlert ? 'text-danger' : 'text-gray-300' }}"></i>-->
            <!--                </div>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--</div>-->
        </div>
    </div>

    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

    <div class="modal fade" id="settingsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-cog mr-2"></i>
                        Alert Range Settings
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
                            <small>Configure min/max values for <strong id="settings_sensor_name"></strong></small>
                        </div>
                        
                        <div class="form-group">
                            <label for="settings_min">Minimum Value <span id="settings_unit"></span></label>
                            <input type="number" class="form-control" id="settings_min" step="0.1" required>
                            <small class="form-text text-muted">Alert if value goes below this</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="settings_max">Maximum Value <span id="settings_unit"></span></label>
                            <input type="number" class="form-control" id="settings_max" step="0.1" required>
                            <small class="form-text text-muted">Alert if value goes above this</small>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <small>Settings are saved in database</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="resetToDefault()">
                        <i class="fas fa-undo mr-1"></i> Reset to Default
                    </button>
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="saveSettings()">
                        <i class="fas fa-save mr-1"></i> Save Changes
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

    .settings-btn:hover {
        color: #4e73df !important;
        transform: rotate(90deg);
        transition: all 0.3s ease;
    }

    #connection-indicator {
        font-size: 0.85rem;
        padding: 4px 8px;
    }

    #connection-dot {
        display: inline-block;
        margin-right: 4px;
    }
</style>
@endpush

@push('scripts')
<script>
    const UPDATE_INTERVAL = 1000;
    const MAX_CONSECUTIVE_ERRORS = 3;
    const RETRY_DELAY = 10000;

    let ranges = {};
    let activeAlerts = {};
    let updateInterval = null;
    let isUpdating = false;
    let consecutiveErrors = 0;
    let alertSound = null;
    let alertsEnabled = true;
    let soundEnabled = false;

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
        server_voltage: { border: 'border-left-success', bar: 'bg-success', text: 'text-success' },
    };

    const units = {
        battery_a: 'V', battery_b: 'V', battery_c: 'V', battery_d: 'V',
        pln_volt: 'V', pln_current: 'A', pln_power: 'W',
        temperature_1: '°C', temperature_2: '°C',
        server_voltage: 'V',
    };

    const sensorNames = {
        battery_a: 'Battery A',
        battery_b: 'Battery B',
        battery_c: 'Battery C',
        battery_d: 'Battery D',
        pln_volt: 'PLN Voltage',
        pln_current: 'PLN Current',
        pln_power: 'PLN Power',
        temperature_1: 'Temperature 1',
        temperature_2: 'Temperature 2',
        server_voltage: 'Server Voltage',
    };

    function clamp(v, lo, hi) {
        return Math.max(lo, Math.min(hi, v));
    }

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

    function updateConnectionIndicator(status) {
        const indicator = $('#connection-indicator');
        const dot = $('#connection-dot');
        const text = $('#connection-text');

        indicator.removeClass('badge-success badge-warning badge-danger badge-secondary');
        dot.removeClass('text-success text-warning text-danger');

        switch (status) {
            case 'connected':
                indicator.addClass('badge-success');
                dot.addClass('text-success');
                text.text('Connected');
                break;
            case 'updating':
                indicator.addClass('badge-warning');
                dot.addClass('text-warning');
                text.text('Updating...');
                break;
            case 'error':
                indicator.addClass('badge-danger');
                dot.addClass('text-danger');
                text.text('Error');
                break;
            default:
                indicator.addClass('badge-secondary');
                text.text('Connecting...');
        }
    }

    function initAlertSound() {
        if (soundEnabled) {
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
                        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                        oscillator.start(audioContext.currentTime);
                        oscillator.stop(audioContext.currentTime + 0.5);
                    }
                };
            } catch (e) {
                // Web Audio API not supported
            }
        }
    }

    function playAlertSound() {
        if (alertSound && alertsEnabled && soundEnabled) {
            try {
                alertSound.play();
            } catch (e) {
                // ignore
            }
        }
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
        // Alert popup dinonaktifkan – indikator blink di card sudah cukup
        return;
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

        if (on) {
            icon.addClass('fa-spin');
        } else {
            icon.removeClass('fa-spin');
        }
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
            
            success: function(response) {
                consecutiveErrors = 0;
                updateConnectionIndicator('connected');

                if (response.data) {
                    ['a', 'b', 'c', 'd'].forEach(function(batt) {
                        const key = 'battery_' + batt;
                        const value = parseFloat(response.data[key]) || 0;
                        updateSensorAlert(key, value);
                    });

                    const plnVolt = parseFloat(response.data.pln_volt) || 0;
                    const plnCurrent = parseFloat(response.data.pln_current) || 0;
                    const plnPower = parseFloat(response.data.pln_power) || 0;

                    updateSensorAlert('pln_volt', plnVolt);
                    updateSensorAlert('pln_current', plnCurrent);
                    updateSensorAlert('pln_power', plnPower);

                    const temp1 = parseFloat(response.data.temperature_1) || 0;
                    const temp2 = parseFloat(response.data.temperature_2) || 0;

                    updateSensorAlert('temperature_1', temp1);
                    updateSensorAlert('temperature_2', temp2);

                    const fan1On = toOnOff(response.data.relay_1 ?? response.relay_1);
                    const fan2On = toOnOff(response.data.relay_2 ?? response.relay_2);
                    
                    setFanUI(1, fan1On);
                    setFanUI(2, fan2On);

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

                if (response.server_data) {
                    const serverVolt = parseFloat(response.server_data.server_voltage) || 0;
                    updateSensorAlert('server_voltage', serverVolt);
                }
            },
            
            error: function() {
                consecutiveErrors++;
                updateConnectionIndicator('error');

                if (consecutiveErrors >= MAX_CONSECUTIVE_ERRORS) {
                    stopUpdates();
                    setTimeout(function() {
                        consecutiveErrors = 0;
                        startUpdates();
                    }, RETRY_DELAY);
                }
            },
            
            complete: function() {
                isUpdating = false;
            }
        });
    }

    async function loadRanges() {
        try {
            const response = await $.ajax({
                url: '/sensor-ranges',
                method: 'GET',
                dataType: 'json',
                cache: false
            });

            if (response.success && response.data) {
                ranges = response.data;
                updateDisplayedRanges();
                return true;
            }
            return false;
        } catch (e) {
            return false;
        }
    }

    function updateDisplayedRanges() {
        Object.keys(ranges).forEach(key => {
            const rangeEl = $(`#range_${key}`);
            if (rangeEl.length) {
                rangeEl.find('.range-min').text(ranges[key].min);
                rangeEl.find('.range-max').text(ranges[key].max);
            }
        });
    }

    window.openSettingsModal = function(sensorKey) {
        $('#settings_sensor_key').val(sensorKey);
        $('#settings_sensor_name').text(sensorNames[sensorKey] || sensorKey);
        $('#settings_unit').text(`(${units[sensorKey]})`);
        
        const currentRange = ranges[sensorKey];
        if (currentRange) {
            $('#settings_min').val(currentRange.min);
            $('#settings_max').val(currentRange.max);
        }
        
        $('#settingsModal').modal('show');
    };

    window.saveSettings = function() {
        const sensorKey = $('#settings_sensor_key').val();
        const minVal = parseFloat($('#settings_min').val());
        const maxVal = parseFloat($('#settings_max').val());
        
        if (isNaN(minVal) || isNaN(maxVal)) {
            alert('Please enter valid numbers');
            return;
        }
        
        if (minVal >= maxVal) {
            alert('Minimum value must be less than maximum value');
            return;
        }

        const saveBtn = $('#settingsModal').find('.btn-primary');
        saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
        
        $.ajax({
            url: '/sensor-ranges',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                sensor_key: sensorKey,
                min_value: minVal,
                max_value: maxVal
            },
            success: function(response) {
                if (response.success) {
                    ranges[sensorKey] = { min: minVal, max: maxVal };
                    updateDisplayedRanges();
                    $('#settingsModal').modal('hide');
                    showSuccessToast(`Settings saved for ${sensorNames[sensorKey]}`);
                    updateDashboard();
                } else {
                    alert(response.message || 'Failed to save settings');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to save settings';
                alert(message);
            },
            complete: function() {
                saveBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Changes');
            }
        });
    };

    window.resetToDefault = function() {
        const sensorKey = $('#settings_sensor_key').val();
        
        if (confirm(`Reset ${sensorNames[sensorKey]} to default range?`)) {
            const resetBtn = $('#settingsModal').find('.btn-secondary');
            resetBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Resetting...');

            $.ajax({
                url: '/sensor-ranges',
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
                        showSuccessToast(`Reset to default: ${sensorNames[sensorKey]}`);
                    } else {
                        alert(response.message || 'Failed to reset settings');
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to reset settings';
                    alert(message);
                },
                complete: function() {
                    resetBtn.prop('disabled', false).html('<i class="fas fa-undo mr-1"></i> Reset to Default');
                }
            });
        }
    };

    function showSuccessToast(message) {
        const toastHtml = `
            <div class="toast" role="alert" data-delay="3000">
                <div class="toast-header bg-success text-white">
                    <i class="fas fa-check-circle mr-2"></i>
                    <strong class="mr-auto">Success</strong>
                    <button type="button" class="ml-2 close text-white" data-dismiss="toast">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        const toast = $(toastHtml);
        $('#toast-container').append(toast);
        toast.toast('show');
        
        toast.on('hidden.bs.toast', function() {
            $(this).remove();
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

    $(document).ready(async function() {
        await loadRanges();
        initAlertSound();
        startUpdates();
        document.addEventListener('visibilitychange', handleVisibilityChange);
    });

    $(window).on('beforeunload', function() {
        stopUpdates();
    });
</script>
@endpush