<?php

namespace App\Filament\Clusters\Inventory\Resources;

use App\Filament\Clusters\Inventory;
use App\Filament\Clusters\Inventory\Resources\EquipmentResource\Pages;
use App\Filament\Clusters\Inventory\Resources\EquipmentResource\RelationManagers;
use App\Forms\Components\EquipmentStatusBadge;
use App\Models\Equipment;
use App\Models\EquipmentBrand;
use App\Models\EquipmentCategory;
use App\Models\EquipmentType;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Equipment';

    protected static ?string $modelLabel = 'Equipment';

    // protected static ?string $pluralModelLabel = 'Equipments';

    protected static ?string $cluster = Inventory::class;

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Equipment Details')
                    ->aside()
                    ->schema([
                        ViewField::make('status')
                            ->label('')
                            ->view('filament.forms.components.equipment-status')
                            ->visible(fn ($record) => !empty($record)),
                        Select::make('unit_id')
                            ->native(false)
                            ->searchable()
                            ->label('Unit')
                            ->live()
                            ->options(Unit::all()->pluck('name', 'id'))
                            ->required()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateEquipmentCode($set, $get)),
                        Select::make('equipment_category_id')
                            ->native(false)
                            ->searchable()
                            ->label('Equipment Category')
                            ->live()
                            ->options(EquipmentCategory::all()->pluck('name', 'id'))
                            ->required()
                            ->afterStateUpdated(function (Set $set) {
                                $set('equipment_type_id', null);
                                $set('equipment_brand_id', null);
                            })
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateEquipmentCode($set, $get)),
                        Select::make('equipment_brand_id')
                            ->native(false)
                            ->searchable()
                            ->label('Equipment Brand')
                            ->live()
                            ->options(fn(Get $get): Collection => EquipmentBrand::query()
                                ->where('equipment_category_id', $get('equipment_category_id'))
                                ->pluck('name', 'id'))
                            ->preload()
                            ->required()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateEquipmentCode($set, $get)),
                        Select::make('equipment_type_id')
                            ->native(false)
                            ->searchable()
                            ->label('Equipment Type')
                            ->live()
                            ->options(fn(Get $get): Collection => EquipmentType::query()
                                ->where('equipment_category_id', $get('equipment_category_id'))
                                ->pluck('name', 'id'))
                            ->preload()
                            ->required()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateEquipmentCode($set, $get)),
                        TextInput::make('code')
                            ->label('Equipment Code')
                            ->default('XXXX-XXXX-XX-XX-X')
                            ->readOnly()
                            ->unique(Equipment::class, 'code', fn ($record) => $record),
                        Split::make([
                            DatePicker::make('date_acquired')
                                ->label('Date Acquired')
                                ->required(),
                            DatePicker::make('date_disposed')
                                ->label('Date Disposed')
                                ->nullable()
                                ->disabledOn('create')
                                ->hidden(fn ($get, $record) => $record === null || is_null($record->date_disposed))
                                ->readonly()
                        ])
                    ])
            ]);
    }

    /**
     * Update the equipment code dynamically based on selected values.
     */
    protected static function updateEquipmentCode(Set $set, Get $get): void
    {
        $unitCode = Unit::find($get('unit_id'))?->code ?? 'XXXX';
        $categoryCode = EquipmentCategory::find($get('equipment_category_id'))?->code ?? 'XXXX';
        $typeCode = EquipmentType::find($get('equipment_type_id'))?->code ?? 'XX';
        $brandCode = EquipmentBrand::find($get('equipment_brand_id'))?->code ?? 'XX';

        $baseCode = "{$unitCode}-{$categoryCode}-{$brandCode}-{$typeCode}";

        $existingMaxIdentifier = Equipment::query()
        ->where('unit_id', $get('unit_id'))
        ->where('equipment_category_id', $get('equipment_category_id'))
        ->where('equipment_type_id', $get('equipment_type_id'))
        ->where('equipment_brand_id', $get('equipment_brand_id'))
        ->orderByRaw('CAST(SUBSTRING_INDEX(code, "-", -1) AS UNSIGNED) DESC') // Extract the last part after the last hyphen and order descending
        ->first(); // Get the first record which should have the max identifier

        // Extract the last 2 digits of the existing code to find the highest identifier
        // Initialize next identifier
        $nextIdentifier = 1;

        if ($existingMaxIdentifier) {
            // Extract number after the last hyphen
            $existingCode = $existingMaxIdentifier->code;
            $existingIdentifier = intval(substr($existingCode, strrpos($existingCode, '-') + 1));

            // Increment the identifier by 1
            $nextIdentifier = $existingIdentifier + 1;
        }

        // Combine base code with the new identifier
        $generatedCode = "{$baseCode}-{$nextIdentifier}";

        $set('code', $generatedCode);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption('all')
            ->groups([
                Group::make('equipmentCategory.code')
                    ->getTitleFromRecordUsing(function (Equipment $record) {
                        // Access the related EquipmentCategory model
                        $category = $record->equipmentCategory;

                        // If the category exists, return its code
                        return $category ? $category->name : '';
                    })
                    ->getDescriptionFromRecordUsing(function (Equipment $record) {
                        // Access the related EquipmentCategory model
                        $category = $record->equipmentCategory;

                        // If the category exists, return its code
                        return $category ? 'Code: ' . $category->code : '';
                    })
                    ->titlePrefixedWithLabel(false),
            ])
            ->groupingSettingsHidden()
            ->defaultGroup('equipmentCategory.code')
            ->defaultSort('code', 'asc')
            ->columns([
                TextColumn::make('code'),
                TextColumn::make('status')
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'primary',
                        'Inactive' => 'warning',
                        'Disposed' => 'danger',
                    }),
                TextColumn::make('unit.name'),
                TextColumn::make('equipmentBrand.name')->label('Brand'),
                TextColumn::make('equipmentType.name')->label('Type'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEquipment::route('/'),
            'create' => Pages\CreateEquipment::route('/create'),
            'edit' => Pages\EditEquipment::route('/{record}/edit'),
        ];
    }
}
