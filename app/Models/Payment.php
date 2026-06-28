<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'method',
    ];

    /**
     * Boot method to log creation/updates/deletions of payments.
     */
    protected static function booted()
    {
        // Identifiers + amount only (no parent PII).
        static::created(function ($model) {
            Log::info('Payment created', ['id' => $model->id, 'parent_id' => $model->parent_id, 'amount_paid' => $model->amount_paid]);
        });
        static::updated(function ($model) {
            Log::info('Payment updated', ['id' => $model->id, 'parent_id' => $model->parent_id]);
        });
        static::deleted(function ($model) {
            Log::info('Payment deleted', ['id' => $model->id, 'parent_id' => $model->parent_id]);
        });
    }

    /**
     * Payment belongs to a parent.
     *
     * @return BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(ParentModel::class, 'parent_id');
    }
}
