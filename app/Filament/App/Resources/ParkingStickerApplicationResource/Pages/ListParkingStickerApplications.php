<?php

namespace App\Filament\App\Resources\ParkingStickerApplicationResource\Pages;

use App\Filament\App\Resources\ParkingStickerApplicationResource;
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
