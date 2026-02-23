<?php

namespace App\Http\Controllers;

use App\Models\SensorRange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SensorRangeController extends Controller
{
    public function index()
    {
        $ranges = Auth::user()->sensorRanges()
            ->get()
            ->keyBy('sensor_key')
            ->map(function ($range) {
                return [
                    'min' => $range->min_value,
                    'max' => $range->max_value,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $ranges
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sensor_key' => 'required|string|max:50',
            'min_value' => 'required|numeric',
            'max_value' => 'required|numeric|gt:min_value',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $sensorRange = SensorRange::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'sensor_key' => $request->sensor_key,
            ],
            [
                'min_value' => $request->min_value,
                'max_value' => $request->max_value,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully',
            'data' => [
                'min' => $sensorRange->min_value,
                'max' => $sensorRange->max_value,
            ]
        ]);
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sensor_key' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        SensorRange::where('user_id', Auth::id())
            ->where('sensor_key', $request->sensor_key)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Settings reset to default'
        ]);
    }

    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ranges' => 'required|array',
            'ranges.*.sensor_key' => 'required|string|max:50',
            'ranges.*.min_value' => 'required|numeric',
            'ranges.*.max_value' => 'required|numeric|gt:ranges.*.min_value',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $userId = Auth::id();
        $savedRanges = [];

        foreach ($request->ranges as $range) {
            $sensorRange = SensorRange::updateOrCreate(
                [
                    'user_id' => $userId,
                    'sensor_key' => $range['sensor_key'],
                ],
                [
                    'min_value' => $range['min_value'],
                    'max_value' => $range['max_value'],
                ]
            );

            $savedRanges[$range['sensor_key']] = [
                'min' => $sensorRange->min_value,
                'max' => $sensorRange->max_value,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'All settings saved successfully',
            'data' => $savedRanges
        ]);
    }
}