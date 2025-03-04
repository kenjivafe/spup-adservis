<?php

namespace App\Filament\App\Clusters\Inventory\Resources;

use App\Filament\App\Clusters\Inventory;
use App\Filament\App\Clusters\Inventory\Resources\EquipmentCategoryResource\Pages;
use App\Filament\App\Clusters\Inventory\Resources\EquipmentCategoryResource\RelationManagers;
use App\Models\EquipmentCategory;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EquipmentCategoryResource extends Resource
{
    protected static ?string $model = EquipmentCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark-square';

    protected static ?string $navigationLabel = 'Categories';

    protected static ?string $modelLabel = 'Category';

    protected static ?string $cluster = Inventory::class;

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return  $user->can('Recommend Job Orders');
    }

    // Restrict creating records
    public static function canCreate(): bool
    {
        $user = auth()->user();

        return  $user->can('Recommend Job Orders');
    }

    // Restrict editing records
    public static function canEdit($record): bool
    {
        $user = auth()->user();

        return  $user->can('Recommend Job Orders');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Equipment Category')
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('name')
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
            'index' => Pages\ListEquipmentCategories::route('/'),
            'create' => Pages\CreateEquipmentCategory::route('/create'),
            'edit' => Pages\EditEquipmentCategory::route('/{record}/edit'),
        ];
    }
}
