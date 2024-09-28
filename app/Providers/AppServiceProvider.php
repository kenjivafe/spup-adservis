<?php

namespace App\Providers;

use App\Filament\Widgets\UniversityInfoWidget;
use App\Models\Booking;
use App\Models\JobOrder;
use App\Models\ParkingStickerApplication;
use App\Observers\BookingObserver;
use App\Observers\JobOrderObserver;
use App\Observers\ParkingStickerApplicationObserver;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // if (app()->environment('local')) {
        //     URL::forceScheme('https');
        // }
        JobOrder::observe(JobOrderObserver::class);
        Booking::observe(BookingObserver::class);
        ParkingStickerApplication::observe(ParkingStickerApplicationObserver::class);
        Filament::registerWidgets([
            UniversityInfoWidget::class,
        ]);
    }
}
