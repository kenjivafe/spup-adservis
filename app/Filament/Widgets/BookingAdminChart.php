<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Venue;
use Filament\Widgets\ChartWidget;

class BookingAdminChart extends ChartWidget
{
    protected static ?int $sort = 6;

    protected static ?string $heading = 'Bookings by Venue';

    protected function getData(): array
    {
        // Define the statuses we are interested in
        $statuses = ['Pending', 'Approved', 'Confirmed', 'Ongoing', 'Ended'];

        // Get all venues
        $venues = Venue::all();

        // Define a set of colors for the bars
        $colors = [
            'rgba(255, 99, 132, 0.8)', // red
            'rgba(54, 162, 235, 0.8)', // blue
            'rgba(255, 206, 86, 0.8)', // yellow
            'rgba(75, 192, 192, 0.8)', // green
            'rgba(153, 102, 255, 0.8)', // purple
            'rgba(255, 159, 64, 0.8)', // orange
            // More colors as needed
        ];

        // Generate data for the chart
        $labels = $venues->pluck('name')->toArray();
        $datasets = $venues->map(function ($venue, $index) use ($statuses, $colors) {
            // Get count of bookings matching the specified statuses for this venue
            $count = Booking::where('venue_id', $venue->id)
                ->whereIn('status', $statuses)
                ->count();

            // Return dataset for each venue
            return [
                'label' => $venue->name,
                'data' => [$count], // Wrap count in an array for compatibility with chart.js
                'backgroundColor' => $colors[$index % count($colors)], // Cycle through colors if there are more venues than colors
                'borderColor' => $colors[$index % count($colors)],
                'borderWidth' => 1
            ];
        })->all();

        return [
            'datasets' => $datasets,
            'labels' => ['Bookings'] // Generic label for all bars, since each dataset is for a different venue
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [ // Using 'y' instead of 'yAxes' for the newer versions of Chart.js
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1, // Ensure ticks increment by 1
                        'precision' => 0 // Avoids fractional parts in the chart ticks
                    ]
                ]
            ],
        ];
    }
}
