<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorRange extends Model
{
    protected $fillable = [
        'user_id',
        'sensor_key',
        'min_value',
        'max_value',
    ];
        
    protected $casts = [
        'min_value'=>'float',
        'max_value'=>'float',
    ];
    
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function device(){
        return $this->belongsTo(Device::class);
    }
}
