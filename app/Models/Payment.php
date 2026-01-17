<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Payment model tracks payments made by a parent.
 *
 * @property int $parent_id
 * @property float $amount_paid
 * @property \DateTime $paid_date
 */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'amount_paid',
        'paid_date',
    ];

    /**
     * Boot method to log creation/updates/deletions of payments.
     */
    protected static function booted()
    {
        static::created(function ($model) {
            Log::info('Payment created', $model->toArray());
        });
        static::updated(function ($model) {
            Log::info('Payment updated', $model->toArray());
        });
        static::deleted(function ($model) {
            Log::info('Payment deleted', $model->toArray());
        });
    }

    /**
     * Payment belongs to a parent.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(ParentModel::class, 'parent_id');
    }
}
