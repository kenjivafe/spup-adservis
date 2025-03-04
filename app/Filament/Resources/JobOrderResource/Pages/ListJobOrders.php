<?php

namespace App\Filament\Resources\JobOrderResource\Pages;

use App\Filament\Exports\JobOrderExporter;
use App\Filament\Resources\JobOrderResource;
use App\Models\JobOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;
use Spatie\Browsershot\Browsershot;

class ListJobOrders extends ListRecords
{
    protected static string $resource = JobOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                ExportAction::make()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('Export to CSV/XLSX')
                    ->exporter(JobOrderExporter::class),

                Action::make('Export PDF')
                    ->color('gray')
                    ->label('Export to PDF')
                    ->icon('heroicon-s-arrow-down-tray')
                    ->action(function () {
                        $query = $this->getFilteredSortedTableQuery();

                        $filters = $this->getTable()->getFilters();
                        $dateRange = $filters['created_at']->getState();
                        $statuses = $filters['status']->getState();
                        $unit = $filters['unit_name']->getState();

                        $dateParts = explode(' - ', $dateRange['created_at']);

                        $dateString = '';
                        $statusString = '';
                        $unitString = '';

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

                        if (isset($unit)) {
                            $unitString = $unit['value'];
                        }

                        // Fetch job orders based on query
                        $jobOrders = $query->get();

                        // Generate HTML content using a Blade view
                        $html = view('pdfs.job-orders', [
                            'jobOrders' => $jobOrders,
                            'title' => 'Job Orders > List',
                            'date' => $dateString,
                            'status' => $statusString,
                            'unit' => $unitString
                        ])->render();

                        // DOMPDF Setup
                        $dompdf = new Dompdf();
                        $options = new Options();
                        $options->set('isHtml5ParserEnabled', true);
                        $options->set('isPhpEnabled', true); // Enable PHP functions like Carbon if needed
                        $dompdf->setOptions($options);

                        // Load the HTML content into DOMPDF
                        $dompdf->loadHtml($html);

                        // (Optional) Set paper size and orientation
                        $dompdf->setPaper('A4', 'portrait'); // Use 'landscape' if you prefer

                        // Render the PDF (first pass)
                        $dompdf->render();

                        // Output the PDF to a file or browser
                        $output = $dompdf->output();

                        // Save the PDF to a file
                        file_put_contents(public_path('job-orders.pdf'), $output);

                        // Return the PDF as a download response
                        return response()->download(public_path('job-orders.pdf'))
                            ->deleteFileAfterSend(true);
                    }),
            ])
            ->button()
            ->label('Export')
            ->icon('heroicon-o-document-arrow-down')
            ->color('gray'),

            Actions\CreateAction::make(),
            // Action::make('exportPdf')
            //     ->label('Export PDF')
            //     ->url(function () {
            //         // Merge current request parameters to maintain filters, sorting, and pagination
            //         $queryParams = array_merge(Request::query(), [
            //             'filters' => Request::get('filters', []),
            //             'sorts' => Request::get('sorts', []),
            //         ]);
            //         return route('job_orders.job-orders-pdf', $queryParams);
            //     })
            //     // ->openUrlInNewTab(true),
        ];
    }
}
