<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('note')
                    ->label('Note')
                    ->action(function (Booking $booking): void {
                        DB::transaction(function () use ($booking) {
                            // Update the status of the current booking to 'Approved'
                            $booking->update([
                                'noted_by' => auth()->id(),  // Assuming 'approved_by' is the field name in your database
                            ]);
                        });
                    })
                    ->visible(fn ($record) =>
                        $record->status === 'Pending' &&
                        auth()->user()->id === ($record->unit)->unitHead->id &&
                        empty($record->noted_by))
                    ->icon('heroicon-s-check')
                    ->color('success'),

                Action::make('disregard')
                    ->label('Disregard')
                    ->action(function (Booking $booking): void {
                        $booking->update([
                            'status' => 'Rejected',
                            'rejected_by' => auth()->user()->id
                        ]);
                    })
                    ->visible(fn ($record) =>
                        $record->status === 'Pending' &&
                        auth()->user()->id === ($record->unit)->unitHead->id &&
                        empty($record->noted_by) &&
                        empty($record->rejected_by))
                    ->icon('heroicon-s-x-circle')
                    ->color('danger'),
                ])->label('Approval')->icon('heroicon-m-chevron-down')->button(),
            ActionGroup::make([
                Action::make('approve')
                    ->label('Approve')
                    ->action(function (Booking $booking): void {
                        DB::transaction(function () use ($booking) {
                            // Update the status of the current booking to 'Approved'
                            $booking->update([
                                'approved_by' => auth()->id(),  // Assuming 'approved_by' is the field name in your database
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


            Actions\EditAction::make()
                ->label('Edit Booking')
                ->icon('heroicon-o-pencil-square')
                ->visible(function ($record) {
                    return auth()->id() === $record->person_responsible;
                }),

            Actions\Action::make('cancel')
                ->label('Cancel Job Order')
                ->color('danger')
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
        ];
    }
}
