@if(!empty($this->hafalanList))
@php
$tahunAjaranAktif = \App\Models\TahunAjaran::getAktif();
$isAktifSelected = $tahunAjaranAktif && $this->selectedTahunAjaranId === $tahunAjaranAktif->id;
$allTahunAjaran = \App\Models\TahunAjaran::orderByDesc('id')->get();

$wajib = collect($this->hafalanList)->where('kriteria', 'Wajib');
$wajibIds = $wajib->pluck('id')->toArray();
$completedWajibCount = count(array_intersect($wajibIds, $this->hafalanChecked));
$totalWajibCount = count($wajibIds);
$wajibSelesai = $totalWajibCount > 0 ? $completedWajibCount >= $totalWajibCount : true;
@endphp

<div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="fi-section-header flex flex-col sm:flex-row sm:items-center gap-3 overflow-hidden px-6 py-4">
        <div class="grid flex-1 gap-y-1">
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                Daftar Hafalan Kelas Diniyyah
            </h3>
        </div>

        {{-- Dropdown Tahun Ajaran & Tombol Uncek --}}
        <div class="flex items-center gap-3">
            {{-- Tombol Mode Uncek (nanti bisa di-hide berdasarkan role) --}}
            @if($isAktifSelected)
            <button type="button"
                wire:click="toggleUncekMode"
                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-medium transition-colors
                        {{ $this->uncekMode
                            ? 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/10 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20'
                            : 'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-700' }}">
                @if($this->uncekMode)
                <x-heroicon-s-x-mark class="w-4 h-4" />
                Batal
                @else
                <x-heroicon-o-arrow-uturn-left class="w-4 h-4" />
                Edit Hafalan
                @endif
            </button>
            @endif

            <select
                wire:change="changeTahunAjaran($event.target.value)"
                class="fi-input block w-auto rounded-lg border-gray-300 bg-white text-sm text-gray-950 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-primary-500 dark:focus:ring-primary-500">
                @foreach($allTahunAjaran as $ta)
                <option value="{{ $ta->id }}" {{ $this->selectedTahunAjaranId == $ta->id ? 'selected' : '' }}>
                    {{ $ta->label_lengkap }}{{ $ta->is_aktif ? ' (Aktif)' : '' }}
                </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="fi-section-content-ctn border-t border-gray-200 dark:border-white/10">
        {{-- Info banner --}}
        @if(!$isAktifSelected)
        <div class="px-6 py-3 bg-blue-50 border-b border-blue-100 dark:bg-blue-500/10 dark:border-blue-500/20">
            <p class="text-sm font-medium text-blue-800 dark:text-blue-400 flex items-center gap-2">
                <x-heroicon-o-information-circle class="w-5 h-5 flex-shrink-0" />
                Anda sedang melihat data historis. Untuk mengubah hafalan, pilih tahun ajaran yang aktif.
            </p>
        </div>
        @endif

        @if($totalWajibCount > 0 && !$wajibSelesai)
        <div class="px-6 py-3 bg-orange-50 border-b border-orange-100 dark:bg-orange-500/10 dark:border-orange-500/20">
            <p class="text-sm font-medium text-orange-800 dark:text-orange-400 flex items-center gap-2">
                Hafalan wajib yang telah diselesaikan ({{ $completedWajibCount }}/{{ $totalWajibCount }} selesai).
            </p>
        </div>
        @endif

        @if($this->uncekMode && $isAktifSelected)
        <div class="px-6 py-3 bg-red-50 border-b border-red-100 dark:bg-red-500/10 dark:border-red-500/20">
            <p class="text-sm font-medium text-red-800 dark:text-red-400 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 flex-shrink-0" />
                Masuk mode edit
            </p>
        </div>
        @endif

        <div class="fi-ta-ctn overflow-hidden">
            <div class="fi-ta-content relative overflow-x-auto">
                <table class="fi-ta-table w-full text-left divide-y divide-gray-200 dark:divide-white/5">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 w-1">
                                <span class="sr-only">Status</span>
                            </th>
                            <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                                <span class="text-sm font-semibold text-gray-950 dark:text-white">Nama Hafalan</span>
                            </th>
                            <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                                <span class="text-sm font-semibold text-gray-950 dark:text-white">Keterangan</span>
                            </th>
                            <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                                <span class="text-sm font-semibold text-gray-950 dark:text-white">Kriteria</span>
                            </th>
                            @if($this->uncekMode && $isAktifSelected)
                            <th class="fi-ta-header-cell px-3 py-3.5 sm:last-of-type:pe-6 w-1">
                                <span class="sr-only">Aksi</span>
                            </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @foreach($this->hafalanList as $hafalan)
                        @php
                        $isChecked = in_array((int)$hafalan['id'], $this->hafalanChecked);
                        $isWajib = $hafalan['kriteria'] === 'Wajib';
                        $isDisabled = !$isWajib && !$wajibSelesai && !$isChecked;
                        @endphp
                        <tr class="fi-ta-row [@media(hover:hover)]:hover:bg-gray-50 dark:[@media(hover:hover)]:hover:bg-white/5 {{ $isChecked ? 'bg-green-50/50 dark:bg-green-500/5' : '' }}">
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 sm:first-of-type:ps-6 w-1 whitespace-nowrap">
                                <div class="flex items-center px-3 py-4">
                                    @if($isChecked)
                                    {{-- Sudah tercentang: tampilkan checkbox tercentang tapi tidak bisa diklik langsung --}}
                                    <div class="flex items-center justify-center w-5 h-5 rounded border bg-primary-600 border-primary-600 text-white cursor-default">
                                        <x-heroicon-s-check class="w-3.5 h-3.5" />
                                    </div>
                                    @elseif($isDisabled)
                                    <div class="flex items-center justify-center w-5 h-5 rounded border border-gray-300 bg-gray-100 dark:border-gray-600 dark:bg-gray-800 cursor-not-allowed" title="Selesaikan Wajib dahulu">
                                        <x-heroicon-s-lock-closed class="w-3 h-3 text-gray-400" />
                                    </div>
                                    @elseif(!$isAktifSelected)
                                    {{-- Tahun ajaran bukan aktif: read-only --}}
                                    <div class="flex items-center justify-center w-5 h-5 rounded border border-gray-300 bg-gray-100 dark:border-gray-600 dark:bg-gray-800 cursor-not-allowed">
                                    </div>
                                    @else
                                    {{-- Belum tercentang: bisa diklik dengan konfirmasi --}}
                                    <button type="button"
                                        wire:click="setHafalanTuntas({{ $hafalan['id'] }})"
                                        wire:confirm="Apakah Anda yakin ingin menandai hafalan '{{ $hafalan['nama_hafalan'] }}' sebagai tuntas?"
                                        class="flex items-center justify-center w-5 h-5 rounded border transition-colors border-gray-300 bg-white text-transparent dark:border-gray-600 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 dark:focus:ring-offset-gray-900 hover:border-primary-500">
                                    </button>
                                    @endif
                                </div>
                            </td>
                            <td class="fi-ta-cell p-0">
                                <div class="flex flex-col px-3 py-4">
                                    <span class="text-sm font-medium text-gray-950 dark:text-white {{ $isDisabled ? 'text-gray-400 dark:text-gray-500' : '' }}">
                                        {{ $hafalan['nama_hafalan'] }}
                                    </span>
                                </div>
                            </td>
                            <td class="fi-ta-cell p-0">
                                <div class="px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if($isChecked)
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20">
                                        <x-heroicon-s-check-circle class="w-4 h-4 mr-1" /> Tuntas
                                    </span>
                                    @else
                                    {{ !empty($hafalan['keterangan']) ? $hafalan['keterangan'] : '-' }}
                                    @endif
                                </div>
                            </td>
                            <td class="fi-ta-cell p-0 {{ !($this->uncekMode && $isAktifSelected) ? 'last-of-type:pe-1 sm:last-of-type:pe-6' : '' }} whitespace-nowrap w-1">
                                <div class="px-3 py-4">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $isWajib ? 'bg-red-50 text-red-700 ring-red-600/10 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20' : 'bg-blue-50 text-blue-700 ring-blue-600/10 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/20' }}">
                                        {{ $hafalan['kriteria'] }}
                                    </span>
                                </div>
                            </td>
                            @if($this->uncekMode && $isAktifSelected)
                            <td class="fi-ta-cell p-0 last-of-type:pe-1 sm:last-of-type:pe-6 whitespace-nowrap w-1">
                                <div class="px-3 py-4">
                                    @if($isChecked)
                                    <button type="button"
                                        wire:click="batalkanHafalan({{ $hafalan['id'] }})"
                                        wire:confirm="Apakah Anda yakin ingin membatalkan hafalan '{{ $hafalan['nama_hafalan'] }}'?"
                                        class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-700 bg-red-50 ring-1 ring-inset ring-red-600/10 hover:bg-red-100 transition-colors dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20 dark:hover:bg-red-500/20">
                                        <x-heroicon-o-x-circle class="w-4 h-4" />
                                        Uncek
                                    </button>
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                @if(count($this->hafalanList) === 0)
                <div class="fi-ta-empty-state px-6 py-12">
                    <div class="fi-ta-empty-state-content mx-auto grid max-w-lg justify-items-center text-center">
                        <div class="fi-ta-empty-state-icon-ctn mb-4 rounded-full bg-gray-100 p-3 dark:bg-gray-500/20">
                            <x-heroicon-o-x-mark class="fi-ta-empty-state-icon h-6 w-6 text-gray-500 dark:text-gray-400" />
                        </div>
                        <h4 class="fi-ta-empty-state-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                            Belum ada data hafalan
                        </h4>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif