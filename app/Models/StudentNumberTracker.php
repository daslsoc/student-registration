<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Tracks the incrementing student_number across children.
 *
 * @property int $current_number
 */
class StudentNumberTracker extends Model
{
    use HasFactory;

    protected $fillable = [
        'current_number',
    ];

    /**
     * Boot to log changes for debugging.
     */
    protected static function booted()
    {
        static::updated(function ($model) {
            Log::info('StudentNumberTracker updated', $model->toArray());
        });
    }
}
