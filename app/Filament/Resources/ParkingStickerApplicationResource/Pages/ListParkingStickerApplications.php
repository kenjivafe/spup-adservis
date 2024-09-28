<?php

namespace App\Filament\Resources\ParkingStickerApplicationResource\Pages;

use App\Filament\Resources\ParkingStickerApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParkingStickerApplications extends ListRecords
{
    protected static string $resource = ParkingStickerApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
