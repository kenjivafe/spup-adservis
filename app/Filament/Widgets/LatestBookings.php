<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestBookings extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->query(Booking::query()->latest('date_requested')->take(4))
            ->defaultSort('date_requested', 'desc')
            ->columns([
                TextColumn::make('status')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->hasPendingAction()) {
                            return $state . ' 🔸'; // Appending a simple red circle emoji as a notification dot
                        }                        // Return the state as is for other conditions
                        return match ($state) {
                            'Pending' => 'Pending',
                            'Rejected' => 'Rejected',
                            'Canceled' => 'Canceled',
                            'Approved' => 'Approved',
                            'Unavailable' => 'Unavailable',
                            'Confirmed' => 'Confirmed',
                            'Ongoing' => 'Ongoing',
                            'Ended' => 'Ended',
                            default => $state,
                        };
                        return $state;
                    })
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'yellow',
                        'Canceled' => 'danger',
                        'Unavailable' => 'warning',
                        'Approved' => 'primary',
                        'Rejected' => 'danger',
                        'Confirmed' => 'primary',
                        'Ongoing' => 'blue',
                        'Ended' => 'gray'
                    }),
                TextColumn::make('purpose'),
                TextColumn::make('venue.name'),
                TextColumn::make('starts_at')
                    ->label('Event Start Date')
                    ->dateTime('M d, Y \a\t g:iA')
                    ->sortable(),
                TextColumn::make('participants'),
            ]);
    }
}
