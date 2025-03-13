<?php

namespace App\Filament\App\Resources\BookingResource\Pages;

use App\Filament\App\Resources\BookingResource;
use App\Models\Booking;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Generate PDF')
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

            Actions\ActionGroup::make([
                Actions\Action::make('note')
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

                Actions\Action::make('disregard')
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

            Actions\ActionGroup::make([
                Actions\Action::make('Approve')
                    ->color('primary')
                    ->action(function (Booking $booking): void {
                        DB::transaction(function () use ($booking) {
                            $booking->update([
                                'status' => 'Approved',
                                'approved_by_finance' => auth()->id(),  // Assuming 'approved_by' is the field name in your database
                                'approved_by_finance_at' => Carbon::now(),
                            ]);

                            $booking->save();
                            $this->redirect($this->getResource()::getUrl('view', ['record' => $booking->getKey()]));
                        });
                    })
                    ->visible(fn ($record) =>
                        // optional($record)->status === 'Pending' &&
                        !empty($record->approved_by) &&
                        is_null($record->approved_by_finance) &&
                        auth()->user()->can('Approve Venue Bookings as Finance')
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
                        // optional($record)->status === 'Pending' &&
                        is_null($record->approved_by_finance) &&
                        !is_null($record->approved_by) &&
                        is_null($record->rejected_by) &&
                        auth()->user()->can('Approve Venue Bookings as Finance')
                    )
                    ->icon('heroicon-o-no-symbol'),
                ])
                ->label('Approval')->icon('heroicon-m-chevron-down')->button()->color('yellow'),

                Actions\Action::make('Receive')
                ->color('blue')
                ->form([
                    DateTimePicker::make('actual_started_at')
                        ->seconds(false)
                        ->required(),
                    DateTimePicker::make('actual_ended_at')
                        ->seconds(false)
                        ->required()
                ])
                ->action(function (Booking $booking, array $data,): void {
                    $actualStartedAt = $data['actual_started_at'];
                    $actualEndedAt = $data['actual_ended_at'];

                    DB::transaction(function () use ($booking, $actualStartedAt, $actualEndedAt) {
                        $booking->update([
                            'status' => 'Confirmed',
                            'received_by' => auth()->id(),  // Assuming 'approved_by' is the field name in your database
                            'received_at' => Carbon::now(),
                            'actual_started_at' => $actualStartedAt,
                            'actual_ended_at' => $actualEndedAt,
                        ]);

                        $booking->save();
                        $this->redirect($this->getResource()::getUrl('view', ['record' => $booking->getKey()]));
                    });
                })
                ->visible(fn ($record) =>
                    ($record)->status === 'Approved' &&
                    !empty($record->approved_by) &&
                    // !empty($record->approved_by_finance) &&
                    is_null($record->received_by) &&
                    auth()->user()->can('Be In-charge of Venues')
                )
                ->icon('heroicon-s-check'),

            Actions\EditAction::make()
                ->label('Edit Booking')
                ->icon('heroicon-o-pencil-square')
                ->visible(function ($record) {
                    return auth()->id() === $record->person_responsible && empty($record->noted_by) ;
                }),

            Actions\Action::make('Cancel')
                ->label('Cancel Job Order')
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
                    auth()->user()->id == $record->person_responsible
                )
                ->icon('heroicon-o-no-symbol'),
        ];
    }
}
