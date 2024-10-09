<?php

namespace App\Listeners;

use App\Events\VaccinationRemainder;
use App\Jobs\VaccinationRemainderMailJob;
use Carbon\Carbon;

/**
 * Class SendVaccinationRegisteredEmail
 *
 * This listener handles the sending of an email when a vaccination registration occurs.
 */
class SendVaccinationRemainderEmail
{
    /**
     * Handle the event.
     *
     * @param VaccinationRegistered $event The vaccination registered event.
     * @return void
     */
    public function handle(VaccinationRemainder $event)
    {
        // Get the user from the event
        $user = $event->user;

        // Prepare the mail data
        $mail_data = [
            'to_email' => $user->email,
            'receiver_name' => $user->full_name,
            'subject' => 'Vaccination Reminder: Upcoming Appointment',
            'body' => 'Dear ' . $user->full_name . ',<br><br>
        This is a friendly reminder about your upcoming vaccination appointment! Your commitment to your health and the well-being of our community is appreciated.<br><br>
        Here are the details of your appointment:<br>
        - <strong>Center:</strong> ' . ($user->vaccineCenter->center_name ?? "N/A") . '<br>
        - <strong>Location:</strong> ' . ($user->vaccineCenter->location ?? "N/A") . '<br>
        - <strong>Appointment Date:</strong> ' . Carbon::parse($user->scheduled_vaccination_date)->format('M d, Y') . '<br><br>
        Please remember to bring a valid ID and arrive at least 10 minutes early for your appointment. If you have any questions or need to reschedule, feel free to reach out to us.<br><br>
        Thank you for taking this important step towards a healthier future! We look forward to seeing you soon.<br><br>
        Best regards,<br>
        The Vaccination Team',
            'cc_array' => null, // If you have CC emails, set them here
            'attachment_paths' => null // If you have attachments, set them here
        ];


        // Dispatch mail job
        dispatch(new VaccinationRemainderMailJob($mail_data));
    }
}
