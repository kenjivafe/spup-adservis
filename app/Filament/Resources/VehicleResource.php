<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationLabel = 'Vehicle Types';

    protected static ?string $modelLabel = 'Vehicle Type';

    protected static ?string $navigationGroup = 'Parking Sticker Applications';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('type')
                    ->required()
                    ->label('Vehicle Type')
                    ->helperText('Enter the type of vehicle, e.g., Sedan, SUV, Motorcycle.'),
                TextInput::make('sticker_cost')
                    ->required()
                    ->numeric()
                    ->label('Sticker Cost')
                    ->helperText('Enter the cost of the parking sticker for this vehicle type.')
                    ->prefix('₱') // Optionally add a prefix or suffix
                    ->step(0.01), // Set the step for numeric input, allowing decimal places
                TextInput::make('category')
                    ->required()
                    ->maxLength(255),
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('type')
                ->label('Vehicle Type')
                ->sortable(),
            TextColumn::make('sticker_cost')
                ->label('Sticker Cost')
                ->money('php', true)  // Format as money in USD, show cents
                ->sortable(),
            TextColumn::make('category'),
        ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
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
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
