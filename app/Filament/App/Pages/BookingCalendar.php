<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;

class BookingCalendar extends Page
{
    protected static ?string $navigationGroup = 'Venue Bookings';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.booking-calendar';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        // Check if the user has any of the required permissions
        return  $user->can('Book Venues') ||
                $user->can('Note Venue Bookings') ||
                $user->can('Approve Venue Bookings as Finance') ||
                $user->can('Be In-charge of Venues');
    }
}
