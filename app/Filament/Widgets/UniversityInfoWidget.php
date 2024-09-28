<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class UniversityInfoWidget extends Widget
{
    protected static string $view = 'filament.widgets.university-info-widget';

    protected function getHeading(): string
    {
        return 'University Website';
    }
}
