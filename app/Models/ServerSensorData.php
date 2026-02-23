<?php

namespace App\Models;

// use 
use Illuminate\Database\Eloquent\Model;

class ServerSensorData extends Model
{
    protected $fillable = [
        'device_id',
        'server_voltage'
    ];
    
    public function device(){
        return $this->belongsTo(Device::class);
    }
}
