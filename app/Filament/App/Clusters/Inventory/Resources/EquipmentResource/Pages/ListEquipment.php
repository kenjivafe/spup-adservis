<?php

namespace App\Filament\App\Clusters\Inventory\Resources\EquipmentResource\Pages;

use App\Filament\App\Clusters\Inventory\Resources\EquipmentResource;
use App\Models\Equipment;
use App\Models\Unit;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ListEquipment extends ListRecords
{
    protected static string $resource = EquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Equipment'),
        ];
    }

    public function getTabs(): array
    {
        // Create filter tabs dynamically for each unit
        $unitTabs = Unit::all()->mapWithKeys(function (Unit $unit) {
            $equipmentCount = Equipment::where('unit_id', $unit->id)->count();

            return [
                $unit->id => Tab::make($unit->code)
                    ->badge($equipmentCount) // Set the badge with the count
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('unit_id', $unit->id)),
            ];
        })->toArray();

        return array_merge([
            'all' => Tab::make('All')
                ->modifyQueryUsing(fn (Builder $query) => $query), // No modification, shows all records
        ], $unitTabs);
    }
}
