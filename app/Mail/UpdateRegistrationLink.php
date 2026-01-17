<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * UpdateRegistrationLink mail for re-registration or updating an existing record.
 */
class UpdateRegistrationLink extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The unique update link.
     *
     * @var string
     */
    public $url;

    /**
     * Create a new message instance.
     *
     * @param string $url
     */
    public function __construct($url)
    {
        $this->url = $url;
        Log::info("Queueing UpdateRegistrationLink mail for link={$url}");
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Update Your Registration')
                    ->view('emails.update_link');
    }
}
