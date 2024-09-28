<?php

namespace App\Filament\App\Resources\ParkingStickerApplicationResource\Pages;

use App\Filament\App\Resources\ParkingStickerApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateParkingStickerApplication extends CreateRecord
{
    protected static string $resource = ParkingStickerApplicationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return null;
    }

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['applicant_id'] = auth()->user()->id;
        $data['contact_number'] = auth()->user()->phone;
        return $data;
    }
}
