<?php

namespace App\Filament\Widgets;

use App\Models\JobOrder;
use Filament\Widgets\ChartWidget;

class JobOrderAdminChart extends ChartWidget
{
    protected static ?string $heading = 'Job Orders Chart';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '230px';

    protected function getData(): array
    {
        $jobOrders = JobOrder::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Job Orders',
                    'data' => $jobOrders->pluck('count'),
                ],
            ],
            'labels' => $jobOrders->pluck('date')->map(function ($date) {
                return \Carbon\Carbon::parse($date)->format('M d, Y');
            }),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
