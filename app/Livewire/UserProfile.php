<?php

namespace App\Livewire;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Guava\FilamentClusters\Forms\Cluster;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Joaopaulolndev\FilamentEditProfile\Concerns\HasSort;
use Joaopaulolndev\FilamentEditProfile\Livewire\EditProfileForm;

class UserProfile extends EditProfileForm
{
    use InteractsWithForms;
    use HasSort;

    public ?array $data = [];

    protected static int $sort = 0;

    public function mount(): void
    {
        $this->user = $this->getUser();

        $this->userClass = get_class($this->user);

        $this->form->fill($this->user->only('avatar_url', 'name', 'surname', 'email', 'phone'));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('filament-edit-profile::default.profile_information'))
                    ->aside()
                    ->description(__('filament-edit-profile::default.profile_information_description'))
                    ->schema([
                        FileUpload::make('avatar_url')
                            ->columnSpan(2)
                            ->label(__('filament-edit-profile::default.avatar'))
                            ->avatar()
                            ->imageEditor()
                            ->directory(filament('filament-edit-profile')->getAvatarDirectory())
                            ->rules(filament('filament-edit-profile')->getAvatarRules())
                            ->hidden(! filament('filament-edit-profile')->getShouldShowAvatarForm()),
                        Cluster::make([
                            TextInput::make('name')
                                ->prefix('FN')
                                ->placeholder('First Name')
                                ->columnSpan(1)
                                ->label(__('filament-edit-profile::default.name'))
                                ->required(),
                            TextInput::make('surname')
                                ->prefix('LN')
                                ->placeholder('Last Name')
                                ->columnSpan(1)
                                ->label(__('Surname'))
                                ->required(),
                        ])
                        ->label('Full Name')
                        ->columnSpan(2),
                        Cluster::make([
                            TextInput::make('email')
                                ->placeholder('Email Address')
                                ->prefixIcon('heroicon-s-envelope')
                                ->columnSpan(1)
                                ->label(__('filament-edit-profile::default.email'))
                                ->email()
                                ->required()
                                ->unique($this->userClass, ignorable: $this->user),
                            TextInput::make('phone')
                                ->placeholder('Phone Number')
                                ->prefixIcon('heroicon-s-phone')
                                ->columnSpan(1)
                                ->label(__('Contact Number'))
                                ->tel()
                                ->prefix('+63')
                                ->mask('999 999 9999')
                                ->required()
                        ])
                        ->label('Contact')
                        ->columnSpan(2),
                    ])
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
    }
}
