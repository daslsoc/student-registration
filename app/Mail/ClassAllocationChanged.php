<?php

namespace App\Mail;

use App\Models\Child;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Sent to a family when an admin changes a child's allocated class for one or
 * both subjects on the Class Allocations page. Only the subjects that actually
 * changed are included.
 */
class ClassAllocationChanged extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The child whose allocation changed.
     *
     * @var Child
     */
    public $child;

    /**
     * The per-subject changes, each: ['subject' => string, 'from' => ?string, 'to' => ?string].
     *
     * @var array<int, array{subject: string, from: ?string, to: ?string}>
     */
    public $changes;

    /**
     * @param  array<int, array{subject: string, from: ?string, to: ?string}>  $changes
     */
    public function __construct(Child $child, array $changes)
    {
        $this->child = $child;
        $this->changes = $changes;
        Log::info("Queueing ClassAllocationChanged mail for child_id={$child->id}");
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Your child\'s class allocation has changed')
            ->view('emails.class_allocation_changed');
    }
}
