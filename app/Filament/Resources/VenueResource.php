<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VenueResource\Pages;
use App\Filament\Resources\VenueResource\RelationManagers;
use App\Livewire\VenueBookings;
use App\Models\User;
use App\Models\Venue;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Livewire;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Grid as TableGrid;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VenueResource extends Resource
{
    protected static ?string $model = Venue::class;

    protected static ?string $navigationLabel = 'Venues';

    protected static ?string $modelLabel = 'Venue';

    protected static ?string $navigationGroup = 'Venue Bookings';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),
                        Forms\Components\TextInput::make('capacity')
                        ->required()
                        ->columnSpan(2),
                        Forms\Components\Select::make('facilitator')
                        ->required()
                        ->native(false)
                        ->columnSpan(2)->options(function () {
                            return User::role('Facilitator')->pluck('full_name', 'id');
                        }),
                        Forms\Components\MarkdownEditor::make('description')
                        ->required()
                        ->columnSpan(2),
                    ])->columnSpan(1) ->columns(2),
                    Forms\Components\Section::make('Venue Images')
                        ->description('Add 2 images for a venue for best user experience')
                        ->icon('heroicon-m-photo')
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('venue_image')
                                ->multiple()
                                ->minFiles(2)
                                ->maxFiles(2)
                                ->reorderable()
                                ->label('')
                                ->image()
                                ->columnSpan(1)
                                ->collection(function (Venue $record) {
                                    return $record->name . '_images';
                                }),
                    ])->columnSpan(1)
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                TableGrid::make()
                ->columns(1)
                ->schema([
                    SpatieMediaLibraryImageColumn::make('venue_image')
                        ->collection(function (Venue $record) {
                            return $record->name . '_images';
                        })
                        ->limit(1)
                        ->extraImgAttributes(['class' => 'w-full rounded'])
                        ->defaultImageUrl(asset('/images/placeholder.png'))
                        ->height('275px'),
                    Tables\Columns\TextColumn::make('name')
                        ->weight(FontWeight::ExtraBold)
                        ->size(TextColumnSize::Large),
                ]),
            ])
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->recordAction(Tables\Actions\ViewAction::class)
            // // ->bulkActions([
            // //     // Tables\Actions\BulkActionGroup::make([
            // //     //     Tables\Actions\DeleteBulkAction::make(),
            // //     ]),
            // ])
            ;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Venue Details')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Section::make([])
                                    ->columns(6)
                                    ->schema([
                                        Group::make([
                                            TextEntry::make('created_at')
                                                ->color('gray')
                                                ->label('')
                                                ->dateTime('\D\a\t\e \P\o\s\t\e\d\:\ m/d/Y g:iA'),
                                            TextEntry::make('capacity')
                                                ->color('gray')
                                                ->numeric(),
                                            TextEntry::make('facilitator.full_name')
                                                ->color('gray'),
                                            TextEntry::make('description')
                                                ->markdown()
                                                ->color('gray')
                                                ->label('Venue Description'),
                                        ])->columnSpan(['default'=>6, 'sm'=>6, 'md'=>6, 'lg'=>2, 'xl'=>2, '2xl'=>2]),
                                        Group::make([
                                            SpatieMediaLibraryImageEntry::make('venue_image')
                                                ->collection(function (Venue $record) {
                                                    return $record->name . '_images';
                                                })
                                                ->label('')
                                                ->defaultImageUrl(asset('/images/placeholder.png'))
                                                ->extraImgAttributes(['class' => 'w-full rounded'])
                                                ->height(324)
                                                // ->width('auto'),
                                        ])
                                        ->columnStart(['default'=>1, 'sm'=>1, 'md'=>1, 'lg'=>3, 'xl'=>3, '2xl'=>3])
                                        ->columnSpan(['default'=>6, 'sm'=>6, 'md'=>6, 'lg'=>4, 'xl'=>4, '2xl'=>4]),
                                ]),
                            ]),
                        Tabs\Tab::make('Venue Bookings')
                            ->icon('heroicon-m-calendar-days')
                            ->schema([
                                Livewire::make(VenueBookings::class)
                            ]),
                    ])->contained(false)
            ])->columns(1);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVenues::route('/'),
            'create' => Pages\CreateVenue::route('/create'),
            'view' => Pages\ViewVenue::route('/{record}'),
            'edit' => Pages\EditVenue::route('/{record}/edit'),
        ];
    }
}
