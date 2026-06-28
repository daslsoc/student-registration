<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

/**
 * Child model representing each child's details.
 *
 * @property int $id
 * @property int $parent_id
 * @property string $first_name
 *                              ...
 */
class Child extends Model
{
    use HasFactory;

    protected $table = 'children';

    protected $fillable = [
        'parent_id',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'residency_status',
        'day_school_name',
        'day_school_year',
        'allergies',
        'special_needs',
        'allocated_dhamma_class',
        'allocated_sinhala_class',
        'student_number',
        'year_of_first_registration',
        'photography_allowed',
    ];

    /**
     * Boot method to log creation/updates/deletions of child records.
     */
    protected static function booted()
    {
        // Log identifiers only — child names/DOB/allergies are PII.
        static::created(function ($model) {
            Log::info('Child created', ['id' => $model->id, 'parent_id' => $model->parent_id, 'student_number' => $model->student_number]);
        });
        static::updated(function ($model) {
            Log::info('Child updated', ['id' => $model->id, 'parent_id' => $model->parent_id, 'student_number' => $model->student_number]);
        });
        static::deleted(function ($model) {
            Log::info('Child deleted', ['id' => $model->id, 'parent_id' => $model->parent_id]);
        });
    }

    /**
     * Child belongs to one parent.
     *
     * @return BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(ParentModel::class, 'parent_id');
    }

    public function age()
    {
        return Carbon::parse($this->attributes['date_of_birth'])->age;
    }
}
