<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorDataStill extends Model
{
 protected $fillable = [
        'device_id',
        'battery_a',
        'battery_b',
        'battery_c',
        'battery_d',
        'temperature_1',
        'temperature_2',
        'pln_volt',
        'pln_current',
        'pln_power',
        'relay_1',
        'relay_2',
        'timeDevice',
    ];

    protected $casts = [
        'battery_a' => 'float',
        'battery_b' => 'float',
        'battery_c' => 'float',
        'battery_d' => 'float',
        'temperature_1' => 'float',
        'temperature_2' => 'float',
        'pln_volt' => 'float',
        'pln_current' => 'float',
        'pln_power' => 'float',
        'relay_1' => 'integer',
        'relay_2' => 'integer',
        'timeDevice' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
