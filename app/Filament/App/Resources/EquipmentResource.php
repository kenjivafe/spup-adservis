<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\EquipmentResource\Pages;
use App\Filament\App\Resources\EquipmentResource\RelationManagers;
use App\Models\Equipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static ?string $navigationGroup = 'Job Orders';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return  $user->can('Post Job Orders');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(2)
            ->schema([
                TextEntry::make('code')->label('')->weight(FontWeight::ExtraBold)->size(TextEntrySize::Large),
                TextEntry::make('status')->label('')->badge()
                    ->color(fn (string $state): string => match ($state) {
                    'Active' => 'primary',
                    'Inactive' => 'warning',
                    'Disposed' => 'danger'}),
                TextEntry::make('equipmentCategory.name')->label('Category')->weight(FontWeight::Light)->color('gray')->columnSpan(2),
                TextEntry::make('equipmentBrand.name')->label('Brand')->weight(FontWeight::Light)->color('gray'),
                TextEntry::make('equipmentType.name')->label('Type')->weight(FontWeight::Light)->color('gray'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption('all')
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();

                if ($user) {
                    $unitId = $user->unit->id; // Assuming the user has a 'unit' relationship
                    $query->where('unit_id', $unitId);
                }
            })
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
                Tables\Actions\ViewAction::make()->modalWidth(MaxWidth::Large),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            // ->recordAction(Tables\Actions\ViewAction::class())
            ->recordUrl(null);
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
            // 'create' => Pages\CreateEquipment::route('/create'),
            // 'edit' => Pages\EditEquipment::route('/{record}/edit'),
        ];
    }
}
