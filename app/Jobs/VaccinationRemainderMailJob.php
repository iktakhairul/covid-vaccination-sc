<?php

namespace App\Jobs;

use App\Mail\VaccinationRemainderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class VaccinationRegisteredMailJob
 *
 * This job is responsible for sending vaccination registration confirmation emails.
 */
class VaccinationRemainderMailJob extends Job implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $mail_data;

    /**
     * Create a new job instance.
     *
     * @param array $mail_data The mail data.
     * @return void
     */
    public function __construct(array $mail_data)
    {
        $this->mail_data = $mail_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->mail_data['to_email'])
            ->cc($this->mail_data['cc_array'])
            ->send(new VaccinationRemainderMail($this->mail_data));
    }
}
