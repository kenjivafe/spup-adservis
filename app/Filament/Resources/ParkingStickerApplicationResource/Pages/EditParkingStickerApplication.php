<?php

namespace App\Filament\Resources\ParkingStickerApplicationResource\Pages;

use App\Filament\Resources\ParkingStickerApplicationResource;
use App\Models\ParkingStickerApplication;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditParkingStickerApplication extends EditRecord
{
    protected static string $resource = ParkingStickerApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make('Approve')
                    ->color('primary')
                    ->action(function (ParkingStickerApplication $application): void {
                        $expirationDate = today()->addYear(1);

                        DB::transaction(function () use ($application, $expirationDate) {
                            $application->update([
                                'status' => 'Active',
                                'approved_by' => auth()->id(),  // Assuming 'approved_by' is the field name in your database
                                'expiration_date' => $expirationDate,
                            ]);

                            $application->save();
                            $this->redirect($this->getResource()::getUrl('edit', ['record' => $application->getKey()]));
                        });
                    })
                    ->visible(fn ($record) =>
                        optional($record)->status === 'Pending' &&
                        empty($record->approved_by) &&
                        auth()->user()->can('Manage Job Orders')
                    )
                    ->icon('heroicon-s-check'),

                Actions\Action::make('Reject')
                    ->color('danger')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->placeholder('Please provide a reason for rejection.'),
                    ])
                    ->requiresConfirmation()
                    ->action(function (ParkingStickerApplication $application, array $data,): void {
                        $rejectionReason = $data['rejection_reason'];

                        DB::transaction(function () use ($application, $rejectionReason) {
                            $application->update([
                                'status' => 'Rejected',
                                'rejected_by' => auth()->id(),
                                'rejection_reason' => $rejectionReason,
                            ]);
                        });
                        $this->redirect($this->getResource()::getUrl('edit', ['record' => $application->getKey()]));
                    })
                    ->visible(fn ($record) =>
                        optional($record)->status === 'Pending' &&
                        empty($record->approved_by) &&
                        empty($record->rejected_by) &&
                        auth()->user()->can('Manage Parking Application')
                    )
                    ->icon('heroicon-o-no-symbol'),
                ])
                ->label('Approval')->icon('heroicon-o-chevron-down')->button()->color('yellow'),
            Actions\Action::make('Revoke')
                ->label('Revoke Sticker')
                ->color('danger')
                ->form([
                    Textarea::make('revocation_reason')
                        ->label('Revocation Reason')
                        ->required()
                        ->placeholder('Please provide a reason for revocation.'),
                ])
                ->requiresConfirmation()
                ->action(function (ParkingStickerApplication $application, array $data,): void {
                    $revocationReason = $data['revocation_reason'];

                    DB::transaction(function () use ($application, $revocationReason) {
                        $application->update([
                            'status' => 'Revoked',
                            'revoked_by' => auth()->id(),
                            'revocation_reason' => $revocationReason,
                        ]);
                    });
                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $application->getKey()]));
                })
                ->visible(fn ($record) =>
                    optional($record)->status === 'Active' &&
                    empty($record->revoked_by) &&
                    auth()->user()->can('Manage Parking Applications')
                )
                ->icon('heroicon-o-no-symbol'),
            // Actions\DeleteAction::make(),
        ];
    }


    protected function getFormActions(): array
    {

        return array_merge(parent::getFormActions());
    }
}
