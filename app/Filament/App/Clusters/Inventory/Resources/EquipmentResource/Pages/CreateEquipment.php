<?php

namespace App\Filament\App\Clusters\Inventory\Resources\EquipmentResource\Pages;

use App\Filament\App\Clusters\Inventory\Resources\EquipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEquipment extends CreateRecord
{
    protected static string $resource = EquipmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Add Equipment'; // Fallback title if record not loaded
    }
}
