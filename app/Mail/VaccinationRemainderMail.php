<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class VaccinationRegisteredMail
 *
 * This mailable sends the vaccination registration email.
 */
class VaccinationRemainderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The data for the email.
     *
     * @var array
     */
    public $mail_data;

    /**
     * Create a new message instance.
     *
     * @param array $mail_data The data for the email.
     */
    public function __construct(array $mail_data)
    {
        $this->mail_data = $mail_data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.vaccination-reminder')
            ->subject($this->mail_data['subject']);
    }
}
