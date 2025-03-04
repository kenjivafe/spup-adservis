<?php

namespace App\Filament\App\Clusters\Inventory\Resources\EquipmentCategoryResource\Pages;

use App\Filament\App\Clusters\Inventory\Resources\EquipmentCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEquipmentCategory extends EditRecord
{
    protected static string $resource = EquipmentCategoryResource::class;

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
