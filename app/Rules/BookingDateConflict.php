<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Booking;
use Carbon\Carbon;

class BookingDateConflict implements Rule
{
    private $startsAt;
    private $endsAt;
    private $venueId;
    private $bookingId;
    private $conflictMessage;


    public function __construct($startsAt, $endsAt, $venueId, $bookingId = null)
    {
        $this->startsAt = Carbon::parse($startsAt);
        $this->endsAt = Carbon::parse($endsAt);
        $this->venueId = $venueId;
        $this->bookingId = $bookingId;
    }

    public function passes($attribute, $value)
    {
        // Check for overlaps
        $conflictingBooking = Booking::where('id', '!=', $this->bookingId)
            ->where('venue_id', $this->venueId)
            ->whereIn('status', ['Approved', 'Confirmed'])
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('starts_at', '<', $this->endsAt)
                      ->where('ends_at', '>', $this->startsAt);
                });
            })
            ->first();
            if ($conflictingBooking) {
                $this->conflictMessage = 'There is a conflict in booking from '
                    . $conflictingBooking->starts_at->format('F d, H:i')
                    . ' to ' . $conflictingBooking->ends_at->format('F d, H:i') . '.';
                return false;
            }

            return true;
        }

        public function message()
        {
            return $this->conflictMessage ?: 'There is a conflict booking on this venue with these date/time.';
    }
}
