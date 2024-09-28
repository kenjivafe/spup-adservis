<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

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

                Action::make('reject')
                    ->label('Reject')
                    ->action(function (Booking $booking): void {

                        $booking->update([
                            'rejected_by' => auth()->id(),
                            'status' => 'Rejected'
                        ]);
                    })
                    ->visible(fn ($record) =>
                        $record->status === 'Pending' &&
                        auth()->user()->can('Manage Venue Bookings') &&
                        !empty($record->noted_by) &&
                        empty($record->approved_by) &&
                        empty($record->rejected_by)
                        )
                    ->icon('heroicon-s-x-circle')
                    ->color('danger'),
            ])->label('Approval')->icon('heroicon-m-chevron-down')->button(),

            Action::make('cancel')
                ->label('Cancel Booking')
                ->action(function (Booking $booking): void {
                    $booking->update([
                        'status' => 'Canceled',
                        'canceled_by' => auth()->id(),
                    ]);
                    // Optionally, add more logic here, like sending notifications or logging the action
                })
                ->visible(function ($record) {
                    auth()->id() === $record->person_responsible;
                })
                ->button()
                ->icon('heroicon-o-no-symbol')
                ->color('danger'),
        ];
    }
}
