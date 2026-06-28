<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One immutable audit record per manual payment-status override.
 *
 * @property int $parent_id
 * @property int|null $user_id
 * @property string $performed_by
 * @property string $action
 * @property string|null $method
 * @property float|null $amount
 */
class PaymentOverride extends Model
{
    use HasFactory;

    public const ACTION_MARKED_PAID = 'marked_paid';

    public const ACTION_REVERTED = 'reverted';

    public const METHOD_CASH = 'cash';

    public const METHOD_EFTPOS = 'eftpos';

    /** Fee waived — financial hardship / recently migrated. Completes at $0. */
    public const METHOD_WAIVED = 'waived';

    /** @return array<int,string> the methods an admin can record. */
    public static function methods(): array
    {
        return [self::METHOD_CASH, self::METHOD_EFTPOS, self::METHOD_WAIVED];
    }

    protected $fillable = [
        'parent_id',
        'user_id',
        'performed_by',
        'action',
        'method',
        'amount',
        'previous_status',
        'new_status',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentModel::class, 'parent_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
