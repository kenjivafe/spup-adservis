<?php

namespace App\Filament\Resources\JobOrderResource\Pages;

use App\Filament\Resources\JobOrderResource;
use App\Models\JobOrder;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;
use Filament\Forms\Components\Textarea;
use Spatie\Browsershot\Browsershot;

class EditJobOrder extends EditRecord
{
    protected static string $resource = JobOrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            $approvalActions = Actions\ActionGroup::make([
                Actions\Action::make('Approve')
                    ->color('primary')
                    ->action(function (JobOrder $jobOrder): void {
                        DB::transaction(function () use ($jobOrder) {
                            $jobOrder->update([
                                'status' => 'Assigned',
                                'approved_by' => auth()->id(),  // Assuming 'approved_by' is the field name in your database
                            ]);

                            $jobOrder->save();
                            $this->redirect($this->getResource()::getUrl('edit', ['record' => $jobOrder->getKey()]));
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
                            $this->redirect($this->getResource()::getUrl('edit', ['record' => $jobOrder->getKey()]));
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

                Action::make('Generate PDF')
                ->button()
                ->label('PDF')
                ->color('gray')
                ->icon('heroicon-s-document-arrow-down')
                ->action(function (JobOrder $record) {
                    // Create HTML content using a template engine like Blade
                    $html = view('pdfs.job-order', ['jobOrder' => $record, 'title' => 'UNIV-025'])->render();
                    $headerHtml = view('pdfs.header')->render();
                    $footerHtml = view('pdfs.footer')->render();

                    // Generate PDF
                    Browsershot::html($html)
                    ->waitUntilNetworkIdle()
                    ->writeOptionsToFile()
                    // ->showBrowserHeaderAndFooter()
                    // ->headerHtml($headerHtml)
                    // ->footerHtml($footerHtml)
                    ->format('A4')
                    ->landscape()
                    ->showBackground()
                    ->margins(12, 10, 12, 10)
                    ->save('job-order-' . $record->id . '.pdf');

                    // You can also return a download response:
                    return response()->download(public_path('job-order-' . $record->id . '.pdf'))
                    ->deleteFileAfterSend(true);
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
                            'cancelation_reason' => $cancelationReason
                        ]);
                        $this->redirect($this->getResource()::getUrl('edit', ['record' => $jobOrder->getKey()]));
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

    protected function getFormActions(): array
    {
        return array_merge(parent::getFormActions());
    }
}
