<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Venue;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;
use Spatie\Browsershot\Browsershot;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cancel')
            ->label('Cancel Booking')
            ->color('gray')
            ->form([
                Textarea::make('cancelation_reason')
                    ->label('Cancelation Reason')
                    ->required()
                    ->placeholder('Please provide a reason for cancelation.'),
            ])
            ->requiresConfirmation()
            ->action(function (Booking $booking, array $data,): void {
                $cancelationReason = $data['cancelation_reason'];

                DB::transaction(function () use ($booking, $cancelationReason) {
                    $booking->update([
                        'status' => 'Canceled',
                        'canceled_by' => auth()->id(),
                        'canceled_at' => Carbon::now(),
                        'cancelation_reason' => $cancelationReason
                    ]);
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $booking->getKey()]));
                });
            })
            ->visible(fn ($record) =>
                $record->status != 'Completed' &&
                $record->status != 'Rejected' &&
                empty($record->canceled_by) &&
                auth()->user()->can('Manage Job Orders')
            )
            ->icon('heroicon-o-no-symbol'),

            Action::make('Generate PDF')
                ->button()
                ->color('gray')
                ->label('PDF')
                ->icon('heroicon-s-document-arrow-down')
                ->action(function (Booking $record) {
                    // Create HTML content using a template engine like Blade
                    $html = view('pdfs.venue-booking', ['booking' => $record, 'title' => 'UNIV-029'])->render();

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
                    $filePath = public_path('venue-booking-' . $record->id . '.pdf');
                    file_put_contents($filePath, $output);

                    // Return the generated PDF for download
                    return response()->download($filePath)->deleteFileAfterSend(true);
                }),

            ActionGroup::make([
                Action::make('note')
                    ->label('Note')
                    ->action(function (Booking $booking): void {
                        DB::transaction(function () use ($booking) {
                            // Update the status of the current booking to 'Approved'
                            $booking->update([
                                'noted_by' => auth()->id(),  // Assuming 'approved_by' is the field name in your database
                                'noted_at' => Carbon::now(),
                            ]);
                        });
                    })
                    ->visible(fn ($record) =>
                        $record->status === 'Pending' &&
                        $record->unit && $record->unit->unitHead &&
                        auth()->user()->id === ($record->unit)->unitHead->id &&
                        empty($record->noted_by))
                    ->icon('heroicon-s-check')
                    ->color('success'),

                Action::make('disregard')
                    ->label('Disregard')
                    ->action(function (Booking $booking): void {
                        $booking->update([
                            'status' => 'Rejected',
                            'rejected_by' => auth()->user()->id,
                            'rejected_at' => Carbon::now(),
                        ]);
                    })
                    ->visible(fn ($record) =>
                        $record->status === 'Pending' &&
                        $record->unit && $record->unit->unitHead &&
                        auth()->user()->id === ($record->unit)->unitHead->id &&
                        empty($record->noted_by) &&
                        empty($record->rejected_by))
                    ->icon('heroicon-s-x-circle')
                    ->color('danger'),
                ])->label('Approval')->icon('heroicon-m-chevron-down')->button(),

            // Actions\EditAction::make()
            //     ->color('gray')
            //     ->label('Edit Booking')
            //     ->icon('heroicon-o-pencil-square')
            //     ->visible(function ($record) {
            //         return auth()->id() === $record->person_responsible;
            //     }),

            ActionGroup::make([
                Action::make('approve')
                    ->label('Approve')
                    ->action(function (Booking $booking): void {
                        DB::transaction(function () use ($booking) {
                            // Update the status of the current booking to 'Approved'
                            $booking->update([
                                'approved_by' => auth()->id(),  // Assuming 'approved_by' is the field name in your database
                                'approved_at' => Carbon::now(),
                                'status' => 'Approved',
                            ]);

                            // Find and update conflicting bookings
                            $conflictingBookings = Booking::where('venue_id', $booking->venue_id)
                                ->where('status', 'Pending')
                                ->where(function ($query) use ($booking) {
                                    $query->whereBetween('starts_at', [$booking->starts_at, $booking->ends_at])
                                        ->orWhereBetween('ends_at', [$booking->starts_at, $booking->ends_at]);
                                })
                                ->where('id', '!=', $booking->id)
                                ->get();

                            foreach ($conflictingBookings as $conflict) {
                                $conflict->update(['status' => 'Unavailable']);
                            }
                        });
                    })
                    ->visible(fn ($record) =>
                        $record->status === 'Pending' &&
                        auth()->user()->can('Manage Venue Bookings') &&
                        !empty($record->noted_by) &&
                        empty($record->approved_by)
                        )
                    ->icon('heroicon-s-check')
                    ->color('success'),

                Actions\Action::make('Reject')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->placeholder('Please provide a reason for rejection.'),
                    ])
                    ->requiresConfirmation()
                    ->color('danger')
                    ->action(function (Booking $booking, array $data,): void {
                        $rejectionReason = $data['rejection_reason'];

                        DB::transaction(function () use ($booking, $rejectionReason) {
                            $booking->update([
                                'status' => 'Rejected',
                                'rejected_by' => auth()->id(),
                                'rejected_at' => Carbon::now(),
                                'rejection_reason' => $rejectionReason,
                            ]);
                            $this->redirect($this->getResource()::getUrl('view', ['record' => $booking->getKey()]));
                        });
                    })
                    ->visible(fn ($record) =>
                        $record->status === 'Pending' &&
                        auth()->user()->can('Manage Venue Bookings') &&
                        !empty($record->noted_by) &&
                        empty($record->approved_by) &&
                        empty($record->rejected_by)
                        )
                    ->icon('heroicon-s-x-circle'),
            ])->label('Approval')->icon('heroicon-m-chevron-down')->button(),
        ];
    }
}
