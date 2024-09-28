<?php

namespace App\Observers;

use App\Models\Booking;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class BookingObserver
{
    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        $personResponsible = User::find($booking->person_responsible);
        $unit = $booking->unit;
        $unitHead = $unit ? $unit->unitHead : null;
        $bookingUrl = route('filament.admin.resources.bookings.view', ['record' => $booking->id]);
        $appBookingUrl = route('filament.app.resources.bookings.view', ['record' => $booking->id]);

        if ($personResponsible) {
            Notification::make()
                ->title($booking->purpose)
                ->body("Your Booking has been submitted successfully. Ask for {$unitHead->full_name}'s recommendation.")
                ->success()
                ->actions([
                    Action::make('view')
                        ->url($appBookingUrl),
                ])
                ->send()
                ->sendToDatabase($personResponsible);
        }

        if ($unitHead) {
            Notification::make()
                ->title($booking->purpose)
                ->body('A new Booking has been submitted and requires recommendation.')
                ->success()
                ->actions([
                    Action::make('view')
                        ->url($appBookingUrl),
                ])
                ->sendToDatabase($unitHead);
        }
    }

    public function saving(Booking $booking)
    {
    }

    /**
     * Handle the JobOrder "updated" event.
     */
    public function updated(Booking $booking): void
    {
        $bookingUrl = route('filament.admin.resources.bookings.view', ['record' => $booking->id]);
        $appBookingUrl = route('filament.app.resources.bookings.view', ['record' => $booking->id]);
        $listBookingUrl = route('filament.app.resources.bookings.index');
        $personResponsible = User::find($booking->person_responsible);
        $unit = $booking->unit;
        $unitHead = $unit ? $unit->unitHead : null;
        $noter = User::find($booking->noted_by);
        $bookingManagers = User::permission('Manage Venue Bookings')->get();
        $bookingFinancers = User::permission('Approve Venue Bookings as Finance')->get();
        $approver = User::find($booking->approved_by);
        $financeApprover = User::find($booking->approved_by_finance);
        $venue = $booking->venue;
        $facilitator = $venue ? $venue->venueHead : null;
        $receiver = User::find($booking->received_by);
        $rejecter = User::find($booking->rejected_by);
        $canceler = User::find($booking->canceled_by);

        if ($booking->isDirty('noted_by')) {

            if ($personResponsible) {
                // Notification for the submitter that their job order has been approved
                Notification::make()
                    ->title('Booking Noted')
                    ->body("Your Booking '{$booking->purpose}' has been noted by " . ($noter ? $noter->full_name : "an unknown user") . ". Pending for VP Admin's approval.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($appBookingUrl)  // Link to the Job Order detail page
                    ])
                    ->sendToDatabase($personResponsible);
            }

            if ($noter) {
                // Notification for the approver that they have approved the job order
                Notification::make()
                    ->title('Booking Noted')
                    ->body("You have noted the booking '{$booking->purpose}' of {$personResponsible->full_name}.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($appBookingUrl)  // Link to the Job Order detail page
                    ])
                    ->send()
                    ->sendToDatabase($noter);
            }

            if ($bookingManagers) {
                // Notification for the approver that they have approved the job order
                Notification::make()
                    ->title('Job Order Recommended')
                    ->body("{$noter->full_name} have recommended the booking '{$booking->purpose}' of {$personResponsible->full_name}. Pending for your approval.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($bookingUrl)  // Link to the Job Order detail page
                    ])
                    ->sendToDatabase($bookingManagers);
            }
        }

        if ($booking->isDirty('approved_by')) {

            if ($personResponsible) {
                // Notification for the submitter that their job order has been approved
                Notification::make()
                    ->title("Booking Approved by {$approver->full_name}")
                    ->body("Your Booking '{$booking->purpose}' has been approved by " . ($approver ? $approver->full_name : "an unknown user") . ". Pending for VP Finance's approval.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($appBookingUrl)  // Link to the Job Order detail page
                    ])
                    ->sendToDatabase($personResponsible);
            }

            if ($approver) {
                // Notification for the approver that they have approved the job order
                Notification::make()
                    ->title('Booking Approved')
                    ->body("You have approved the booking '{$booking->purpose}' of {$personResponsible->full_name}. Pending for VP Finance's approval.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($bookingUrl)  // Link to the Job Order detail page
                    ])
                    ->send()
                    ->sendToDatabase($approver);
            }

            if ($bookingFinancers) {
                // Notification for the approver that they have approved the job order
                Notification::make()
                    ->title("Booking Approved by {$approver->full_name}")
                    ->body("{$approver->full_name} have approved the booking '{$booking->purpose}' of {$personResponsible->full_name}. Pending for your approval.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($appBookingUrl)  // Link to the Job Order detail page
                    ])
                    ->sendToDatabase($bookingFinancers);
            }
        }

        if ($booking->isDirty('approved_by_finance')) {

            if ($personResponsible) {
                // Notification for the submitter that their job order has been approved
                Notification::make()
                    ->title("Booking Approved by {$financeApprover->full_name}")
                    ->body("Your Booking '{$booking->purpose}' has been approved by " . ($financeApprover ? $financeApprover->full_name : "an unknown user") . ". Pending for Facilitator's Reception.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($appBookingUrl)  // Link to the Job Order detail page
                    ])
                    ->sendToDatabase($personResponsible);
            }

            if ($financeApprover) {
                // Notification for the approver that they have approved the job order
                Notification::make()
                    ->title('Booking Approved')
                    ->body("You have approved the booking '{$booking->purpose}' of {$personResponsible->full_name}. Pending for Facilitator's approval.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($appBookingUrl)  // Link to the Job Order detail page
                    ])
                    ->send()
                    ->sendToDatabase($financeApprover);
            }

            if ($facilitator) {
                // Notification for the approver that they have approved the job order
                Notification::make()
                    ->title("Booking Approved by {$financeApprover->full_name}")
                    ->body("{$financeApprover->full_name} have approved the booking '{$booking->purpose}' of {$personResponsible->full_name}. Pending for your reception.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($booking)  // Link to the Job Order detail page
                    ])
                    ->sendToDatabase($facilitator);
            }
        }

        if ($booking->isDirty('received_by')) {

            if ($personResponsible) {
                // Notification for the submitter that their job order has been approved
                Notification::make()
                    ->title("Booking Received")
                    ->body("Your Booking '{$booking->purpose}' has been received by " . ($receiver ? $receiver->full_name : "an unknown user"))
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($appBookingUrl)  // Link to the Job Order detail page
                    ])
                    ->sendToDatabase($personResponsible);
            }

            if ($receiver) {
                // Notification for the approver that they have approved the job order
                Notification::make()
                    ->title('Booking Received')
                    ->body("You have received the booking '{$booking->purpose}' of {$personResponsible->full_name}.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($appBookingUrl)  // Link to the Job Order detail page
                    ])
                    ->send()
                    ->sendToDatabase($financeApprover);
            }
        }

        if ($booking->isDirty('status') && $booking->status === 'Rejected') {
            if ($personResponsible) {
                Notification::make()
                    ->title('Booking Rejected')
                    ->body("Your Booking '{$booking->purpose}' has been rejected by " . ($rejecter ? $rejecter->full_name : "an unknown user" . "for the reason:  '{$booking->rejection_reason}'"))
                    ->danger()
                    ->actions([
                        Action::make('view')
                            ->url($appBookingUrl)  // Link to the Job Order detail page
                    ])
                    ->sendToDatabase($personResponsible);
            }

            if ($rejecter) {
                Notification::make()
                    ->title('Booking Rejected')
                    ->body("You have rejected the booking '{$booking->job_order_title}'.")
                    ->danger()
                    ->actions([
                        Action::make('view')
                            ->url($listBookingUrl)  // Link to the Job Order detail page
                    ])
                    ->send()
                    ->sendToDatabase($rejecter);
            }
        }

        if ($booking->isDirty('status') && $booking->status === 'Canceled') {
            if ($personResponsible) {
                Notification::make()
                    ->title('Booking Canceled')
                    ->body("Your Booking '{$booking->purpose}' has been canceled for the reason:  '{$booking->cancelation_reason}'")
                    ->danger()
                    ->actions([
                        Action::make('view')
                            ->url($appBookingUrl)  // Link to the Job Order detail page
                    ])
                    ->sendToDatabase($personResponsible);
            }

            if ($canceler) {
                Notification::make()
                    ->title('Booking Canceled')
                    ->body("You have canceled the booking '{$booking->purpose}'.")
                    ->danger()
                    ->actions([
                        Action::make('view')
                            ->url($listBookingUrl)  // Link to the Job Order detail page
                    ])
                    ->send()
                    ->sendToDatabase($canceler);
            }
        }

        if ($booking->isDirty('status') && $booking->status === 'Ongoing') {
            if ($personResponsible) {
                Notification::make()
                    ->title('Booking Started')
                    ->body("Your Booking '{$booking->purpose}' has started.")
                    ->danger()
                    ->actions([
                        Action::make('view')
                            ->url($appBookingUrl)  // Link to the Job Order detail page
                    ])
                    ->sendToDatabase($personResponsible);
            }
        }

        if ($booking->isDirty('status') && $booking->status === 'Ended') {
            if ($personResponsible) {
                Notification::make()
                    ->title('Booking Started')
                    ->body("Your Booking '{$booking->purpose}' has ended.")
                    ->danger()
                    ->actions([
                        Action::make('view')
                            ->url($appBookingUrl)  // Link to the Job Order detail page
                    ])
                    ->sendToDatabase($personResponsible);
            }
        }
    }

    /**
     * Handle the Booking "deleted" event.
     */
    public function deleted(Booking $jobOrder): void
    {
        //
    }

    /**
     * Handle the Booking "restored" event.
     */
    public function restored(Booking $jobOrder): void
    {
        //
    }

    /**
     * Handle the Booking "force deleted" event.
     */
    public function forceDeleted(Booking $jobOrder): void
    {
        //
    }
}
