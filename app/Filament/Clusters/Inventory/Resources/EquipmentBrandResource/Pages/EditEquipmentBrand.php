<?php

namespace App\Filament\Clusters\Inventory\Resources\EquipmentBrandResource\Pages;

use App\Filament\Clusters\Inventory\Resources\EquipmentBrandResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEquipmentBrand extends EditRecord
{
    protected static string $resource = EquipmentBrandResource::class;

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
