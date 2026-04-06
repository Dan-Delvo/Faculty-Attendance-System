<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'flss_room_id',
        'room_code',
        'building_name',
    ];
}
