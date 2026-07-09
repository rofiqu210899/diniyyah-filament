<x-filament-panels::page>
    <div class="space-y-4" style="font-size: 90%;">

        {{-- ========================================== --}}
        {{-- Card Filter --}}
        {{-- ========================================== --}}
        {{ $this->form }}

        {{-- Tombol Cari --}}
        <div class="flex items-center gap-3">
            <x-filament::button
                wire:click="cariRekap"
                icon="heroicon-m-magnifying-glass"
                size="sm"
                color="primary"
            >
                Cari Rekap
            </x-filament::button>

            @if($showHasil)
                <span class="text-xs text-gray-500 dark:text-gray-400 italic">
                    {{ count($hasilRekap) }} santri ditemukan
                </span>
            @endif
        </div>

        {{-- ========================================== --}}
        {{-- Card Hasil Rekap --}}
        {{-- ========================================== --}}
        @if($showHasil)
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

                {{-- Header Card --}}
                <div class="fi-section-header flex items-center justify-between gap-3 px-4 py-3 border-b border-gray-200 dark:border-white/10">
                    <div class="flex-1">
                        <h3 class="fi-section-header-heading text-sm font-semibold leading-5 text-gray-950 dark:text-white">
                            <x-heroicon-o-table-cells class="inline-block w-4 h-4 mr-1 -mt-0.5" />
                            Hasil Rekap Hafalan
                        </h3>
                        @if($filterLabel)
                            <p class="fi-section-header-description text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $filterLabel }}
                            </p>
                        @endif
                    </div>
                    @if(count($hasilRekap) > 0)
                        <div class="flex items-center gap-2">
                            <x-filament::button
                                wire:click="downloadExcel"
                                icon="heroicon-m-document-arrow-down"
                                size="xs"
                                color="success"
                            >
                                Unduh Excel
                            </x-filament::button>
                        </div>
                    @endif
                </div>

                {{-- Tabel --}}
                @if(count($hasilRekap) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th class="px-2 py-2 font-semibold text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-white/10 text-center whitespace-nowrap sticky left-0 bg-gray-50 dark:bg-gray-800 z-10" style="min-width: 36px;">
                                        No
                                    </th>
                                    <th class="px-2 py-2 font-semibold text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-white/10 whitespace-nowrap sticky left-[36px] bg-gray-50 dark:bg-gray-800 z-10" style="min-width: 80px;">
                                        NOIN
                                    </th>
                                    <th class="px-2 py-2 font-semibold text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-white/10 whitespace-nowrap sticky left-[116px] bg-gray-50 dark:bg-gray-800 z-10" style="min-width: 150px;">
                                        Nama
                                    </th>
                                    @if($jenisPendidikan === 'kurikulum')
                                        <th class="px-2 py-2 font-semibold text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-white/10 whitespace-nowrap sticky left-[266px] bg-gray-50 dark:bg-gray-800 z-10" style="min-width: 90px;">
                                            Madin
                                        </th>
                                    @endif
                                    @foreach($headerHafalan as $hafalan)
                                        <th class="px-1.5 py-2 font-semibold border-b border-gray-200 dark:border-white/10 text-center whitespace-nowrap {{ $hafalan['kriteria'] === 'Wajib' ? 'text-red-600 dark:text-red-400' : ($hafalan['kriteria'] === 'Sunnah' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400') }}" style="min-width: 36px;">
                                            <div class="flex flex-col items-center gap-0">
                                                <span class="text-[9px] uppercase tracking-wider text-gray-400 dark:text-gray-500 leading-none">
                                                    {{ $hafalan['tkt_label'] }} K{{ $hafalan['mkls'] }}
                                                </span>
                                                <span class="text-[10px] leading-tight mt-0.5" title="{{ $hafalan['nama_hafalan'] }} ({{ $hafalan['kriteria'] }})">
                                                    {{ Str::limit($hafalan['nama_hafalan'], 12) }}
                                                </span>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hasilRekap as $i => $row)
                                    @php
                                        $stickyBg = $i % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-900';
                                    @endphp
                                    <tr class="border-b border-gray-100 dark:border-white/5 hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors {{ $i % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50/30 dark:bg-gray-900/50' }}">
                                        <td class="px-2 py-1.5 text-center text-gray-500 dark:text-gray-400 sticky left-0 z-10 {{ $stickyBg }}">
                                            {{ $i + 1 }}
                                        </td>
                                        <td class="px-2 py-1.5 font-mono text-gray-700 dark:text-gray-300 sticky left-[36px] z-10 {{ $stickyBg }}">
                                            {{ $row['noin'] }}
                                        </td>
                                        <td class="px-2 py-1.5 font-medium text-gray-900 dark:text-white sticky left-[116px] z-10 {{ $stickyBg }}">
                                            {{ $row['nama'] }}
                                        </td>
                                        @if($jenisPendidikan === 'kurikulum')
                                            <td class="px-2 py-1.5 text-gray-600 dark:text-gray-400 sticky left-[266px] z-10 {{ $stickyBg }} whitespace-nowrap">
                                                {{ $row['madin_label'] ?? '-' }}
                                            </td>
                                        @endif
                                        @foreach($headerHafalan as $hafalan)
                                            <td class="px-1.5 py-1.5 text-center">
                                                @if($row['hafalan'][$hafalan['id']] ?? false)
                                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400">
                                                        <x-heroicon-m-check class="w-3 h-3" />
                                                    </span>
                                                @else
                                                    <span class="text-gray-300 dark:text-gray-600 font-medium">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer Summary --}}
                    <div class="px-4 py-2 border-t border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/5">
                        <div class="flex items-center gap-3 text-[10px] text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-1">
                                <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400">
                                    <x-heroicon-m-check class="w-2.5 h-2.5" />
                                </span>
                                Tuntas
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <span class="text-gray-300 dark:text-gray-600 font-medium">—</span>
                                Belum Tuntas
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <span class="w-2.5 h-2.5 rounded-sm bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700"></span>
                                <span class="text-red-600 dark:text-red-400">Wajib</span>
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <span class="w-2.5 h-2.5 rounded-sm bg-emerald-100 dark:bg-emerald-900/30 border border-emerald-300 dark:border-emerald-700"></span>
                                <span class="text-emerald-600 dark:text-emerald-400">Sunnah</span>
                            </span>
                             <span class="inline-flex items-center gap-1">
                                 <span class="w-2.5 h-2.5 rounded-sm bg-amber-100 dark:bg-amber-900/30 border border-amber-300 dark:border-amber-700"></span>
                                 <span class="text-amber-600 dark:text-amber-400">Wisuda</span>
                             </span>
                        </div>
                    </div>

                @else
                    {{-- Empty State --}}
                    <div class="flex flex-col items-center justify-center py-10 px-6">
                        <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center mb-3">
                            <x-heroicon-o-document-magnifying-glass class="w-6 h-6 text-gray-400 dark:text-gray-500" />
                        </div>
                        <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">Data tidak ditemukan</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Tidak ada santri yang sesuai dengan filter yang dipilih.</p>
                    </div>
                @endif

            </div>
        @endif

    </div>
</x-filament-panels::page>
