<?php

namespace App\Filament\Clusters\Inventory\Resources;

use App\Filament\Clusters\Inventory;
use App\Filament\Clusters\Inventory\Resources\EquipmentBrandResource\Pages;
use App\Filament\Clusters\Inventory\Resources\EquipmentBrandResource\RelationManagers;
use App\Models\EquipmentBrand;
use App\Models\EquipmentCategory;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EquipmentBrandResource extends Resource
{
    protected static ?string $model = EquipmentBrand::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Brands';

    protected static ?string $modelLabel = 'Brand';

    protected static ?string $cluster = Inventory::class;

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Equipment Brand')
                    ->aside()
                    ->schema([
                        Select::make('equipment_category_id')
                            ->native(false)
                            ->label('Equipment Category')
                            ->options(EquipmentCategory::all()->pluck('name', 'id'))
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('Brand Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(255),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption('all')
            ->groups([
                Group::make('equipmentCategory.code')
                    ->getTitleFromRecordUsing(function (EquipmentBrand $record) {
                        // Access the related EquipmentCategory model
                        $category = $record->equipmentCategory;

                        // If the category exists, return its code
                        return $category ? $category->name : '';
                    })
                    ->getDescriptionFromRecordUsing(function (EquipmentBrand $record) {
                        // Access the related EquipmentCategory model
                        $category = $record->equipmentCategory;

                        // If the category exists, return its code
                        return $category ? 'Code: ' . $category->code : '';
                    })
                    ->titlePrefixedWithLabel(false),
            ])
            ->groupingSettingsHidden()
            ->defaultGroup('equipmentCategory.code')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('code'),
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
            'index' => Pages\ListEquipmentBrands::route('/'),
            'create' => Pages\CreateEquipmentBrand::route('/create'),
            'edit' => Pages\EditEquipmentBrand::route('/{record}/edit'),
        ];
    }
}
