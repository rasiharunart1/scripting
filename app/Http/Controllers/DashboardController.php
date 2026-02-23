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
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ];
            
            Log::info('Realtime data sent successfully', [
                'user_id' => $user->id,
                'device_id' => $device->id
            ]);
            
            return response()->json($response, 200);
            
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
}