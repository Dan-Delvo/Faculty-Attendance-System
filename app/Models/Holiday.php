<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Holiday extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'holiday_date',
        'name',
        'type',
        'is_recurring',
    ];

    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
            'is_recurring' => 'boolean',
        ];
    }
}
