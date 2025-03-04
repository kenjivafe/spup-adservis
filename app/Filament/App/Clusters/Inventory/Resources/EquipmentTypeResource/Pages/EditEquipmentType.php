<?php

namespace App\Filament\App\Clusters\Inventory\Resources\EquipmentTypeResource\Pages;

use App\Filament\App\Clusters\Inventory\Resources\EquipmentTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEquipmentType extends EditRecord
{
    protected static string $resource = EquipmentTypeResource::class;

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
}
