<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceSettingController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SensorRangeController;

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/realtime', [DashboardController::class, 'getRealtimeData'])->name('dashboard.realtime');

    Route::get('/device_settings', [DeviceSettingController::class, 'index'])->name('device_settings.index');
    Route::post('/device_settings/update', [DeviceSettingController::class, 'update'])->name('device_settings.update');
    Route::get('/device_settings/{deviceCode}', [DeviceSettingController::class, 'show'])->name('device_settings.show');


    Route::get('/logs', [LogsController::class, 'index'])->name('logs.index');
    Route::get('/logs/export', [LogsController::class, 'export'])->name('logs.export');
    Route::delete('/logs', [LogsController::class, 'destroy'])->name('logs.destroy');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    
     Route::get('/sensor-ranges', [SensorRangeController::class, 'index'])->name('sensor-ranges.index');
    Route::post('/sensor-ranges', [SensorRangeController::class, 'store'])->name('sensor-ranges.store');
    Route::post('/sensor-ranges/bulk', [SensorRangeController::class, 'bulkStore'])->name('sensor-ranges.bulk');
    Route::delete('/sensor-ranges', [SensorRangeController::class, 'destroy'])->name('sensor-ranges.destroy');

});

require __DIR__.'/auth.php';
