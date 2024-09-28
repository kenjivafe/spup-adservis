<?php

namespace App\Filament\App\Resources\BookingResource\Pages;

use App\Filament\App\Resources\BookingResource;
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

            Actions\ActionGroup::make([
                Actions\Action::make('Approve')
                    ->color('primary')
                    ->action(function (Booking $booking): void {
                        DB::transaction(function () use ($booking) {
                            $booking->update([
                                'status' => 'Approved',
                                'approved_by_finance' => auth()->id(),  // Assuming 'approved_by' is the field name in your database
                            ]);

                            $booking->save();
                            $this->redirect($this->getResource()::getUrl('view', ['record' => $booking->getKey()]));
                        });
                    })
                    ->visible(fn ($record) =>
                        optional($record)->status === 'Pending' &&
                        !empty($record->approved_by) &&
                        is_null($record->approved_by_finance) &&
                        auth()->user()->can('Approve Venue Bookings as Finance')
                    )
                    ->icon('heroicon-s-check'),

                Actions\Action::make('Reject')
                    ->color('danger')
                    ->action(function (Booking $booking): void {
                        DB::transaction(function () use ($booking) {
                            $booking->update([
                                'status' => 'Rejected',
                                'rejected_by' => auth()->id(),
                            ]);
                            $this->redirect($this->getResource()::getUrl('view', ['record' => $booking->getKey()]));
                        });
                    })
                    ->visible(fn ($record) =>
                        optional($record)->status === 'Pending' &&
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
                    ->action(function (Booking $booking): void {
                        DB::transaction(function () use ($booking) {
                            $booking->update([
                                'status' => 'Confirmed',
                                'received_by' => auth()->id(),  // Assuming 'approved_by' is the field name in your database
                            ]);

                            $booking->save();
                            $this->redirect($this->getResource()::getUrl('view', ['record' => $booking->getKey()]));
                        });
                    })
                    ->visible(fn ($record) =>
                        ($record)->status === 'Approved' &&
                        !empty($record->approved_by) &&
                        !empty($record->approved_by_finance) &&
                        is_null($record->received_by) &&
                        auth()->user()->can('Be In-charge of Venues')
                    )
                    ->icon('heroicon-s-check'),

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
