<?php

namespace App\Http\Controllers\Frontend;

use App\Events\VaccinationRemainder;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VaccineCenter;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    /**
     * Display the registration page with available vaccine centers.
     *
     * This method retrieves all vaccine centers and returns the registration view.
     *
     * @return View The view for the vaccine registration page.
     */
    public function create()
    {
        $vaccineCenters = VaccineCenter::select('id', 'center_name', 'location')->get(); // Get all vaccine centers for the dropdown

        return view('frontend.registration', compact('vaccineCenters'));
    }

    /**
     * Store a new vaccination registration in the database.
     *
     * This method validates the incoming request data and creates a new registration entry
     * for the user, scheduling their vaccination and notifying them via email.
     *
     * @param Request $request The incoming request containing user data for registration.
     * @return RedirectResponse A redirect response indicating the result of the registration.
     */
    public function store(Request $request)
    {
        // Validate request data
        $request->validate([
            'user_nid' => 'required|string|unique:users,nid', // Check unique against users table
            'vaccine_center_id' => 'required|exists:vaccine_centers,id',
            'full_name' => 'required|string|max:255', // Validate full name
            'email' => 'required|email|max:255|unique:users,email', // Validate email
            'phone_number' => 'required|string|max:20|unique:users,phone_number', // Validate phone number
        ]);

        // Get the selected vaccine center
        $vaccineCenter = DB::table('vaccine_centers')
            ->where('id', $request['vaccine_center_id'])
            ->select('id', 'daily_limit')
            ->first();

        // Next working schedule date is generated, off day is : Friday and Saturday,
        // depending on vaccine center limit
        $scheduledDate = $this->clculateScheduleDate($vaccineCenter);

        // Start transaction
        DB::beginTransaction();
        try {
            // Create new user registration
            User::create([
                'vaccine_center_id' => $request['vaccine_center_id'],
                'full_name' => $request['full_name'],
                'nid' => $request['user_nid'],
                'email' => $request['email'],
                'phone_number' => $request['phone_number'],
                'status' => 'Scheduled',
                'scheduled_vaccination_date' => $scheduledDate,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Registration successful!');
        } catch (\Exception $e) {
            // Rollback the transaction if an error occurs
            DB::rollBack();

            return redirect()->back()->withErrors('error', 'Registration failed. Please try again.');
        }
    }

    private function clculateScheduleDate($vaccineCenter)
    {
        // Get the next available working day starting from today
        $nextWorkingDay = $this->getNextAvailableWeekday(now());

        // Get the latest scheduled vaccination date for the vaccine center
        $latestScheduledDate = User::where('vaccine_center_id', $vaccineCenter->id)
            ->max('scheduled_vaccination_date');

        // If there's no scheduled date, set the date to the next available working day
        if (!$latestScheduledDate) {
            $latestScheduledDate = $nextWorkingDay;
        }

        // Get how many users are scheduled for the latest scheduled vaccination date
        $scheduledUsersCountForLatestDate = User::where('vaccine_center_id', $vaccineCenter->id)
            ->whereDate('scheduled_vaccination_date', $latestScheduledDate)
            ->count();

        // Calculate the scheduled date based on the daily limit
        if ($scheduledUsersCountForLatestDate < $vaccineCenter->daily_limit) {
            // If the latest scheduled date has capacity, schedule for that day
            $scheduledDate = Carbon::parse($latestScheduledDate);
        } else {
            // If the latest scheduled date has reached the limit, move to the next available working day
            $scheduledDate = $this->getNextAvailableWeekday(Carbon::parse($latestScheduledDate));
        }

        return $scheduledDate;
    }


    /**
     * Get the next available weekday for scheduling.
     *
     * This method determines the next available weekday based on the provided date.
     *
     * @param Carbon $date The date from which to calculate the next available weekday.
     * @return Carbon The next available weekday.
     */
    private function getNextAvailableWeekday($date)
    {
        $dayOfWeek = $date->dayOfWeek;

        // Check the current day of the week and determine the next available working day
        switch ($dayOfWeek) {
            case Carbon::SUNDAY:  // 0
                return $date->addDay(); // Next Monday (1)
            case Carbon::MONDAY:  // 1
                return $date->addDay(); // Next Tuesday (2)
            case Carbon::TUESDAY: // 2
                return $date->addDay(); // Next Wednesday (3)
            case Carbon::WEDNESDAY: // 3
                return $date->addDay(); // Next Thursday (4)
            case Carbon::THURSDAY: // 4
                return $date->addDay(); // Next Friday (5)
            case Carbon::FRIDAY: // 5
                return $date->addDays(2); // Skip to next Sunday (0)
            case Carbon::SATURDAY: // 6
                return $date->addDays(1); // Next Sunday (0)
            default:
                return $date; // Should not reach here
        }
    }
}
