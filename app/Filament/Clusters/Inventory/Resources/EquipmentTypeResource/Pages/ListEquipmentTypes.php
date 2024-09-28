<?php

namespace App\Filament\Clusters\Inventory\Resources\EquipmentTypeResource\Pages;

use App\Filament\Clusters\Inventory\Resources\EquipmentTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEquipmentTypes extends ListRecords
{
    protected static string $resource = EquipmentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
