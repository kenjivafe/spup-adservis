<?php

namespace App\Filament\Clusters\Inventory\Resources\EquipmentCategoryResource\Pages;

use App\Filament\Clusters\Inventory\Resources\EquipmentCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEquipmentCategory extends CreateRecord
{
    protected static string $resource = EquipmentCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
