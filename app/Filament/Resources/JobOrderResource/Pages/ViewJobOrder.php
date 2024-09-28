<?php

namespace App\Filament\Resources\JobOrderResource\Pages;

use App\Filament\Resources\JobOrderResource;
use App\Models\JobOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\DB;

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
            Actions\ActionGroup::make([
                Actions\Action::make('Approve')
                    ->color('primary')
                    ->action(function (JobOrder $jobOrder): void {
                        DB::transaction(function () use ($jobOrder) {
                            $jobOrder->update([
                                'status' => 'Assigned',
                                'approved_by' => auth()->id(),  // Assuming 'approved_by' is the field name in your database
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

            Action::make('downloadPdf')
                ->color('blue')
                ->label('PDF')
                ->url(fn (JobOrder $record): string => route('job_orders.job-order-pdf', $record))
                ->icon('heroicon-o-arrow-down-tray')
                ->button(),

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
        ];
    }
}
