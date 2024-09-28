<?php

namespace App\Filament\Resources\ParkingLimitResource\Pages;

use App\Filament\Resources\ParkingLimitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParkingLimits extends ListRecords
{
    protected static string $resource = ParkingLimitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
