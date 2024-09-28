<?php

namespace App\Filament\App\Resources\VenueResource\Pages;

use App\Filament\App\Resources\VenueResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewVenue extends ViewRecord
{
    protected static string $resource = VenueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function getTitle(): string | Htmlable
    {
        if (isset($this->record)) {
            return $this->record->name; // Use the desired field for the title
        }

        return 'View Venue'; // Fallback title if record not loaded
    }
}
