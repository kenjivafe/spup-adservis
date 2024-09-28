<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class StatsAdminOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $totalUsers = User::query()->count();
        $newUsers = User::doesntHave('roles')->count(); // Users without roles

        // Assuming you are using database sessions
        $activeUsers = DB::table('sessions')
            ->where('user_id', '!=', null)
            ->where('last_activity', '>=', now()->subMinutes(config('session.lifetime'))->getTimestamp())
            ->distinct('user_id') // Ensure each user is counted only once
            ->count('user_id');   // Count distinct user IDs

        // Generate charts based on hypothetical data
        $totalUsersChart = $this->generateTotalUsersChart();
        $newUsersChart = $this->generateNewUsersChart();
        $activeUsersChart = $this->generateActiveUsersChart();

        return [
            Stat::make('Users', $totalUsers)
                ->description('All users registered')
                ->descriptionIcon('heroicon-s-user-group')
                ->chart($totalUsersChart)
                ->color('blue'),
            Stat::make('New Users', $newUsers)
                ->description('Users without permissions yet')
                ->descriptionIcon('heroicon-s-envelope')
                ->chart($newUsersChart)
                ->color('warning'),
            Stat::make('Active Users', $activeUsers)
                ->description('Currently logged in users')
                ->descriptionIcon('heroicon-s-user-circle')
                ->chart($activeUsersChart)
                ->color('primary'),
        ];
    }

    private function generateTotalUsersChart(): array
    {
        // Simulating a steady increase over 7 days
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = User::whereDate('created_at', '<=', Carbon::now()->subDays($i))->count();
        }
        return $data;
    }

    private function generateNewUsersChart(): array
    {
        // Simulating new users over the past 7 days
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = User::whereDate('created_at', Carbon::now()->subDays($i))
                ->doesntHave('roles')
                ->count();
        }
        return $data;
    }

    private function generateActiveUsersChart(): array
    {
        // Simulating active users over the past 7 hours
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = DB::table('sessions')
                ->where('user_id', '!=', null)
                ->where('last_activity', '>=', Carbon::now()->subHours($i)->subMinutes(config('session.lifetime'))->getTimestamp())
                ->distinct('user_id') // Ensure each user is counted only once
                ->count('user_id');   // Count distinct user IDs
        }
        return $data;
    }
}
