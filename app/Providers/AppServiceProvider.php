<?php

namespace App\Providers;

use App\Filament\Clusters\Inventory\Pages\InventoryOverview;
use App\Filament\Widgets\UniversityInfoWidget;
use App\Models\Booking;
use App\Models\JobOrder;
use App\Models\ParkingStickerApplication;
use App\Observers\BookingObserver;
use App\Observers\JobOrderObserver;
use App\Observers\ParkingStickerApplicationObserver;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

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

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): View => view('footer'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::GLOBAL_SEARCH_AFTER,
            fn (): View => view('topbar-date'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::SIDEBAR_NAV_END,
            fn (): string => '<br><br>',
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::CONTENT_END,
            fn (): string => '<br><br>',
        );

        Filament::registerPages([
            InventoryOverview::class,
        ]);
    }
}
