<?php

namespace App\Filament\Clusters\Inventory\Resources\EquipmentResource\Pages;

use App\Filament\Clusters\Inventory\Resources\EquipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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
            Actions\DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Edit Equipment'; // Fallback title if record not loaded
    }
}
