<?php

namespace App\Filament\Resources\ParkingLimitResource\Pages;

use App\Filament\Resources\ParkingLimitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditParkingLimit extends EditRecord
{
    protected static string $resource = ParkingLimitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
