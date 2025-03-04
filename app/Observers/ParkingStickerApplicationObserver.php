<?php

namespace App\Observers;

use App\Models\ParkingStickerApplication;
use App\Models\ParkingLimit;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class ParkingStickerApplicationObserver
{
    /**
     * Handle the ParkingStickerApplication "creating" event.
     */
    public function creating(ParkingStickerApplication $parkingStickerApplication)
    {
        $this->validateParkingLimit($parkingStickerApplication);
    }

    /**
     * Handle the ParkingStickerApplication "updating" event.
     */
    public function updating(ParkingStickerApplication $parkingStickerApplication)
    {
        $this->validateParkingLimit($parkingStickerApplication);
    }

    /**
     * Validate parking limit.
     */
    protected function validateParkingLimit(ParkingStickerApplication $parkingStickerApplication)
    {
        $departmentId = $parkingStickerApplication->department_id;
        $vehicleId = $parkingStickerApplication->vehicle_id;

        if ($departmentId && $vehicleId) {
            $vehicle = $parkingStickerApplication->vehicle;
            $limit = ParkingLimit::where('department_id', $departmentId)
                ->where('vehicle_category', $vehicle->category)
                ->first();

            $currentCount = ParkingStickerApplication::where('department_id', $departmentId)
                ->whereHas('vehicle', function ($query) use ($vehicle) {
                    $query->where('category', $vehicle->category);
                })
                ->where('status', 'Active') // Only count active applications
                ->count();

            if ($currentCount >= $limit->limit) {
                throw ValidationException::withMessages([
                    'vehicle_id' => 'The limit for ' . $vehicle->category . ' vehicles in this department has been reached.'
                ]);
            }
        }
    }

    public function created(ParkingStickerApplication $parkingStickerApplication): void
    {
        $applicant = User::find($parkingStickerApplication->applicant_id);
        $applicationManagers = User::permission('Manage Sticker Applications')->get();
        $applicationUrl = route('filament.admin.resources.parking-sticker-applications.edit', ['record' => $parkingStickerApplication->id]);
        $appApplicationUrl = route('filament.app.resources.parking-sticker-applications.edit', ['record' => $parkingStickerApplication->id]);

        if ($applicant) {
            Notification::make()
                ->title($parkingStickerApplication->plate_number)
                ->body("Your Application has been submitted successfully. Pending for approval.")
                ->success()
                ->actions([
                    Action::make('view')
                        ->url(fn () => auth()->user()->hasRole('Admin') ? $applicationUrl : $appApplicationUrl)
                ])
                ->send()
                ->sendToDatabase($applicant);
        }

        if ($applicationManagers) {
            Notification::make()
                ->title($parkingStickerApplication->plate_number)
                ->body('A new Sticker Application has been submitted and requires approval.')
                ->success()
                ->actions([
                    Action::make('view')
                        ->url(fn () => auth()->user()->hasRole('Admin') ? $applicationUrl : $appApplicationUrl)
                ])
                ->sendToDatabase($applicationManagers);
        }
    }

    public function updated(ParkingStickerApplication $parkingStickerApplication): void
    {
        $applicant = User::find($parkingStickerApplication->applicant_id);
        $approver = User::find($parkingStickerApplication->approved_by);
        $rejecter = User::find($parkingStickerApplication->rejected_by);
        $revoker = User::find($parkingStickerApplication->revoked_by);
        $applicationUrl = route('filament.admin.resources.parking-sticker-applications.edit', ['record' => $parkingStickerApplication->id]);
        $appApplicationUrl = route('filament.app.resources.parking-sticker-applications.edit', ['record' => $parkingStickerApplication->id]);

        if ($parkingStickerApplication->isDirty('approved_by')) {

            if ($applicant) {
                // Notification for the submitter that their job order has been approved
                Notification::make()
                    ->title("Application Approved by {$approver->full_name}")
                    ->body("Your Application with the plate number '{$parkingStickerApplication->plate_number}' has been approved by " . ($approver ? $approver->full_name : "an unknown user") . ". Claim your sticker at the office of the VP Admin.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url(fn () => auth()->user()->hasRole('Admin') ? $applicationUrl : $appApplicationUrl)
                    ])
                    ->sendToDatabase($applicant);
            }

            if ($approver) {
                // Notification for the approver that they have approved the job order
                Notification::make()
                    ->title('Application Approved')
                    ->body("You have approved the application '{$parkingStickerApplication->plate_number}' of {$applicant->full_name}.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($applicationUrl)  // Link to the Job Order detail page
                    ])
                    ->send()
                    ->sendToDatabase($approver);
            }
        }

        if ($parkingStickerApplication->isDirty('status') && $parkingStickerApplication->status === 'Rejected') {
            if ($applicant) {
                Notification::make()
                    ->title('Application Rejected')
                    ->body("Your Booking '{$parkingStickerApplication->plate_number}' has been rejected by " . ($rejecter ? $rejecter->full_name : "an unknown user" . "for the reason:  '{$parkingStickerApplication->rejection_reason}'"))
                    ->danger()
                    ->actions([
                        Action::make('view')
                            ->url(fn () => auth()->user()->hasRole('Admin') ? $applicationUrl : $appApplicationUrl)
                    ])
                    ->sendToDatabase($applicant);
            }

            if ($rejecter) {
                Notification::make()
                    ->title('Application Rejected')
                    ->body("You have rejected the sticker application '{$parkingStickerApplication->plate_number}'.")
                    ->danger()
                    ->actions([
                        Action::make('view')
                            ->url(fn () => auth()->user()->hasRole('Admin') ? $applicationUrl : $appApplicationUrl)
                    ])
                    ->send()
                    ->sendToDatabase($rejecter);
            }
        }

        if ($parkingStickerApplication->isDirty('status') && $parkingStickerApplication->status === 'Revoked') {
            if ($applicant) {
                Notification::make()
                    ->title('Application Revoked')
                    ->body("Your Booking '{$parkingStickerApplication->plate_number}' has been revoked by " . ($revoker ? $revoker->full_name : "an unknown user" . "for the reason:  '{$parkingStickerApplication->revocation_reason}'"))
                    ->danger()
                    ->actions([
                        Action::make('view')
                            ->url(fn () => auth()->user()->hasRole('Admin') ? $applicationUrl : $appApplicationUrl)
                    ])
                    ->sendToDatabase($applicant);
            }

            if ($revoker) {
                Notification::make()
                    ->title('Application Revoked')
                    ->body("You have revoked the sticker application '{$parkingStickerApplication->plate_number}'.")
                    ->danger()
                    ->actions([
                        Action::make('view')
                            ->url(fn () => auth()->user()->hasRole('Admin') ? $applicationUrl : $appApplicationUrl)
                    ])
                    ->send()
                    ->sendToDatabase($revoker);
            }
        }

        if ($parkingStickerApplication->isDirty('status') && $parkingStickerApplication->status === 'Expired') {
            if ($applicant) {
                Notification::make()
                    ->title('Sticker Expired')
                    ->body("Your Sticker with the plate number '{$parkingStickerApplication->plate_number}' has expired.")
                    ->danger()
                    ->actions([
                        Action::make('view')
                            ->url(fn () => auth()->user()->hasRole('Admin') ? $applicationUrl : $appApplicationUrl)
                    ])
                    ->sendToDatabase($applicant);
            }
        }
    }
}
