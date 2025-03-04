<?php

namespace App\Filament\App\Clusters\Inventory\Resources\UnitResource\Pages;

use App\Filament\App\Clusters\Inventory\Resources\UnitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUnit extends CreateRecord
{
    protected static string $resource = UnitResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
