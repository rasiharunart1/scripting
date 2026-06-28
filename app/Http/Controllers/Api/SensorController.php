<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SensorData;
use App\Models\SensorDataStill;
use Carbon\Carbon;
use App\Models\SensorRange;
use App\Models\ServerSensorData;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SensorController extends Controller
{
    public function storeServerSensor(Request $request){
        $request->validate([
            'device_code'=>'required|string|exists:devices,device_code',
            'server_voltage'=>'nullable',
            ]);
            
        $device = Device::where('device_code', $request->device_code)->first();
        
        if(!$device){
            return response()->json(['message'=>'device tidak terdaftar'], 404);
        }
        
        // $device->updateLastSeen();
        
        $data = [
            'device_id'=>$device->id,
            'server_voltage'=>$request->server_voltage,
            ];
        ServerSensorData::create($data);
        
        return response()->json([
            'message'=>"ServerSensorData berhasil disimpan",
            'data'=>$data
            ], 201);
    }
    public function store (Request $request)
    {

       $request->validate([
            'device_code' => 'required|string|exists:devices,device_code',
                'battery_a' => 'nullable',
                'battery_b' => 'nullable',
                'battery_c' => 'nullable',
                'battery_d' => 'nullable',
                'temperature_1' => 'nullable',
                'temperature_2' => 'nullable',
                'pln_volt' => 'nullable',
                'pln_current' => 'nullable',
                'pln_power' => 'nullable',
                'relay_1' => 'nullable',
                'relay_2' => 'nullable',
                // 'relay_charger' => 'nullable',
                'timeDevice' => 'nullable|string',
        ]);

        $device = Device::where('device_code', $request->device_code)->first();
        if (!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }
        $device ->updateLastSeen();
        $deviceSettings = $device->deviceSettings;

        $data = [
                'device_id'=> $device->id,
                'battery_a' => $request->battery_a,
                'battery_b' => $request->battery_b,
                'battery_c' => $request->battery_c,
                'battery_d' => $request->battery_d, // Optional field
                'temperature_1' => $request->temperature_1,
                'temperature_2' => $request->temperature_2,
                'pln_volt' => $request->pln_volt,
                'pln_current' => $request->pln_current,
                'pln_power' => $request->pln_power,
                'relay_1' => $request->relay_1,
                'relay_2' => $request->relay_2,
                // 'relay_charger' => $request->relay_charger,
                'timeDevice' => $request->timeDevice,
                'temp1_threshold' => $deviceSettings ? $deviceSettings->temp1_threshold : null,
                'temp2_threshold' => $deviceSettings ? $deviceSettings->temp2_threshold : null,
                'hysteresis' => $deviceSettings ? $deviceSettings->hysteresis : null,
            ];

        // Store sensor data
    SensorDataStill::updateOrCreate(
        ['device_id' => $device->id],
        $data
    );
        if (!$deviceSettings || !$deviceSettings->interval_record) {
            return response()->json([
                'message' => 'Interval record belum disetting',
                'history_created' => false
            ], 200);
        }
    
        $interval = (int) $deviceSettings->interval_record;
        $now = Carbon::now();
        $shouldCreateHistory = false;
        $sensorData = null;
        
        $updated = Device::where('id', $device->id)
            ->where(function ($query) use ($interval, $now) {
                $query->whereNull('last_history_at')
                      ->orWhere(
                          'last_history_at',
                          '<=',
                          $now->copy()->subSeconds($interval)
                      );
            })
            ->update([
                'last_history_at' => $now
            ]);
        
        if ($updated) {
            $sensorData = SensorData::create($data);
            $shouldCreateHistory = true;
        }
        
        // $historyInterval = 5*60; // detik (bisa diubah sesuai kebutuhan)
        // $cacheKey = "sensor_last_history_{$device->id}";

        // $shouldCreateHistory = false;
        // $lastHistoryTime = Cache::get($cacheKey);

        // if (!$lastHistoryTime || (time() - $lastHistoryTime) >= $historyInterval) {
        //     $sensorData = SensorData::create($data);
        //     Cache::put($cacheKey, time(), $historyInterval * 2);
        //     $shouldCreateHistory = true;
        // }

        return response()->json([
            'message' => 'Sensor data processed successfully',
            'history_created' => $shouldCreateHistory,
            'data' => $shouldCreateHistory ? $sensorData : null
        ], 201);
    }

    /**
     * Endpoint khusus untuk charger control device.
     * Hanya update relay_charger (dan battery_a opsional) — tidak overwrite field sensor lain.
     * POST /api/charger-relay
     */
    public function updateChargerRelay(Request $request)
    {
        $request->validate([
            'device_code'   => 'required|string|exists:devices,device_code',
            'relay_charger' => 'required|integer|in:0,1',
            'battery_a'     => 'nullable|numeric',
            'timeDevice'    => 'nullable|string',
        ]);

        $device = Device::where('device_code', $request->device_code)->first();
        if (!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        // Hanya update kolom relay_charger (dan battery_a jika dikirim)
        // Gunakan updateOrCreate agar tidak perlu ada record dulu
        $updateData = [
            'relay_charger' => $request->relay_charger,
        ];

        if ($request->has('battery_a') && $request->battery_a !== null) {
            $updateData['battery_a'] = $request->battery_a;
        }

        $still = SensorDataStill::updateOrCreate(
            ['device_id' => $device->id],
            $updateData
        );

        // Simpan ke history logs juga (jika ada interval_record)
        $deviceSettings = $device->deviceSettings;
        if ($deviceSettings && $deviceSettings->interval_record) {
            $interval = (int) $deviceSettings->interval_record;
            $now      = Carbon::now();

            $updated = Device::where('id', $device->id)
                ->where(function ($q) use ($interval, $now) {
                    $q->whereNull('last_history_at')
                      ->orWhere('last_history_at', '<=', $now->copy()->subSeconds($interval));
                })
                ->update(['last_history_at' => $now]);

            if ($updated) {
                // Ambil semua data terkini dari sensor_data_stills untuk history
                $latestStill = SensorDataStill::where('device_id', $device->id)->first();
                if ($latestStill) {
                    SensorData::create([
                        'device_id'      => $device->id,
                        'battery_a'      => $latestStill->battery_a,
                        'battery_b'      => $latestStill->battery_b,
                        'battery_c'      => $latestStill->battery_c,
                        'battery_d'      => $latestStill->battery_d,
                        'temperature_1'  => $latestStill->temperature_1,
                        'temperature_2'  => $latestStill->temperature_2,
                        'pln_volt'       => $latestStill->pln_volt,
                        'pln_current'    => $latestStill->pln_current,
                        'pln_power'      => $latestStill->pln_power,
                        'relay_1'        => $latestStill->relay_1,
                        'relay_2'        => $latestStill->relay_2,
                        'relay_charger'  => $request->relay_charger,  // pakai nilai terbaru
                        'timeDevice'     => $request->timeDevice,
                        'temp1_threshold' => $deviceSettings->temp1_threshold,
                        'temp2_threshold' => $deviceSettings->temp2_threshold,
                        'hysteresis'     => $deviceSettings->hysteresis,
                    ]);
                }
            }
        }

        return response()->json([
            'success'       => true,
            'relay_charger' => $request->relay_charger,
            'message'       => 'Charger relay updated',
        ], 200);
    }
public function getSensorData($deviceCode)
{
        $device = Device::where('device_code', $deviceCode)->first();
        if(!$device){
            return response()->json([
                'success'=>false,
                'message'=> 'Device tidak sesuai atau device code salah'
            ]);
        }

        $sensorData = $device->SensorDataStill()->latest()->first();

        if(!$sensorData){
            return response()->json([
                'success'=> false,
                'message'=> 'Data sensor tidak ditemukan untuk device ini'
            ]);
        }
        return response()->json([
            'data'=> $sensorData,
        ],200);
    }
    public function getRangeSensor($deviceCode){
        $device = Device::where('device_code', $deviceCode)->first();
        
        if(!$device){
            return response()->json([
                'success'=>false,
                'message' => 'Device tidak ditemukan',
                ]);
        }
        
        $userId = $device->user_id;
        
        $rangeSensor = SensorRange::where('user_id', $userId)->get();
        
        if(!$rangeSensor){
            return response()->json([
                'success'=> false,
                'message'=> 'range sensor tidak ditemukan untuk device ini'
            ]);
        }
        // {"sensor_key": [min, max]}
    $compact = [];
    foreach ($rangeSensor as $range) {
        $compact[$range->sensor_key] = [
            (float) $range->min_value,
            (float) $range->max_value
        ];
    }
    
    return response()->json($compact, 200, [], JSON_NUMERIC_CHECK);
    }
    
    public function getSensorDataAll($deviceCode)
    {
        $device = Device::where('device_code', $deviceCode)->first();
        if(!$device){
            return response()->json([
                'success'=>false,
                'message'=> 'Device tidak sesuai atau device code salah'
            ]);
        }

        $sensorData = $device->SensorData()->get();

        if($sensorData->isEmpty()){
            return response()->json([
                'success'=> false,
                'message'=> 'Data sensor tidak ditemukan untuk device ini'
            ]);
        }
        return response()->json([
            $sensorData,
        ],200);
    }
    
     public function getDeviceSettings($deviceCode)
    {
        $device = Device::where('device_code', $deviceCode)->first();
        if (!$device) {
            return response("notfound\n", 404)
                ->header('Content-Type', 'text/plain; charset=UTF-8')
                ->header('Connection', 'close'); // penting untuk ESP
        }

        $settings = $device->deviceSettings;
        if (!$settings) {
            return response("nosettings\n", 404)
                ->header('Content-Type', 'text/plain; charset=UTF-8')
                ->header('Connection', 'close');
        }

        $t1   = $settings->temp1_threshold;
        $t2   = $settings->temp2_threshold;
        $h    = $settings->hysteresis;
        $mode = $settings->charger_mode ?? 'manual';
        $min  = $settings->charger_threshold_min ?? 11.0;
        $max  = $settings->charger_threshold_max ?? 13.5;

        // Get latest relay_charger status
        $latestSensorData = $device->SensorDataStill()->latest()->first();
        $relayCharger = $latestSensorData ? ($latestSensorData->relay_charger ?? 0) : 0;

        // Format: temp1:temp2:hysteresis:relay_charger:charger_mode:charger_min:charger_max
        // Contoh:  35.0:35.0:2.0:0:auto:11.0:13.5
        $body = "{$t1}:{$t2}:{$h}:{$relayCharger}:{$mode}:{$min}:{$max}\n";

        return response($body, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Content-Length', (string) strlen($body));
    }
    // <?php
public function getLatestSensorData($deviceCode)
{
    $device = Device::where('device_code', $deviceCode)->first();

    if (!$device) {
        return response()->json([
            'success' => false,
            'message' => 'Device tidak ditemukan',
        ], 404);
    }

    $sensorData = $device->SensorDataStill()->latest()->first();

    if (!$sensorData) {
        return response()->json([
            'success' => false,
            'message' => 'Data sensor tidak ditemukan',
        ], 404);
    }

    return response()->json([
        'device_code'   => $deviceCode,
        'battery_a'     => $sensorData->battery_a,
        'relay_charger' => $sensorData->relay_charger,
    ]);
}
    // public function getDeviceSettings($deviceCode){
    //     $device = Device::where('device_code', $deviceCode)->first();
    //     if (!$device) {
    //         return response()->json(['message' => 'Device not found'], 404);
    //     }

    //     $settings = $device->deviceSettings;

    //     if(!$settings){
    //         return response()->json([
    //             'success'=> false,
    //             'message'=> 'Settings tidak ditemukan untuk device ini'
    //         ], 404);
    //     }
    //     return response()->json([
    //         'data'=> $settings->toArray()
    //         // 'success'=> true,
    //     ],200);
    // }
//    
}
