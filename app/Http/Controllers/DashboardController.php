<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
            }
            
            $device = $user->device;
            
            if (!$device) {
                return redirect()->route('welcome')->with('error', 'Device tidak ditemukan.  Hubungi administrator.');
            }

            $latestData = $device->getLatestSensorData();
            $latestServerData = $device->getLatestServerSensorData();

            return view('dashboard.index', compact('device', 'latestData', 'latestServerData'));
            
        } catch (\Exception $e) {
            Log::error('Dashboard index error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat dashboard.');
        }
    }

    public function getRealtimeData(Request $request)
    {
        Log::info('Realtime data request received', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => Auth::id(),
            'ip' => $request->ip()
        ]);
        
        try {
            if (!Auth::check()) {
                Log::warning('Realtime request without authentication');
                return response()->json([
                    'success' => false,
                    'error' => 'User tidak terautentikasi.'
                ], 401);
            }
            
            $user = Auth::user();
            $device = $user->device;
            
            if (!$device) {
                Log::warning('User has no device', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'error' => 'Device tidak ditemukan.'
                ], 404);
            }
            
            $latestData = $device->getLatestSensorData();
            $latestServerData = $device->getLatestServerSensorData();
            
            $device->checkOnlineStatus();

            $response = [
                'success' => true,
                'device_status' => $device->status ??  'unknown',
                'last_seen' => $device->last_seen ? $device->last_seen->diffForHumans() : 'Never',
                'data' => $latestData ?  $latestData->toArray() : null,
                'server_data' => $latestServerData ? $latestServerData->toArray() : null,
                'device_settings' => $device->deviceSettings ? $device->deviceSettings->toArray() : null,
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ];
            
            Log::info('Realtime data sent successfully', [
                'user_id' => $user->id,
                'device_id' => $device->id
            ]);
            
            return response()->json($response, 200)
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
            
        } catch (\Exception $e) {
            Log::error('Realtime data error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan saat mengambil data realtime.',
                'message' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function updateRelay(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'error' => 'User tidak terautentikasi.'
                ], 401);
            }

            $user = Auth::user();
            $device = $user->device;

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'error' => 'Device tidak ditemukan.'
                ], 404);
            }

            $request->validate([
                'relay_type' => 'required|string|in:charger',
                'status' => 'required|boolean|integer',
            ]);

            $relayType = $request->relay_type;
            $status = (int) $request->status;

            // Update SensorDataStill (current status)
            $latestData = $device->SensorDataStill()->latest()->first();
            
            if (!$latestData) {
                return response()->json([
                    'success' => false,
                    'error' => 'Data sensor tidak ditemukan.'
                ], 404);
            }

            if ($relayType === 'charger') {
                // Guard: jangan izinkan toggle manual jika mode auto (ESP yang kontrol)
                $settings = $device->deviceSettings;
                if ($settings && $settings->charger_mode === 'auto') {
                    return response()->json([
                        'success' => false,
                        'error'   => 'Charger sedang dalam mode Auto. Ubah ke Manual untuk kontrol manual.',
                        'charger_mode' => 'auto',
                    ], 403);
                }

                $latestData->relay_charger = $status;
                $latestData->save();
            }

            Log::info("Relay updated: {$relayType} = {$status}", [
                'user_id' => $user->id,
                'device_id' => $device->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Relay berhasil diupdate.',
                'relay_type' => $relayType,
                'status' => $status
            ], 200);

        } catch (\Exception $e) {
            Log::error('Update relay error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan saat mengupdate relay.',
                'message' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}