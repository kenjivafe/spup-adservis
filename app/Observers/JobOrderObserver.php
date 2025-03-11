<?php

namespace App\Observers;

use App\Models\JobOrder;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class JobOrderObserver
{
    /**
     * Handle the JobOrder "created" event.
     */
    public function created(JobOrder $jobOrder): void
    {
        $assignedUser = User::find($jobOrder->assigned_to);
        $requester = User::find($jobOrder->requested_by);
        $usersWithPermission = User::permission('Recommend Job Orders')->get();
        $jobOrderUrl = route('filament.admin.resources.job-orders.view', ['record' => $jobOrder->id]);
        $appJobOrderUrl = route('filament.app.resources.job-orders.view', ['record' => $jobOrder->id]);

        // Notification for the submitter
        if ($requester) {
            Notification::make()
                ->title($jobOrder->job_order_title)
                ->body('Your Job Order has been submitted successfully. Wait for recommendation.')
                ->success()
                ->actions([
                    Action::make('view')
                        ->url(fn () => auth()->user()->hasRole('Admin') ? $jobOrderUrl : $appJobOrderUrl)
                ])
                ->send()
                ->sendToDatabase($requester);
        }

        // Notify all users with the permission to manage job orders
        foreach ($usersWithPermission as $user) {
            Notification::make()
                ->title($jobOrder->job_order_title)
                ->body('A new Job Order has been submitted and requires assignment and recommendation.')
                ->success()
                ->actions([
                    Action::make('view')
                        ->url(fn () => auth()->user()->hasRole('Admin') ? $jobOrderUrl : $appJobOrderUrl)
                ])
                ->sendToDatabase($user);
        }
    }

    public function saving(JobOrder $jobOrder)
    {
        // Check if assigned_to ID is present and not null
        // if ($jobOrder->assigned_to) {
        //     // Check if assigned_to has been modified (isDirty)
        //     if ($jobOrder->isDirty('assigned_to') && !$jobOrder->date_begun) {
        //         $jobOrder->status = 'Assigned';
        //         $jobOrder->date_begun = now('Asia/Manila')->format('Y-m-d H:i');
        //     }
        // }
    }

    /**
     * Handle the JobOrder "updated" event.
     */
    public function updated(JobOrder $jobOrder): void
    {
        $jobOrderUrl = route('filament.admin.resources.job-orders.view', ['record' => $jobOrder->id]);
        $appJobOrderUrl = route('filament.app.resources.job-orders.view', ['record' => $jobOrder->id]);
        $requester = User::find($jobOrder->requested_by);
        $recommender = User::find($jobOrder->recommended_by);
        $approver = User::find($jobOrder->approved_by);
        $assignedUser = User::find($jobOrder->assigned_to);
        $usersWithPermission = User::permission('Manage Job Orders')->get();
        $rejecter = User::find($jobOrder->rejected_by);
        $canceler = User::find($jobOrder->canceled_by);

        if ($jobOrder->isDirty('recommended_by')) {

            if ($requester) {
                // Notification for the submitter that their job order has been approved
                Notification::make()
                    ->title('Job Order Recommended')
                    ->body("Your Job Order '{$jobOrder->job_order_title}' has been recommended by " . ($recommender ? $recommender->full_name : "an unknown user"))
                    ->success()
                ->actions([
                    Action::make('view')
                        ->url(fn () => auth()->user()->hasRole('Admin') ? $jobOrderUrl : $appJobOrderUrl)
                ])
                    ->sendToDatabase($requester);
            }

            if ($recommender) {
                // Notification for the approver that they have approved the job order
                Notification::make()
                    ->title('Job Order Recommended')
                    ->body("You have recommended the job order '{$jobOrder->job_order_title}' of {$requester->full_name}.")
                    ->success()
                ->actions([
                    Action::make('view')
                        ->url(fn () => auth()->user()->hasRole('Admin') ? $jobOrderUrl : $appJobOrderUrl)
                ])
                    ->send()
                    ->sendToDatabase($recommender);
            }

            if ($usersWithPermission) {
                // Notification for the approver that they have approved the job order
                Notification::make()
                    ->title('Job Order Recommended')
                    ->body("{$recommender->full_name} have recommended the job order '{$jobOrder->job_order_title}' of {$requester->full_name}. Pending for your approval.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($jobOrderUrl)  // Link to the Job Order detail page
                    ])
                    ->sendToDatabase($usersWithPermission);
            }

            if ($assignedUser) {
                Notification::make()
                    ->title('New Assignment')
                    ->body("You have been assigned to a new job order: '{$jobOrder->job_order_title}'")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($appJobOrderUrl)
                    ])
                    ->sendToDatabase($assignedUser);

                // Notify the submitter that their job order has been assigned
                if ($requester && $requester->id !== $assignedUser->id) {
                    Notification::make()
                        ->title('Job Order Assigned')
                        ->body("Your job order '{$jobOrder->job_order_title}' has been assigned to {$assignedUser->full_name}.")
                        ->success()
                        ->actions([
                            Action::make('view')
                                ->visible(fn () => !auth()->user()->hasRole('Admin'))
                                ->url($appJobOrderUrl),

                            Action::make('view')
                                ->visible(fn () => auth()->user()->hasRole('Admin'))
                                ->url($jobOrderUrl),
                        ])
                        ->sendToDatabase($requester);
                }
            }
        }

        if ($jobOrder->isDirty('status') && $jobOrder->status === 'Assigned') {

            $jobOrder->date_begun = now('Asia/Manila')->format('Y-m-d H:i');

            if ($requester) {
                // Notification for the submitter that their job order has been approved
                Notification::make()
                    ->title('Job Order Approved')
                    ->body("Your Job Order '{$jobOrder->job_order_title}' has been approved by " . ($approver ? $approver->full_name : "an unknown user"))
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url(fn () => auth()->user()->hasRole('Admin') ? $jobOrderUrl : $appJobOrderUrl)
                    ])
                    ->sendToDatabase($requester);
            }

            if ($approver) {
                // Notification for the approver that they have approved the job order
                Notification::make()
                    ->title('Job Order Approved')
                    ->body("You have approved the job order '{$jobOrder->job_order_title}' of {$requester->full_name}.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($jobOrderUrl)  // Link to the Job Order detail page
                    ])
                    ->send()
                    ->sendToDatabase($approver);
            }
        }

        if ($jobOrder->isDirty('accomplished_by')) {
            if ($recommender) {
                Notification::make()
                    ->title('Job Order Finished')
                    ->body("{$assignedUser->full_name} have accomplished the job order '{$jobOrder->job_order_title}' Pending for your confirmation.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($appJobOrderUrl)  // Link to the Job Order detail page
                    ])
                    ->sendToDatabase($recommender);
            }
        }

        if ($jobOrder->isDirty('status') && $jobOrder->status === 'Rejected') {
            if ($requester) {
                Notification::make()
                    ->title('Job Order Rejected')
                    ->body("Your Job Order '{$jobOrder->job_order_title}' has been rejected by " . ($rejecter ? $rejecter->full_name : "an unknown user" . "for the reason:  '{$jobOrder->rejection_reason}'"))
                    ->danger()
                ->actions([
                    Action::make('view')
                        ->url(fn () => auth()->user()->hasRole('Admin') ? $jobOrderUrl : $appJobOrderUrl)
                ])
                    ->sendToDatabase($requester);
            }

            if ($rejecter) {
                Notification::make()
                    ->title('Job Order Rejected')
                    ->body("You have rejected the job order '{$jobOrder->job_order_title}'.")
                    ->danger()
                    ->actions([
                        Action::make('view')
                            ->url(fn () => auth()->user()->hasRole('Admin') ? $jobOrderUrl : $appJobOrderUrl)
                    ])
                    ->send()
                    ->sendToDatabase($rejecter);
            }
        }

        if ($jobOrder->isDirty('status') && $jobOrder->status === 'Canceled') {
            if ($requester) {
                Notification::make()
                    ->title('Job Order Canceled')
                    ->body("Your Job Order '{$jobOrder->job_order_title}' has been canceled for the reason:  '{$jobOrder->cancelation_reason}'")
                    ->danger()
                    ->actions([
                        Action::make('view')
                            ->url(fn () => auth()->user()->hasRole('Admin') ? $jobOrderUrl : $appJobOrderUrl)
                    ])
                    ->sendToDatabase($requester);
            }

            if ($canceler) {
                Notification::make()
                    ->title('Job Order Canceled')
                    ->body("You have canceled the job order '{$jobOrder->job_order_title}'.")
                    ->danger()
                    ->actions([
                        Action::make('view')
                            ->url(fn () => auth()->user()->hasRole('Admin') ? $jobOrderUrl : $appJobOrderUrl)
                    ])
                    ->send()
                    ->sendToDatabase($canceler);
            }
        }

        if ($jobOrder->isDirty('status') && $jobOrder->status === 'Completed') {
            if ($requester) {
                Notification::make()
                    ->title('Job Order Verified')
                    ->body("Your Job Order '{$jobOrder->job_order_title}' has been verified by " . ($recommender ? $recommender->full_name : "an unknown user"))
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url(fn () => auth()->user()->hasRole('Admin') ? $jobOrderUrl : $appJobOrderUrl)
                    ])
                    ->sendToDatabase($requester);
            }

            if ($recommender) {
                Notification::make()
                    ->title('Job Order Verified')
                    ->body("You have checked the job order '{$jobOrder->job_order_title}'.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($appJobOrderUrl)  // Link to the Job Order detail page
                    ])
                    ->send()
                    ->sendToDatabase($recommender);
            }

            if ($assignedUser) {
                Notification::make()
                    ->title('Job Order Verified')
                    ->body("{$recommender->full_name} have checked the job order '{$jobOrder->job_order_title}'.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($appJobOrderUrl)  // Link to the Job Order detail page
                    ])
                    ->sendToDatabase($assignedUser);
            }
        }

        if ($jobOrder->isDirty('confirmed_by')) {
            if ($requester) {
                Notification::make()
                    ->title('Job Order Confirmed')
                    ->body("Your Job Order '{$jobOrder->job_order_title}' has been completed")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url(fn () => auth()->user()->hasRole('Admin') ? $jobOrderUrl : $appJobOrderUrl)
                    ])
                    ->send()
                    ->sendToDatabase($requester);
            }

            if ($recommender) {
                Notification::make()
                    ->title('Job Order Verified')
                    ->body("{$requester->full_name} have confirmed the job order '{$jobOrder->job_order_title}'.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($appJobOrderUrl)  // Link to the Job Order detail page
                    ])
                    ->sendToDatabase($recommender);
            }

            if ($usersWithPermission) {
                Notification::make()
                    ->title('Job Order Completed')
                    ->body("{$requester->full_name} have confirmed the job order '{$jobOrder->job_order_title}'.")
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->url($jobOrderUrl)  // Link to the Job Order detail page
                    ])
                    ->sendToDatabase($usersWithPermission);
            }
        }
    }

    /**
     * Handle the JobOrder "deleted" event.
     */
    public function deleted(JobOrder $jobOrder): void
    {
        //
    }

    /**
     * Handle the JobOrder "restored" event.
     */
    public function restored(JobOrder $jobOrder): void
    {
        //
    }

    /**
     * Handle the JobOrder "force deleted" event.
     */
    public function forceDeleted(JobOrder $jobOrder): void
    {
        //
    }
}
