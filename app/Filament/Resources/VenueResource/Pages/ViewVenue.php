<?php

namespace App\Filament\Resources\VenueResource\Pages;

use App\Filament\Resources\VenueResource;
use App\Models\Venue;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewVenue extends ViewRecord
{
    protected static string $resource = VenueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('Edit Venue'),
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
