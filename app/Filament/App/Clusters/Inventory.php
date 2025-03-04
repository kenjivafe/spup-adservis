<?php

namespace App\Filament\App\Clusters;

use Filament\Clusters\Cluster;

class Inventory extends Cluster
{
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Job Orders';

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
}
