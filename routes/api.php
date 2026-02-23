<?php

use App\Http\Controllers\Api\SensorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/sensor-data/', [SensorController::class, 'store']);
Route::post('/sensor-data/server-sensor/', [SensorController::class, 'storeServerSensor']);
Route::get('/settings/{device_code}', [SensorController::class, 'getDeviceSettings']);
Route::get('/sensor-data/getSensorData/{device_code}', [SensorController::class, 'getSensorData']);
Route::get('/sensor-data/getData/{device_code}', [SensorController::class, 'getSensorData']);
Route::get('/sensor-data/getDataAll/{device_code}', [SensorController::class, 'getSensorDataAll']);
Route::get('/sensor-data/range/{device_code}', [SensorController::class, 'getRangeSensor']);
// Route::post('/settings/', [SensorController::class, 'getDeviceSettings']);

Route::fallback(function(){
    abort(404);
});
