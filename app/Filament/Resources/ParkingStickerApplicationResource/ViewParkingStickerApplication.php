<?php

namespace App\Filament\App\Resources\ParkingStickerApplicationResource\Pages;

use App\Filament\App\Resources\ParkingStickerApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewParkingStickerApplication extends ViewRecord
{
    protected static string $resource = ParkingStickerApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
