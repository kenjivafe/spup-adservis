<?php

namespace App\Filament\App\Clusters\Inventory\Resources\EquipmentResource\Pages;

use App\Filament\App\Clusters\Inventory\Resources\EquipmentResource;
use App\Models\Equipment;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\DB;

class EditEquipment extends EditRecord
{
    protected static string $resource = EquipmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Dispose')
                ->color('gray')
                ->button()
                ->modalWidth(MaxWidth::Medium)
                ->form([
                    Select::make('disposal_reason')
                    ->label('Disposal Reason')
                    ->native(false)
                    ->options([
                        'Unrepairable' => 'Unrepairable',
                        'Obsolete' => 'Obsolete',
                        'Stolen' => 'Stolen',
                    ])
                    ->required()
                    ->placeholder('Please select a reason for disposal.'),
                ])
                ->requiresConfirmation()
                ->action(function (Equipment $equipment, array $data): void {
                    $disposalReason = $data['disposal_reason'];

                    DB::transaction(function () use ($equipment, $disposalReason) {
                        $equipment->update([
                            'status' => 'Disposed',
                            'disposal_reason' => $disposalReason,
                            'date_disposed' => now()
                        ]);

                        $this->redirect($this->getResource()::getUrl('edit', ['record' => $equipment->getKey()]));
                    });
                })
                ->visible(fn ($record) => $record->status === 'Inactive')
                ->icon('heroicon-s-trash'),
        ];
    }

    public function getTitle(): string
    {
        return 'Edit Equipment'; // Fallback title if record not loaded
    }
}
