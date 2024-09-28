<?php

namespace App\Filament\Resources\ParkingStickerApplicationResource\Pages;

use App\Filament\Resources\ParkingStickerApplicationResource;
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
