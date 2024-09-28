<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdateBookingStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:bookings-update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the status of bookings based on the current date and time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // Update bookings to 'Ongoing'
        $ongoingBookings = Booking::where('status', 'Confirmed')
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>', $now)
            ->update(['status' => 'Ongoing']);

        // Update bookings to 'Ended'
        $endedBookings = Booking::where('status', 'Ongoing')
            ->where('ends_at', '<=', $now)
            ->update(['status' => 'Ended']);

        $this->info("Updated $ongoingBookings bookings to 'Ongoing'.");
        $this->info("Updated $endedBookings bookings to 'Ended'.");
    }
}
