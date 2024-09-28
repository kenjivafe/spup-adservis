<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class BookingAdminLineChart extends ChartWidget
{
    protected static ?int $sort = 7;

    protected static ?string $heading = 'Booking Chart';

    protected function getData(): array
{
    // Define the statuses we are interested in
    $successfulStatuses = ['Approved', 'Confirmed', 'Ongoing', 'Ended'];
    $pendingStatuses = ['Pending'];
    $failedStatuses = ['Canceled', 'Rejected', 'Unavailable'];

    // Get the bookings grouped by date and status
    $bookings = Booking::selectRaw('DATE(created_at) as date, status, COUNT(*) as count')
        ->groupBy('date', 'status')
        ->orderBy('date', 'ASC')
        ->get();

    // Initialize the labels and datasets
    $labels = [];
    $successfulData = [];
    $pendingData = [];
    $failedData = [];

    // Fill the data arrays
    foreach ($bookings as $booking) {
        $date = Carbon::parse($booking->date)->format('M d, Y');

        // Add the date to labels if it's not already there
        if (!in_array($date, $labels)) {
            $labels[] = $date;
        }

        // Initialize the data arrays for the current date if not set
        if (!isset($successfulData[$date])) {
            $successfulData[$date] = 0;
        }
        if (!isset($pendingData[$date])) {
            $pendingData[$date] = 0;
        }
        if (!isset($failedData[$date])) {
            $failedData[$date] = 0;
        }

        // Increment the counts based on the booking status
        if (in_array($booking->status, $successfulStatuses)) {
            $successfulData[$date] += $booking->count;
        } elseif (in_array($booking->status, $pendingStatuses)) {
            $pendingData[$date] += $booking->count;
        } elseif (in_array($booking->status, $failedStatuses)) {
            $failedData[$date] += $booking->count;
        }
    }

    // Ensure all labels have corresponding data, filling in zeros where necessary
    foreach ($labels as $label) {
        if (!isset($successfulData[$label])) {
            $successfulData[$label] = 0;
        }
        if (!isset($pendingData[$label])) {
            $pendingData[$label] = 0;
        }
        if (!isset($failedData[$label])) {
            $failedData[$label] = 0;
        }
    }

    // Prepare the datasets for the chart
    $datasets = [
        [
            'label' => 'Successful',
            'data' => array_values($successfulData),
            'borderColor' => 'rgba(75, 250, 120, 1)',
            'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
            'fill' => false,
        ],
        [
            'label' => 'Pending',
            'data' => array_values($pendingData),
            'borderColor' => 'rgba(255, 206, 86, 1)',
            'backgroundColor' => 'rgba(255, 206, 86, 0.5)',
            'fill' => false,
        ],
        [
            'label' => 'Failed',
            'data' => array_values($failedData),
            'borderColor' => 'rgba(255, 99, 132, 1)',
            'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
            'fill' => false,
        ],
    ];

    return [
        'datasets' => $datasets,
        'labels' => $labels,
    ];
}


    protected function getType(): string
    {
        return 'line';
    }
}
