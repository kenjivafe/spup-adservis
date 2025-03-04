<?php

namespace App\Filament\App\Clusters\Inventory\Resources\EquipmentBrandResource\Pages;

use App\Filament\App\Clusters\Inventory\Resources\EquipmentBrandResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEquipmentBrand extends CreateRecord
{
    protected static string $resource = EquipmentBrandResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
