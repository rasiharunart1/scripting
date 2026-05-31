<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceSettingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $device = $user->device;
        $settings = $device->deviceSettings;
        return view('device_settings.index', compact('device', 'settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'temp1_threshold'      => 'nullable|numeric',
            'temp2_threshold'      => 'nullable|numeric',
            'hysteresis'           => 'nullable|numeric',
            'interval_record'      => 'nullable|numeric',
            'charger_mode'         => 'nullable|string|in:manual,auto',
            'charger_threshold_min' => 'nullable|numeric',
            'charger_threshold_max' => 'nullable|numeric',
        ]);

        $user = Auth::user();
        $device = $user->device;
        
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan'
            ], 404);
        }

        $settings = $device->deviceSettings;

        if (!$settings) {
            return response()->json([
                'success' => false,
                'message' => 'Device settings tidak ditemukan'
            ], 404);
        }

        $data = [];

        // Handle temperature and hysteresis settings
        if ($request->has('temp1_threshold') && $request->temp1_threshold !== null) {
            $data['temp1_threshold'] = (float) $request->temp1_threshold;
        }
        if ($request->has('temp2_threshold') && $request->temp2_threshold !== null) {
            $data['temp2_threshold'] = (float) $request->temp2_threshold;
        }
        if ($request->has('hysteresis') && $request->hysteresis !== null) {
            $data['hysteresis'] = (float) $request->hysteresis;
        }
        if ($request->has('interval_record') && $request->interval_record !== null) {
            $data['interval_record'] = (int) $request->interval_record;
        }

        // Handle charger settings
        if ($request->has('charger_mode') && $request->charger_mode !== null) {
            $data['charger_mode'] = $request->charger_mode;
        }
        if ($request->has('charger_threshold_min') && $request->charger_threshold_min !== null) {
            $data['charger_threshold_min'] = (float) $request->charger_threshold_min;
        }
        if ($request->has('charger_threshold_max') && $request->charger_threshold_max !== null) {
            $data['charger_threshold_max'] = (float) $request->charger_threshold_max;
        }

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data untuk diupdate'
            ], 400);
        }

        $settings->update($data);

        // If it's an AJAX request, return JSON
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan perangkat berhasil diperbarui.',
                'data' => $settings->toArray()
            ], 200);
        }

        return redirect()->route('device_settings.index')->with('success', 'Pengaturan perangkat berhasil diperbarui.');
    }

    public function show($deviceCode)
    {
        $device = Device::where('device_cod', $deviceCode)->first();
        $settings = $device->settings;
        if(!$device){
            return response()->json([
                'success'=>false,
                'message'=>'Device not found'
            ],404);
        }

        return response()->json([
            'success'=>true,
            'data'=>[
                'device'=>$device,
                'settings'=>$settings
            ]
        ]);
    }
}
