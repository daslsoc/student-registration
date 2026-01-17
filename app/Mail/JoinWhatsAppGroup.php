<?php

namespace App\Mail;

use App\Models\ParentModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * JoinWhatsAppGroup mail is sent when a parent successfully completes payment.
 */
class JoinWhatsAppGroup extends Mailable implements ShouldQueue
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
        Log::info("Queueing JoinWhatsAppGroup mail for parent_id={$parent->id}");
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Join Our Official WhatsApp Group for Updates')
                    ->view('emails.join_whatsapp_group');
    }
}
