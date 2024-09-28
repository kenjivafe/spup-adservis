<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParkingLimitResource\Pages;
use App\Filament\Resources\ParkingLimitResource\RelationManagers;
use App\Models\ParkingLimit;
use App\Models\Department;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ParkingLimitResource extends Resource
{
    protected static ?string $model = ParkingLimit::class;

    protected static ?string $navigationLabel = 'Parking Limits';

    protected static ?string $modelLabel = 'Limit';

    protected static ?string $navigationGroup = 'Parking Sticker Applications';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->required(),
                Select::make('vehicle_category')
                    ->options([
                        '4 Wheels' => '4 Wheels',
                        '2 Wheels' => '2 Wheels',
                    ])
                    ->required(),
                TextInput::make('limit')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('vehicle_category')
            ->columns([
                TextColumn::make('department.name')
                    ->label('Department'),
                TextColumn::make('vehicle_category')
                    ->label('Vehicle Category'),
                TextColumn::make('limit'),
            ])
            ->filters([
                //
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
            'index' => Pages\ListParkingLimits::route('/'),
            'create' => Pages\CreateParkingLimit::route('/create'),
            'edit' => Pages\EditParkingLimit::route('/{record}/edit'),
        ];
    }
}
