<?php

namespace App\Filament\Clusters\Inventory\Resources\UnitResource\Pages;

use App\Filament\Clusters\Inventory\Resources\UnitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUnits extends ListRecords
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
