<?php

namespace App\Filament\Widgets;

use App\Models\JobOrder;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestJobOrders extends BaseWidget
{
    protected static ?int $sort = 1;
    // protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->query(JobOrder::query()->latest('date_requested')->take(4))
            ->defaultSort('date_requested', 'desc')
            ->columns([
                TextColumn::make('status')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->hasPendingAction()) {
                            return $state . ' 🔸'; // Appending a simple red circle emoji as a notification dot
                        }
                        // Special handling for 'Assigned' status
                        if ($state === 'Assigned' && auth()->id() !== $record->assigned_to) {
                            return 'In Progress';  // Change the displayed value to 'In Progress'
                        }

                        // Return the state as is for other conditions
                        return match ($state) {
                            'Pending' => 'Pending',
                            'Rejected' => 'Rejected',
                            'Canceled' => 'Canceled',
                            'Available' => 'Available',
                            'Assigned' => 'Assigned',
                            'In Progress' => 'In Progress',
                            'Completed' => 'Completed',
                            default => $state,
                        };

                        return $state;
                    })
                    ->color(fn (string $state): string => match ($state) {
                            'Pending' => 'yellow',
                            'Canceled' => 'danger',
                            'Rejected' => 'danger',
                            'Assigned' => 'purple',
                            'In Progress' => 'purple',
                            'Completed' => 'primary',
                    }),
                TextColumn::make('job_order_title')
                    ->label('Job Order Title'),
                TextColumn::make('unit_name')
                    ->label('Unit'),
            ]);
    }
}
