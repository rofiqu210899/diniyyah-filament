<div x-data="{ activeTab: 'ula' }" class="space-y-6">
    <!-- Tab Headers -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 p-1.5 rounded-xl shadow-sm gap-2">
        <button 
            type="button"
            @click="activeTab = 'ula'" 
            :class="activeTab === 'ula' ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-white shadow-sm font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 hover:dark:text-white'" 
            class="flex-1 py-2.5 px-4 text-sm rounded-lg transition duration-200"
        >
            Tingkat ULA
        </button>
        <button 
            type="button"
            @click="activeTab = 'wustho'" 
            :class="activeTab === 'wustho' ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-white shadow-sm font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 hover:dark:text-white'" 
            class="flex-1 py-2.5 px-4 text-sm rounded-lg transition duration-200"
        >
            Tingkat WUSTHO
        </button>
        <button 
            type="button"
            @click="activeTab = 'ulya'" 
            :class="activeTab === 'ulya' ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-white shadow-sm font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 hover:dark:text-white'" 
            class="flex-1 py-2.5 px-4 text-sm rounded-lg transition duration-200"
        >
            Tingkat ULYA
        </button>
    </div>

    <!-- Tab Contents -->
    <div class="max-h-[60vh] overflow-y-auto pr-2 space-y-4">
        <div x-show="activeTab === 'ula'" class="space-y-6">
            @include('filament.pages.rekapitulasi-preview-sheet', ['data' => $ula])
        </div>
        <div x-show="activeTab === 'wustho'" class="space-y-6" x-cloak>
            @include('filament.pages.rekapitulasi-preview-sheet', ['data' => $wustho])
        </div>
        <div x-show="activeTab === 'ulya'" class="space-y-6" x-cloak>
            @include('filament.pages.rekapitulasi-preview-sheet', ['data' => $ulya])
        </div>
    </div>
</div>
