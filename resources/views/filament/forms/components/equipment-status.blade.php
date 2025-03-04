<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <x-filament::badge
        color="{{ match ($getRecord()->status) {
            'Active' => 'primary',
            'Inactive' => 'warning',
            'Disposed' => 'danger',
        } }}"
        icon="{{ match ($getRecord()->status) {
            'Active' => 'heroicon-m-check-circle',
            'Inactive' => 'heroicon-m-archive-box-arrow-down',
            'Disposed' => 'heroicon-m-trash',
        } }}"
        class="">
        {{ $getRecord()->status }}
    </x-filament::badge>
</x-dynamic-component>
