<?php

namespace App\Filament\Resources\JobOrderResource\Pages;

use App\Filament\Resources\JobOrderResource;
use App\Models\JobOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\DB;
use Spatie\Browsershot\Browsershot;

class ViewJobOrder extends ViewRecord
{
    protected static string $resource = JobOrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Generate PDF')
                ->button()
                ->label('PDF')
                ->color('gray')
                ->icon('heroicon-s-document-arrow-down')
                ->action(function (JobOrder $record) {
                    // Create HTML content using a template engine like Blade
                    $html = view('pdfs.job-order', ['jobOrder' => $record, 'title' => 'UNIV-025'])->render();

                    // Generate PDF
                    // Instantiate DOMPDF
                    $dompdf = new Dompdf();

                    // Set DOMPDF options if needed (for example, for custom margins, etc.)
                    $options = new Options();
                    $options->set('isHtml5ParserEnabled', true); // Enable HTML5 parsing
                    $options->set('isPhpEnabled', true); // Enable PHP functions like include()
                    $dompdf->setOptions($options);

                    // Load HTML content
                    $dompdf->loadHtml($html);

                    // (Optional) Set paper size and orientation (A4, Portrait/Landscape)
                    $dompdf->setPaper('A4', 'landscape');

                    // Render PDF (first pass to parse HTML and CSS)
                    $dompdf->render();

                    // Save the generated PDF to a file
                    $output = $dompdf->output();
                    $filePath = public_path('job-order-' . $record->id . '.pdf');
                    file_put_contents($filePath, $output);

                    // Return the generated PDF for download
                    return response()->download($filePath)->deleteFileAfterSend(true);
                }),

            Actions\Action::make('Cancel')
                ->label('Cancel Job Order')
                ->color('danger')
                ->form([
                    Textarea::make('cancelation_reason')
                        ->label('Cancelation Reason')
                        ->required()
                        ->placeholder('Please provide a reason for cancelation.'),
                ])
                ->requiresConfirmation()
                ->action(function (JobOrder $jobOrder, array $data,): void {
                    $cancelationReason = $data['cancelation_reason'];

                    DB::transaction(function () use ($jobOrder, $cancelationReason) {
                        $jobOrder->update([
                            'status' => 'Canceled',
                            'canceled_by' => auth()->id(),
                            'canceled_at' => Carbon::now(),
                            'cancelation_reason' => $cancelationReason
                        ]);
                        $this->redirect($this->getResource()::getUrl('view', ['record' => $jobOrder->getKey()]));
                    });
                })
                ->visible(fn ($record) =>
                    $record->status != 'Completed' &&
                    $record->status != 'Rejected' &&
                    empty($record->canceled_by) &&
                    auth()->user()->can('Manage Job Orders')
                )
                ->icon('heroicon-o-no-symbol'),

            Actions\ActionGroup::make([
                Actions\Action::make('Approve')
                    ->color('primary')
                    ->action(function (JobOrder $jobOrder): void {
                        DB::transaction(function () use ($jobOrder) {
                            $jobOrder->update([
                                'status' => 'Assigned',
                                'date_begun' => now('Asia/Manila')->format('Y-m-d H:i'),
                                'approved_by' => auth()->id(),  // Assuming 'approved_by' is the field name in your database
                                'approved_at' => Carbon::now(),
                            ]);

                            $jobOrder->save();
                            $this->redirect($this->getResource()::getUrl('view', ['record' => $jobOrder->getKey()]));
                        });
                    })
                    ->visible(fn ($record) =>
                        optional($record)->status === 'Pending' &&
                        !empty($record->recommended_by) &&
                        auth()->user()->can('Manage Job Orders')
                    )
                    ->icon('heroicon-s-check'),

                Actions\Action::make('Reject')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->placeholder('Please provide a reason for rejection.'),
                    ])
                    ->requiresConfirmation()
                    ->color('danger')
                    ->action(function (JobOrder $jobOrder, array $data,): void {
                        $rejectionReason = $data['rejection_reason'];

                        DB::transaction(function () use ($jobOrder, $rejectionReason) {
                            $jobOrder->update([
                                'status' => 'Rejected',
                                'rejected_by' => auth()->id(),
                                'rejected_at' => Carbon::now(),
                                'rejection_reason' => $rejectionReason,
                            ]);
                            $this->redirect($this->getResource()::getUrl('view', ['record' => $jobOrder->getKey()]));
                        });
                    })
                    ->visible(fn ($record) =>
                        optional($record)->status === 'Pending' &&
                        !empty($record->recommended_by) &&
                        empty($record->rejected_by) &&
                        auth()->user()->can('Manage Job Orders')
                    )
                    ->icon('heroicon-s-x-circle'),
                ])
                ->label('Approval')->icon('heroicon-o-chevron-down')->button()->color('yellow'),

            Actions\Action::make('Confirm')
                ->color('primary')
                ->button()
                ->action(function (JobOrder $jobOrder): void {
                    DB::transaction(function () use ($jobOrder) {
                        $jobOrder->update([
                            'confirmed_by' => auth()->id(),  // Assuming 'approved_by' is the field name in your database
                            'confirmed_at' => Carbon::now(),
                        ]);
                        $this->redirect($this->getResource()::getUrl('view', ['record' => $jobOrder->getKey()]));
                    });
                })
                ->visible(fn ($record) =>
                    $record->status === 'Completed' &&
                    empty($record->confirmed_by) &&
                    !empty($record->checked_by) &&
                    auth()->user()->id == $record->requested_by
                )
                ->icon('heroicon-s-check-badge'),
        ];
    }
}
