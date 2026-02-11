<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'description',
        'is_editable',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_editable' => 'boolean',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                     */
    /* ------------------------------------------------------------------ */

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
