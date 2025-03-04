<?php

namespace App\Providers\Filament;

use App\Filament\AvatarProviders\BoringAvatarsProvider;
use App\Filament\Widgets\UniversityInfoWidget;
use App\Http\Middleware\RedirectIfNotFilamentAdmin;
use App\Http\Middleware\VerifyIsAdmin;
use App\Livewire\UserProfile;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Auth\EditProfile;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            // ->profile(EditProfile::class)
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn() => auth()->user()->full_name)
                    ->url(fn (): string => EditProfilePage::getUrl())
                    ->icon('heroicon-m-user-circle'),
                // MenuItem::make()
                // ->label('Admin')
                // ->icon('heroicon-o-cog-6-tooth')
                // ->url('/admin')
                // ->visible(fn () => auth()->user()->hasAnyRole(['Admin', 'Staff'])),
            ])
            ->colors([
                'danger' => Color::Rose,
                'gray' => Color::Neutral,
                'info' => Color::Blue,
                'primary' => Color::Green,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'yellow' => Color::Yellow,
                'purple' => Color::Purple,
                'blue' => Color::Blue,
            ])
            // ->defaultAvatarProvider(BoringAvatarsProvider::class)
            ->brandLogo(asset('images/AdServIS.svg'))
            ->favicon(asset('images/favicon.ico'))
            ->font('Noto Sans')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                UniversityInfoWidget::class,
            ])
            ->plugins([
                FilamentFullCalendarPlugin::make()
                    ->editable(false)
                    ->selectable()
                    ->timezone('local'),
                FilamentEditProfilePlugin::make()
                    // ->shouldShowAvatarForm(
                    //     value: true,
                    //     directory: 'avatars', // image will be stored in 'storage/app/public/avatars
                    //     rules: 'mimes:jpeg,png|max:1024' //only accept jpeg and png files with a maximum size of 1MB
                    // )
                    ->shouldShowDeleteAccountForm(false)
                    ->shouldRegisterNavigation(false)
                    ->shouldShowEditProfileForm(false)
                    ->customProfileComponents([
                        UserProfile::class
                    ])
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                VerifyIsAdmin::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Job Order Management')
                    ->icon('heroicon-o-briefcase'),
                NavigationGroup::make()
                    ->label('Venue Bookings')
                    ->icon('heroicon-o-calendar-days'),
                NavigationGroup::make()
                    ->label('Parking Sticker Applications')
                    ->icon('heroicon-o-check-badge'),
                NavigationGroup::make()
                    ->label('User Management')
                    ->icon('heroicon-o-user-group'),
            ])
            ->databaseNotifications();
    }
}
