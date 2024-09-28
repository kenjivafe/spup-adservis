<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Inventory extends Cluster
{
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Job Order Management';
}
