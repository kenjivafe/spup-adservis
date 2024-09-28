<?php

namespace App\Filament\Widgets;

use App\Models\ParkingStickerApplication;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestStickerApplications extends BaseWidget
{
    protected static ?int $sort = 9;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->query(ParkingStickerApplication::query()->latest('created_at')->take(4))
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('status') // Assuming a 'status' field exists
                    ->label('Status')
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'yellow',
                        'Rejected' => 'danger',
                        'Revoked' => 'danger',
                        'Active' => 'primary',
                        'Expired' => 'warning'
                    }),
                Tables\Columns\TextColumn::make('plate_number')
                    ->label('Plate Number'),
                Tables\Columns\TextColumn::make('vehicle.type') // Uses relationship to get vehicle type
                    ->label('Vehicle Type'),
                Tables\Columns\ColorColumn::make('vehicle_color')
                    ->label('Color'),
                Tables\Columns\TextColumn::make('applicant.full_name') // Uses relationship to get applicant name
                    ->label('Applicant'),
            ]);
    }
}
