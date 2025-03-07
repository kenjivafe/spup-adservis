<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\RoleResource\Pages;
use App\Filament\App\Resources\RoleResource\RelationManagers;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfolistsSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationLabel = 'All Roles';

    protected static ?string $modelLabel = 'Role';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')
                        ->minLength(2)
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Select::make('permissions')
                        ->multiple()
                        ->relationship('permissions', 'name')
                        ->preload()
                ])->columns(2)
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
                    ->label('Role'),
                TextColumn::make('permissions.name')
                    ->label('Permissions')
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
                Tables\Actions\EditAction::make()->label(''),
                Tables\Actions\ViewAction::make()->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordAction(Tables\Actions\ViewAction::class)
            ->recordUrl(null);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistsSection::make('Role Info')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Role'),
                        TextEntry::make('permissions.name')
                            ->label('Permissions')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Manage Users' => 'purple',
                                'Manage Job Orders' => 'yellow',
                                'Manage Venue Bookings' => 'yellow',
                                'Manage Sticker Applications' => 'yellow',
                                'Post Job Orders' => 'primary',
                                'Book Venues' => 'primary',
                                'Apply for Sticker' => 'primary',
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('name', '!=', 'Admin');
    }
}
