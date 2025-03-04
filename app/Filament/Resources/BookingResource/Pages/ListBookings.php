<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Venue;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Spatie\Browsershot\Browsershot;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                // ExportAction::make()
                //     ->icon('heroicon-o-arrow-down-tray')
                //     ->label('Export to CSV/XLSX')
                //     ->exporter(JobOrderExporter::class),

                Action::make('Export PDF')
                    ->color('gray')
                    ->label('Export to PDF')
                    ->icon('heroicon-s-arrow-down-tray')
                    ->action(function () {
                        $query = $this->getFilteredSortedTableQuery();

                        $filters = $this->getTable()->getFilters();
                        $dateRange = $filters['created_at']->getState();
                        $statuses = $filters['status']->getState();
                        $venue = $filters['venue_id']->getState();

                        $dateParts = explode(' - ', $dateRange['created_at']);

                        $dateString = '';
                        $statusString = '';
                        $venueString = '';

                        if (isset($dateRange['created_at'])) {
                            $dateParts = explode(' - ', $dateRange['created_at']);

                            if (count($dateParts) == 2) {
                                $startDate = Carbon::createFromFormat('d/m/Y', $dateParts[0]);
                                $endDate = Carbon::createFromFormat('d/m/Y', $dateParts[1]);

                                $dateString = "from " . $startDate->format('F j, Y') . " to " . $endDate->format('F j, Y');
                            }
                        }

                        if (isset($statuses['values']) && count($statuses['values']) > 1) {
                            $statusString = implode(', ', array_slice($statuses['values'], 0, -1)) . ' and ' . end($statuses['values']);
                        } else if (isset($statuses['values']) && count($statuses['values']) == 1) {
                            $statusString = $statuses['values'][0];
                        } else {
                            $statusString = 'All';
                        }

                        if (isset($venue)) {
                            $venueId = $venue['value'];
                            $venue = Venue::find($venueId);

                            if ($venue) {
                                $venueString = $venue->name;
                            } else {
                                $venueString = 'Venue';
                            }
                        }

                        $bookings = $query->get();

                        // Generate HTML content using a Blade view
                        $html = view('pdfs.venue-bookings', [
                            'bookings' => $bookings,
                            'title' => 'Venue Bookings > List',
                            'date' => $dateString,
                            'status' => $statusString,
                            'venue' => $venueString
                        ])->render();

                        // Set up DOMPDF options
                        $options = new Options();
                        $options->set('isHtml5ParserEnabled', true);
                        $options->set('isPhpEnabled', true); // If you want to use PHP functions like Carbon in your view

                        $dompdf = new Dompdf($options);

                        // Load HTML content into DOMPDF
                        $dompdf->loadHtml($html);

                        // (Optional) Set paper size and orientation (A4, portrait by default)
                        $dompdf->setPaper('A4', 'portrait'); // Change to 'landscape' if needed

                        // Render the PDF (first pass)
                        $dompdf->render();

                        // Output the PDF to a file
                        $output = $dompdf->output();
                        $filePath = public_path('venue-bookings.pdf');
                        file_put_contents($filePath, $output);

                        // Return the PDF as a download response
                        return response()->download($filePath)->deleteFileAfterSend(true);
                    }),
            ])
            ->button()
            ->label('Export')
            ->icon('heroicon-o-document-arrow-down')
            ->color('gray'),

            Actions\CreateAction::make(),
        ];
    }

    public function getTitle(): string | Htmlable
    {
        return 'All Bookings'; // Fallback title if record not loaded
    }
}
