<?php

namespace App\Filament\App\Resources\ParkingStickerApplicationResource\Pages;

use App\Filament\App\Resources\ParkingStickerApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditParkingStickerApplication extends EditRecord
{
    protected static string $resource = ParkingStickerApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
