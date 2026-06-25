<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

/**
 * ParentModel represents a parent/guardian record.
 *
 * @property int $id
 * @property string $parent1_first_name
 * @property string $parent1_last_name
 * @property string $parent1_email
 *                                 ...
 */
class ParentModel extends Model
{
    use HasFactory;

    protected $table = 'parents';

    protected $fillable = [
        'parent1_first_name',
        'parent1_last_name',
        'parent1_email',
        'parent1_phone',
        'parent2_first_name',
        'parent2_last_name',
        'parent2_email',
        'parent2_phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'relationship_to_family',
        'update_token',
        'token_expires_at',
        'payment_token',
        'registration_status',
        'postcode',
        'guidelines_accepted',
    ];

    protected $hidden = [
        'update_token', 'payment_token',
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    /**
     * Boot the model to log creation/updates/deletions.
     *
     * @return void
     */
    protected static function booted()
    {
        // Log identifiers only — never the full record (names/emails/phones
        // are PII and tokens are sensitive).
        static::created(function ($model) {
            Log::info('Parent created', ['id' => $model->id, 'status' => $model->registration_status]);
        });
        static::updated(function ($model) {
            Log::info('Parent updated', ['id' => $model->id, 'status' => $model->registration_status]);
        });
        static::deleted(function ($model) {
            Log::info('Parent deleted', ['id' => $model->id]);
        });
    }

    /**
     * One parent can have many children.
     *
     * @return HasMany
     */
    public function children()
    {
        return $this->hasMany(Child::class, 'parent_id');
    }

    /**
     * One parent can have many payments.
     *
     * @return HasMany
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'parent_id');
    }
}
