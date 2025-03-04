<?php

namespace App\Filament\App\Clusters\Inventory\Resources\EquipmentBrandResource\Pages;

use App\Filament\App\Clusters\Inventory\Resources\EquipmentBrandResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEquipmentBrands extends ListRecords
{
    protected static string $resource = EquipmentBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
