<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class BookingCalendar extends Page
{
    protected static ?string $navigationGroup = 'Venue Bookings';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.booking-calendar';
}
