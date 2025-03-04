<?php

namespace App\Filament\App\Clusters\Inventory\Resources;

use App\Filament\App\Clusters\Inventory;
use App\Filament\App\Clusters\Inventory\Resources\UnitResource\Pages;
use App\Filament\App\Clusters\Inventory\Resources\UnitResource\RelationManagers;
use App\Models\Unit;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationLabel = 'Units/Departments';

    protected static ?string $modelLabel = 'Unit';

    protected static ?string $cluster = Inventory::class;

    protected static ?int $navigationSort = 2;

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
                Section::make('Unit/Department')
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('unit_head')
                            ->options(User::role('Unit Head')->pluck('full_name', 'id'))
                            ->required(),
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
                Tables\Columns\TextColumn::make('unitHead.full_name')->label('Unit Head'),
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
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}
