<?php

namespace App\Http\Controllers;

use App\Exports\SensorDataExport;
use App\Models\Device;
use App\Models\SensorData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class LogsController extends Controller
{
    public function destroyAll(){
      $device = Device::where('user_id', auth()->id())->first();
      if(!$device){
          return response()->json([
              'success'=> false,
              'message'=>'Device tidak ditemukan']);
      }
      
      SensorData::where('device_id', $device->id)->delete();
      
      return response()->json([
          'success'=>true,
          'message'=>'berhasil menghapus semua data']);
    }
    public function destroy(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $user = Auth::user();
        $device = $user->device;

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan',
            ], 404);
        }

        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        try {
            $query = SensorData::query()
                ->where('device_id', $device->id)
                ->when($start, fn($q) => $q->where('created_at', '>=', $start))
                ->when($end, fn($q) => $q->where('created_at', '<=', $end));

            $count = $query->count();

            if ($count === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data yang sesuai dengan filter.',
                ], 200);
            }

            $deleted = $query->delete();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus " . number_format($deleted) . " data log.",
                'deleted_count' => $deleted,
            ]);
        } catch (\Throwable $th) {
            \Log::error('Error deleting sensor data: ' . $th->getMessage(), [
                'user_id' => $user->id,
                'device_id' => $device->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data.  Silakan coba lagi.',
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:1000'],
        ]);

        $user = Auth::user();
        $device = $user->device;

        // Default date range: last 7 days
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        if (!$startDate && !$endDate) {
            $startDate = Carbon::now()->subDays(7)->format('Y-m-d');
            $endDate   = Carbon::now()->format('Y-m-d');
        }

        // Default per page
        $perPage = $request->query('per_page', 10);
        
        // Validate per_page value
        $perPage = in_array($perPage, [5,10, 25, 50, 100, 500, 1000]) ? $perPage : 50;

        $error = null;
        $logs = collect();
        $pagination = null;
        $totalRecords = 0;

        if (!$device) {
            $error = 'Device tidak ditemukan untuk akun ini.';
        } else {
            try {
                $query = SensorData::query()
                    ->where('device_id', $device->id)
                    ->when($startDate, function ($q) use ($startDate) {
                        return $q->where('created_at', '>=', Carbon::parse($startDate)->startOfDay());
                    })
                    ->when($endDate, function ($q) use ($endDate) {
                        return $q->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
                    })
                    ->orderBy('created_at', 'desc')
                    ->select([
                        'id',
                        'device_id',
                        'battery_a',
                        'battery_b',
                        'battery_c',
                        'battery_d',
                        'temperature_1',
                        'temperature_2',
                        'pln_volt',
                        'pln_current',
                        'pln_power as power',
                        'relay_1',
                        'relay_2',
                        'timeDevice',
                        'temp1_threshold',     // Tambahan
                        'temp2_threshold',     // Tambahan
                        'hysteresis', 
                        'created_at',
                    ]);

                // Get total count before pagination for statistics
                $totalRecords = $query->count();

                // Paginate with query string preservation
                $pagination = $query->paginate($perPage)->appends($request->query());
                $logs = $pagination->getCollection();

            } catch (\Throwable $th) {
                \Log::error('Error fetching sensor data: ' . $th->getMessage(), [
                    'user_id' => $user->id,
                    'device_id' => $device->id ??  null,
                ]);
                
                $error = 'Terjadi kesalahan saat mengambil data. Silakan coba lagi.';
            }
        }

        return view('logs.index', [
            'logs' => $logs,
            'pagination' => $pagination,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'error' => $error,
            'totalRecords' => $totalRecords,
            'perPage' => $perPage,
            'deviceId'=>$device->id,
        ]);
    }
    public function exportAll($deviceId){
        return Excel::download(
            new SensorDataExport($deviceId, null, null),
            'all_sensor_data.xlsx');
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $user = Auth::user();
        $device = $user->device;

        if (!$device) {
            return redirect()
                ->route('logs.index', $request->all())
                ->with('error', 'Device tidak ditemukan untuk akun ini.');
        }

        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        // Check if there's data to export
        $count = SensorData::query()
            ->where('device_id', $device->id)
            ->when($startDate, fn($q) => $q->where('created_at', '>=', Carbon::parse($startDate)->startOfDay()))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', Carbon::parse($endDate)->endOfDay()))
            ->count();

        if ($count === 0) {
            return redirect()
                ->route('logs.index', $request->all())
                ->with('error', 'Tidak ada data untuk diekspor.');
        }

        // Generate filename with timestamp
        $dateLabel = $startDate && $endDate 
            ? Carbon::parse($startDate)->format('Ymd') . '_to_' . Carbon::parse($endDate)->format('Ymd')
            : 'all_data';
        
        $filename = 'sensor_logs_' . $device->id . '_' . $dateLabel . '_' . now()->format('YmdHis') . '.xlsx';

        try {
            return Excel::download(
                new SensorDataExport($device->id, $startDate, $endDate),
                $filename
            );
        } catch (\Throwable $th) {
            \Log::error('Error exporting sensor data: ' . $th->getMessage(), [
                'user_id' => $user->id,
                'device_id' => $device->id,
            ]);

            return redirect()
                ->route('logs.index', $request->all())
                ->with('error', 'Gagal mengekspor data.  Silakan coba lagi.');
        }
    }
}