<?php

namespace App\Filament\Widgets;

use App\Models\JobOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class JobOrderStatsOverview extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $totalJobOrders = JobOrder::count();
        $pendingJobOrders = JobOrder::where('status', 'Pending')->count();
        $completedJobOrders = JobOrder::where('status', 'Completed')->count();

        // Generate charts based on hypothetical data
        $totalJobOrdersChart = $this->generateTotalJobOrdersChart();
        $pendingJobOrdersChart = $this->generatePendingJobOrdersChart();
        $completedJobOrdersChart = $this->generateCompletedJobOrdersChart();

        return [
            Stat::make('Total Job Orders', $totalJobOrders)
                ->description('Total number of job orders')
                ->descriptionIcon('heroicon-m-archive-box')
                ->chart($totalJobOrdersChart)
                ->color('blue'),
            Stat::make('Pending Job Orders', $pendingJobOrders)
                ->description('Job orders pending action')
                ->descriptionIcon('heroicon-m-clock')
                ->chart($pendingJobOrdersChart)
                ->color('yellow'),
            Stat::make('Completed Job Orders', $completedJobOrders)
                ->description('Successfully completed job orders')
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart($completedJobOrdersChart)
                ->color('success'),
        ];
    }

    private function generateTotalJobOrdersChart(): array
    {
        // Simulating a steady increase over 7 days
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = JobOrder::whereDate('created_at', '<=', Carbon::now()->subDays($i))->count();
        }
        return $data;
    }

    private function generatePendingJobOrdersChart(): array
    {
        // Simulating pending job orders over the past 7 days
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = JobOrder::whereDate('created_at', Carbon::now()->subDays($i))
                ->where('status', 'Pending')
                ->count();
        }
        return $data;
    }

    private function generateCompletedJobOrdersChart(): array
    {
        // Simulating completed job orders over the past 7 days
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = JobOrder::whereDate('created_at', Carbon::now()->subDays($i))
                ->where('status', 'Completed')
                ->count();
        }
        return $data;
    }
}
