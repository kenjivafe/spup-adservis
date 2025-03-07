<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\PermissionResource\Pages;
use App\Filament\App\Resources\PermissionResource\RelationManagers;
use App\Models\Permission;
use Filament\Forms\Components\Card;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfolistsSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static ?string $navigationLabel = 'All Permissions';

    protected static ?string $modelLabel = 'Permission';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Card::make()->schema([
                // TextInput::make('name')
                //     ->minLength(2)
                //     ->maxLength(255)
                //     ->required()
                //     ->unique(ignoreRecord: true)
                // ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->label('Role ID'),
                TextColumn::make('name')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Manage Users' => 'yellow',
                        'Manage Job Orders' => 'warning',
                        'Manage Venue Bookings' => 'warning',
                        'Manage Sticker Applications' => 'warning',
                        'Recommend Job Order', => 'blue',
                        'Be Assigned to Job Orders' => 'purple',
                        'Post Job Orders' => 'primary',
                        'Book Venues' => 'primary',
                        'Apply for Sticker' => 'primary',
                        default => 'blue',
                    }),
                TextColumn::make('created_at')
                    ->since()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make()->label(''),
                // Tables\Actions\DeleteAction::make()->label(''),
                Tables\Actions\ViewAction::make()->label(''),
            ])
            ->recordAction(Tables\Actions\ViewAction::class)
            ->recordUrl(null);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistsSection::make('Permission Info')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Permission'),
                        TextEntry::make('roles.name')
                            ->label('Role/s Assigned')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Admin' => 'purple',
                                'Staff' => 'warning',
                                'Employee' => 'yellow',
                                'Student' => 'success',
                                default => 'default-color',
                            }),
                    ])->columns(2),
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
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }
}
