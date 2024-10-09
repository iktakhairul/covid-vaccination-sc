<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User; // Adjust the namespace as per your application structure
use App\Events\VaccinationRemainder; // Your event that sends emails
use Carbon\Carbon;

class SendVaccinationReminder extends Command
{
    protected $signature = 'vaccination:remind';
    protected $description = 'Send vaccination reminder emails to users scheduled for tomorrow.';

    public function handle()
    {
        // Get the date for tomorrow
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $users = User::whereDate('scheduled_vaccination_date', $tomorrow)->with('vaccineCenter')->get();

        foreach ($users as $user) {
            // Fire the event to send an email notification
            event(new VaccinationRemainder($user));
        }

        $this->info('Vaccination reminders sent for ' . $tomorrow);
    }
}
