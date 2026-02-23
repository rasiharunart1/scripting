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
            'temp1_threshold' => 'required|numeric',
            'temp2_threshold' => 'required|numeric',
            'hysteresis' => 'required|numeric',
        ]);

        $user = Auth::user();
        $device = $user->device;
        $settings = $device->deviceSettings;

        $data = [
            'temp1_threshold' => (float)$request->temp1_threshold,
            'temp2_threshold' => (float)$request->temp2_threshold,
            'hysteresis' => (float)$request->hysteresis,
        ];

        $settings->update($data);

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
