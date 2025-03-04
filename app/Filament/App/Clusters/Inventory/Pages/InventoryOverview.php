<?php

namespace App\Filament\App\Clusters\Inventory\Pages;

use App\Filament\App\Clusters\Inventory;
use App\Models\EquipmentType;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Pages\Page;

class InventoryOverview extends Page implements HasForms
{
    use InteractsWithForms, HasFiltersForm;

    protected static string $view = 'filament.clusters.inventory.pages.inventory-overview';

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Reports and Overview';

    protected static ?int $navigationSort = 6;

    protected static ?string $cluster = Inventory::class;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return  $user->can('Recommend Job Orders');
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\InventoryOverview::make(['filters' => $this->filters]),
        ];
    }

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('equipment_category_id')
                            ->label('Equipment Category')
                            ->options(function () {
                                return \App\Models\EquipmentCategory::all()->pluck('name', 'id');
                            })
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('equipment_type_id', null))
                            ->native(false),
                        Select::make('equipment_type_id')
                            ->label('Equipment Type')
                            ->options(function (callable $get) {
                                $categoryId = $get('equipment_category_id');
                                return $categoryId ? EquipmentType::where('equipment_category_id', $categoryId)->pluck('name', 'id') : [];
                            })
                            ->reactive()
                            ->native(false),
                        Select::make('unit_id')
                            ->label('Unit/Department')
                            ->options(function () {
                                return \App\Models\Unit::all()->pluck('name', 'id');
                            }),
                    ])->columns(3)
            ]);
    }
}
