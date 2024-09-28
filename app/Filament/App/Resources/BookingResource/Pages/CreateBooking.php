<?php

namespace App\Filament\App\Resources\BookingResource\Pages;

use App\Filament\App\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

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
        $data['person_responsible'] = auth()->user()->id;
        $data['date_requested'] = now('Asia/Manila')->format('Y-m-d H:i'); // Include both date and time
        return $data;
    }
}
