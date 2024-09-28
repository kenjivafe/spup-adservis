<x-filament-widgets::widget>
    <x-filament::section>
        <style>
            @font-face {
                font-family: 'Old English Text';
                src: url('{{ asset('fonts/OLDENGL.TTF') }}') format('truetype');
            }
        </style>
        <div class="flex items-center justify-between gap-x-3">
            <div class="flex items-center gap-x-3">
                <!-- University Image -->
                <img src="{{ asset('images/SPUPLogo.png') }}" alt="University Logo" class="h-11 w-11">

                <!-- Heading and Subheading -->
                <div>
                    <h2 class="text-xl" style="font-family: 'Old English Text', serif;">
                        St. Paul University Philippines
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Caritas, Veritas, Scientia.
                    </p>
                </div>
            </div>

            <!-- Link to University Website -->
            <div>
                <a href="https://www.spup.edu.ph" target="_blank" type="submit" wire:loading.attr="disabled">
                    <x-filament::button
                        tag="a"
                        href="https://www.spup.edu.ph"
                        target="_blank"
                        color="gray"
                        icon="heroicon-m-arrow-top-right-on-square"
                        labeled-from="sm"
                        tag="button"
                        type="submit"
                    >
                        {{'Visit site'}}
                    </x-filament::button>
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
