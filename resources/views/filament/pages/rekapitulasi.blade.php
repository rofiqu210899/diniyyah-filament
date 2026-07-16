<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap items-center gap-4">
            <x-filament::button 
                wire:click="openPreview" 
                icon="heroicon-m-eye" 
                size="lg"
                color="primary"
                class="shadow-sm transition hover:scale-[1.01]"
            >
            Unduh Rekap Per Tingkatan
            </x-filament::button>
            
            <x-filament::button 
                wire:click="downloadRekapMustahiqDirect" 
                icon="heroicon-m-user" 
                size="lg"
                color="primary"
                class="shadow-sm transition hover:scale-[1.01]"
            >
                Unduh Rekap Per Mustahiq
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
