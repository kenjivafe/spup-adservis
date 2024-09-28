<?php

namespace App\Filament\Widgets;

use App\Models\ParkingLimit;
use App\Models\ParkingStickerApplication;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StickerApplicationStatsOverview extends BaseWidget
{
    protected static ?int $sort = 8;

    protected function getStats(): array
    {
        // Total Sticker Applications
        $totalApplications = ParkingStickerApplication::count();
        $totalApplicationsChart = $this->getApplicationsChartData();

        // Approved Sticker Applications
        $approvedApplications = ParkingStickerApplication::where('status', 'Active')->count();
        $approvedApplicationsChart = $this->getApprovedApplicationsChartData();

        // Available Parking Slots
        $availableSlots = $this->calculateAvailableSlots();
        $availableSlotsChart = $this->getAvailableSlotsChartData();

        return [
            Stat::make('Total Sticker Applications', $totalApplications)
                ->description('Total number of sticker applications submitted')
                ->descriptionIcon('heroicon-s-document')
                ->chart($totalApplicationsChart)
                ->color('blue'),
            Stat::make('Approved Sticker Applications', $approvedApplications)
                ->description('Total number of approved applications')
                ->descriptionIcon('heroicon-s-check-circle')
                ->chart($approvedApplicationsChart)
                ->color('primary'),
            Stat::make('Available Parking Slots', $availableSlots)
                ->description('Remaining parking slots available')
                ->descriptionIcon('heroicon-s-archive-box')
                ->chart($availableSlotsChart)
                ->color('yellow'),
        ];
    }

    private function getApplicationsChartData()
    {
        $data = ParkingStickerApplication::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        return $data->pluck('count')->toArray();
    }

    private function getApprovedApplicationsChartData()
    {
        $data = ParkingStickerApplication::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('status', 'Active')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        return $data->pluck('count')->toArray();
    }

    private function calculateAvailableSlots()
    {
        $parkingLimits = ParkingLimit::all();
        $availableSlots = $parkingLimits->map(function ($limit) {
            $currentCount = ParkingStickerApplication::where('department_id', $limit->department_id)
                            ->whereHas('vehicle', function ($query) use ($limit) {
                                $query->where('category', $limit->vehicle_category);
                            })
                            ->where('status', 'Active')
                            ->count();
            return max(0, $limit->limit - $currentCount);
        })->sum();

        return $availableSlots;
    }

    private function getAvailableSlotsChartData()
    {
        // Here you would calculate the available slots for each day over the last 7 days
        // This is a simplified example assuming you can calculate it daily
        $dates = collect(range(0, 6))->map(function ($daysAgo) {
            return Carbon::now()->subDays($daysAgo)->format('Y-m-d');
        });

        $data = $dates->map(function ($date) {
            $parkingLimits = ParkingLimit::all();
            $availableSlots = $parkingLimits->map(function ($limit) use ($date) {
                $currentCount = ParkingStickerApplication::where('department_id', $limit->department_id)
                                ->whereHas('vehicle', function ($query) use ($limit) {
                                    $query->where('category', $limit->vehicle_category);
                                })
                                ->where('status', 'Active')
                                ->whereDate('created_at', '<=', $date)
                                ->count();
                return max(0, $limit->limit - $currentCount);
            })->sum();
            return $availableSlots;
        });

        return $data->reverse()->toArray();  // Ensure the data is in chronological order
    }
}
