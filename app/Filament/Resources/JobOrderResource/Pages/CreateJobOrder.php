<?php

namespace App\Filament\Resources\JobOrderResource\Pages;

use App\Filament\Resources\JobOrderResource;
use App\Models\Equipment;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateJobOrder extends CreateRecord
{
    protected static string $resource = JobOrderResource::class;

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
        $data['requested_by'] = auth()->user()->id;
        $data['date_requested'] = now('Asia/Manila')->format('Y-m-d H:i'); // Include both date and time
        return $data;
    }

    protected function afterSave(): void
    {
        // Get the equipment IDs from the form state
        $equipmentIds = $this->record->equipment->pluck('id')->toArray();

        // Update the status of the selected equipment to 'Unavailable'
        Equipment::whereIn('id', $equipmentIds)->update(['status' => 'Unavailable']);
    }
}
