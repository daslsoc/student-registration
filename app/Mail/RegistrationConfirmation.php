<?php

namespace App\Mail;

use App\Models\ParentModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * RegistrationConfirmation mail is sent when a parent successfully completes payment.
 */
class RegistrationConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The parent instance.
     *
     * @var ParentModel
     */
    public $parent;

    /**
     * Create a new message instance.
     *
     * @param ParentModel $parent
     */
    public function __construct(ParentModel $parent)
    {
        $this->parent = $parent;
        Log::info("Queueing RegistrationConfirmation mail for parent_id={$parent->id}");
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('School Registration Confirmation')
                    ->view('emails.registration_confirmation');
    }
}
