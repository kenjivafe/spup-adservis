<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section as InfolistsSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Guava\FilamentClusters\Forms\Cluster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'All Users';

    protected static ?string $modelLabel = 'User';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('User Information')->schema([
                    Cluster::make([
                        TextInput::make('name')
                            ->prefix('FN')
                            ->placeholder('First Name')
                            ->columnSpan(1)
                            ->label(__('filament-edit-profile::default.name'))
                            ->required(),
                        TextInput::make('surname')
                            ->prefix('LN')
                            ->placeholder('Last Name')
                            ->columnSpan(1)
                            ->label(__('Surname'))
                            ->required(),
                    ])
                    ->label('Full Name')
                    ->columnSpan(2),
                    Cluster::make([
                        TextInput::make('email')
                            ->placeholder('Email Address')
                            ->prefixIcon('heroicon-s-envelope')
                            ->columnSpan(1)
                            ->label(__('filament-edit-profile::default.email'))
                            ->email()
                            ->required(),
                        TextInput::make('phone')
                            ->placeholder('Phone Number')
                            ->prefixIcon('heroicon-s-phone')
                            ->columnSpan(1)
                            ->label(__('Contact Number'))
                            ->tel()
                            ->prefix('+63')
                            ->mask('999 999 9999')
                            ->required()
                    ])
                    ->label('Contact')
                    ->columnSpan(2),
                    Select::make('roles')
                        ->required()
                        ->native(false)
                        ->label('Role')
                        ->columnSpan('full')
                        ->relationship('roles', 'name')
                        ->preload(),
                    // Forms\Components\TextInput::make('schoolid')
                    //     ->label('School ID')
                    //     ->dehydrated(fn ($state) => filled($state))
                    //     ->maxLength(255),
                    // Forms\Components\TextInput::make('password')
                    //     ->password()
                    //     ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    //     ->dehydrated(fn ($state) => filled($state))
                    //     ->required(fn (Page $livewire) => ($livewire instanceof CreateUser))
                    //     ->maxLength(255),
                ])->aside()->description('Update the profile information of the account and assign a Role to this User.')->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->getStateUsing(function ($record) {
                        // Check if the record has a non-empty avatar_url
                        if (!empty($record->avatar_url)) {
                            return $record->avatar_url;  // Use the avatar_url if it exists
                        }
                        // Fall back to a default method if avatar_url is empty or not set
                        return Filament::getUserAvatarUrl($record);
                    }),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Users')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->name . ' ' . $record->surname),
                Tables\Columns\TextColumn::make('contact')
                    ->searchable(['email', 'phone'])
                    ->getStateUsing(fn ($record) => $record->email . ' | ' . $record->phone),
                // Tables\Columns\TextColumn::make('schoolid')
                //     ->label('School ID')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('User Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Admin' => 'yellow',
                        'Staff' => 'warning',
                        'Employee' => 'primary',
                        'Student' => 'success',
                        'Maintenance' => 'purple',
                        'Contractor' => 'purple',
                        default => 'blue',
                    }),
                Tables\Columns\TextColumn::make('permissions')
                    ->label('Permissions')
                    ->getStateUsing(fn ($record) => ($record->getAllPermissions()->pluck('name')->toArray()))
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'Manage Users' => 'yellow',
                            'Manage Job Orders', 'Manage Venue Bookings', 'Manage Sticker Applications' => 'warning',
                            'Be Assigned to Job Orders' => 'purple',
                            'Post Job Orders', 'Book Venues', 'Apply for Sticker' => 'primary',
                            default => 'blue',
                        };
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(''),
                // Tables\Actions\DeleteAction::make()->label(''),
                // Tables\Actions\ViewAction::make()->label(''),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->recordAction(Tables\Actions\EditAction::class)
            ->recordUrl(null);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistsSection::make('User Info')
                    ->schema([
                        TextEntry::make('full_name')
                            ->label('Name'),
                        TextEntry::make('contact')
                            ->label('Contact')
                            ->getStateUsing(fn ($record) => $record->email . ' | ' . $record->phone),
                        TextEntry::make('schoolid')
                            ->label('School ID'),
                        InfolistsSection::make([
                            TextEntry::make('roles.name')
                                ->label('Role')
                                ->badge()
                                ->separator(' ')
                                ->color(fn (string $state): string => match ($state) {
                                    'Admin' => 'yellow',
                                    'Staff' => 'warning',
                                    'Employee' => 'success',
                                    'Student' => 'success',
                                    default => 'blue'
                                }),
                            TextEntry::make('all_permissions')
                                ->label('Permissions')
                                ->badge()
                                ->separator(' ')
                                ->color(fn (string $state): string => match ($state) {
                                    'Manage Users' => 'yellow',
                                    'Manage Job Orders' => 'warning',
                                    'Manage Venue Bookings' => 'warning',
                                    'Manage Sticker Applications' => 'warning',
                                    'Be Assigned to Job Orders' => 'purple',
                                    'Post Job Orders' => 'primary',
                                    'Book Venues' => 'primary',
                                    'Apply for Sticker' => 'primary',
                                    default => 'default-color',
                                }),
                        ])
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Get the authenticated user
        $authUser = auth()->user();

        // If the authenticated user is a Super Admin, return all users (no filtering)
        if ($authUser && $authUser->hasRole('Super Admin')) {
            return parent::getEloquentQuery(); // No filtering for Super Admin
        }

        // If the authenticated user is an Admin, exclude users with the "Super Admin" role
        if ($authUser && $authUser->hasRole('Admin')) {
            return parent::getEloquentQuery()->where(function ($query) {
                $query->whereHas('roles', function ($query) {
                    $query->whereNotIn('name', ['Super Admin']);
                })
                // Include users who have no roles
                ->orWhereDoesntHave('roles');
            });
        }

        // If the authenticated user is neither Admin nor Super Admin, exclude users with "Admin" or "Super Admin" roles
        return parent::getEloquentQuery()->where(function ($query) {
            $query->whereHas('roles', function ($query) {
                $query->whereNotIn('name', ['Admin', 'Super Admin']);
            })
            // Include users who have no roles
            ->orWhereDoesntHave('roles');
        });
    }
}
