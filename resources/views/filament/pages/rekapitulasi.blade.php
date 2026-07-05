<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap items-center gap-4">
            <x-filament::button 
                wire:click="downloadRekapPertingkatan" 
                icon="heroicon-m-document-arrow-down" 
                size="lg"
                color="primary"
                class="shadow-sm transition hover:scale-[1.01]"
            >
                Unduh Rekap Per Tingkatan (Excel)
            </x-filament::button>
            
            <x-filament::button 
                type="button" 
                icon="heroicon-m-user" 
                size="lg"
                color="gray"
                disabled
                class="opacity-60 cursor-not-allowed"
            >
                Unduh Rekap Per Mustahiq (Segera Hadir)
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
