<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class BookingStatsOverview extends BaseWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $pendingBookings = Booking::where('status', 'Pending')->count();
        $upcomingApprovedEvents = Booking::whereIn('status', ['Approved', 'Confirmed'])
                                    ->where('starts_at', '>', now())->count();
        $successfulEvents = Booking::whereIn('status', ['Ongoing', 'Ended'])->count();

        // Generate charts based on hypothetical data
        $pendingBookingsChart = $this->generatePendingBookingsChart();
        $upcomingApprovedEventsChart = $this->generateUpcomingApprovedEventsChart();
        $successfulEventsChart = $this->generateSuccessfulEventsChart();

        return [
            Stat::make('Pending Bookings', $pendingBookings)
                ->description('Bookings pending action')
                ->descriptionIcon('heroicon-m-clock')
                ->chart($pendingBookingsChart)
                ->color('yellow'),
            Stat::make('Upcoming Approved Events', $upcomingApprovedEvents)
                ->description('Approved and confirmed bookings')
                ->descriptionIcon('heroicon-s-calendar-days')
                ->chart($upcomingApprovedEventsChart)
                ->color('primary'),
            Stat::make('Successful Events', $successfulEvents)
                ->description('Ongoing and ended bookings')
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart($successfulEventsChart)
                ->color('blue'),
        ];
    }

    private function generatePendingBookingsChart(): array
    {
        // Simulating pending bookings over the past 7 days
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = Booking::whereDate('created_at', Carbon::now()->subDays($i))
                ->where('status', 'Pending')
                ->count();
        }
        return $data;
    }

    private function generateUpcomingApprovedEventsChart(): array
    {
        // Simulating upcoming approved events over the past 7 days
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = Booking::whereIn('status', ['Approved', 'Confirmed'])
                ->whereDate('starts_at', '>', Carbon::now()->subDays($i))
                ->count();
        }
        return $data;
    }

    private function generateSuccessfulEventsChart(): array
    {
        // Simulating successful events over the past 7 days
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = Booking::whereIn('status', ['Ongoing', 'Ended'])
                ->whereDate('created_at', Carbon::now()->subDays($i))
                ->count();
        }
        return $data;
    }
}
