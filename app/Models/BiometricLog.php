<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BiometricLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'biometric_id',
        'log_datetime',
        'log_type',
        'device_id',
        'import_batch_id',
        'is_processed',
    ];

    protected function casts(): array
    {
        return [
            'log_datetime' => 'datetime',
            'is_processed' => 'boolean',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                     */
    /* ------------------------------------------------------------------ */

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'biometric_id', 'biometric_id');
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }
}
