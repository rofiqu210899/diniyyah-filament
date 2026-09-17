<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SantriTuntasResource\Pages;
use App\Models\DataHafalan;
use App\Models\HafalanSantri;
use App\Models\Madin;
use App\Models\Mustahiq;
use App\Models\SantriTuntas;
use App\Models\TahunAjaran;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SantriTuntasResource extends Resource
{
    protected static ?string $model = SantriTuntas::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Santri Tuntas';

    protected static ?string $modelLabel = 'Santri Tuntas';

    protected static ?string $pluralModelLabel = 'Santri Tuntas';

    protected static ?string $navigationGroup = 'Sertifikat';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function getFilterTahunAjaranId($livewire = null): ?int
    {
        if ($livewire && isset($livewire->tableFilters['tahun_ajaran_id']['value']) && filled($livewire->tableFilters['tahun_ajaran_id']['value'])) {
            return (int) $livewire->tableFilters['tahun_ajaran_id']['value'];
        }

        if (request()->filled('tableFilters.tahun_ajaran_id.value')) {
            return (int) request()->input('tableFilters.tahun_ajaran_id.value');
        }

        return TahunAjaran::getAktif()?->id;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('noin')
                    ->label('NIS')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('nm')
                    ->label('NAMA LENGKAP')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jk')
                    ->label('JENIS KELAMIN')
                    ->formatStateUsing(fn($state) => match ((int) $state) {
                        1 => 'Putra',
                        2 => 'Putri',
                        default => '-',
                    })
                    ->badge()
                    ->color(fn($state) => match ((int) $state) {
                        1 => 'info',
                        2 => 'pink',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('kelas_diniyyah')
                    ->label('KELAS DINIYYAH')
                    ->getStateUsing(function ($record, $livewire) {
                        static $classCache = [];
                        $taId = static::getFilterTahunAjaranId($livewire);
                        $key = "{$record->id}_{$taId}";

                        if (!array_key_exists($key, $classCache)) {
                            $snapshot = HafalanSantri::where('santri_id', $record->id)
                                ->where('tahun_ajaran_id', $taId)
                                ->with('jenjang')
                                ->first();

                            $mkls = $snapshot?->mkls ?? $record->mkls;
                            $jenjang = $snapshot?->jenjang?->madin ?? $record->madin?->madin;
                            $classCache[$key] = "{$mkls} {$record->mbag} {$jenjang}";
                        }

                        return $classCache[$key];
                    })
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('unitSekolah.unit')
                    ->label('UNIT SEKOLAH')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('mustahiq')
                    ->label('MUSTAHIQ / WALI KELAS')
                    ->getStateUsing(function ($record, $livewire) {
                        static $mustahiqCache = [];
                        $taId = static::getFilterTahunAjaranId($livewire);

                        $snapshot = HafalanSantri::where('santri_id', $record->id)
                            ->where('tahun_ajaran_id', $taId)
                            ->first();

                        $mkls = $snapshot?->mkls ?? $record->mkls;
                        $tkt = $snapshot?->tkt ?? $record->tkt;
                        $key = "{$mkls}_{$record->mbag}_{$tkt}_{$record->jk}_{$taId}";

                        if (!array_key_exists($key, $mustahiqCache)) {
                            $mustahiq = Mustahiq::where('mkls', $mkls)
                                ->where('mbag', $record->mbag)
                                ->where('tkt', $tkt)
                                ->where('jk', $record->jk)
                                ->where('tahun_ajaran_id', $taId)
                                ->value('nama_mustahiq');
                            $mustahiqCache[$key] = $mustahiq ?? '-';
                        }

                        return $mustahiqCache[$key];
                    })
                    ->searchable(false),

                Tables\Columns\TextColumn::make('status_wajib')
                    ->label('HAFALAN WAJIB')
                    ->default('Tuntas')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-check-circle'),

                Tables\Columns\TextColumn::make('status_sunnah')
                    ->label('HAFALAN SUNNAH')
                    ->getStateUsing(function ($record, $livewire) {
                        static $cacheSunnah = [];
                        $taId = static::getFilterTahunAjaranId($livewire);

                        $snapshot = HafalanSantri::where('santri_id', $record->id)
                            ->where('tahun_ajaran_id', $taId)
                            ->first();

                        $mkls = $snapshot?->mkls ?? $record->mkls;
                        $tkt = $snapshot?->tkt ?? $record->tkt;
                        $key = "{$record->id}_{$mkls}_{$tkt}_{$taId}";

                        if (!array_key_exists($key, $cacheSunnah)) {
                            $count = HafalanSantri::where('santri_id', $record->id)
                                ->where('tahun_ajaran_id', $taId)
                                ->where('tkt', $tkt)
                                ->where('mkls', $mkls)
                                ->whereHas('dataHafalan', fn($q) => $q->where('kriteria', 'Sunnah'))
                                ->count();
                            $cacheSunnah[$key] = $count;
                        }

                        return $cacheSunnah[$key] . ' Selesai';
                    })
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-check-circle'),

                Tables\Columns\TextColumn::make('status_kelulusan')
                    ->label('STATUS')
                    ->default('LULUS & TUNTAS')
                    ->badge()
                    ->color('warning')
                    ->weight('bold'),
            ])
            ->defaultSort('nm', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('tahun_ajaran_id')
                    ->label('Tahun Ajaran')
                    ->options(
                        TahunAjaran::orderByDesc('id')->pluck('nama_tahun_ajaran', 'id')
                    )
                    ->default(fn() => TahunAjaran::getAktif()?->id)
                    ->selectablePlaceholder(false)
                    ->query(function (Builder $query, array $data): Builder {
                        $taId = !empty($data['value']) ? (int)$data['value'] : TahunAjaran::getAktif()?->id;

                        if (!$taId) {
                            return $query->whereRaw('1 = 0');
                        }

                        return $query->whereExists(function ($sub) use ($taId) {
                            $sub->selectRaw(1)
                                ->from('hafalan_santris as hs_main')
                                ->whereColumn('hs_main.santri_id', 'bukuinduk.id')
                                ->where('hs_main.tahun_ajaran_id', $taId)
                                ->whereRaw("(
                                    SELECT COUNT(DISTINCT hs.data_hafalan_id) 
                                    FROM hafalan_santris hs 
                                    JOIN data_hafalans dh ON hs.data_hafalan_id = dh.id 
                                    WHERE hs.santri_id = hs_main.santri_id 
                                      AND hs.tahun_ajaran_id = hs_main.tahun_ajaran_id 
                                      AND hs.mkls = hs_main.mkls 
                                      AND hs.tkt = hs_main.tkt 
                                      AND dh.kriteria = 'Wajib'
                                ) >= (
                                    SELECT COUNT(*) 
                                    FROM data_hafalans dh 
                                    WHERE dh.mkls = hs_main.mkls 
                                      AND dh.tkt = hs_main.tkt 
                                      AND dh.kriteria = 'Wajib'
                                )")
                                ->whereRaw("(
                                    SELECT COUNT(*) 
                                    FROM data_hafalans dh 
                                    WHERE dh.mkls = hs_main.mkls 
                                      AND dh.tkt = hs_main.tkt 
                                      AND dh.kriteria = 'Wajib'
                                ) > 0")
                                ->whereRaw("(
                                    SELECT COUNT(DISTINCT hs.data_hafalan_id) 
                                    FROM hafalan_santris hs 
                                    JOIN data_hafalans dh ON hs.data_hafalan_id = dh.id 
                                    WHERE hs.santri_id = hs_main.santri_id 
                                      AND hs.tahun_ajaran_id = hs_main.tahun_ajaran_id 
                                      AND hs.mkls = hs_main.mkls 
                                      AND hs.tkt = hs_main.tkt 
                                      AND dh.kriteria = 'Sunnah'
                                ) >= 1");
                        });
                    }),

                Tables\Filters\SelectFilter::make('tkt')
                    ->label('Jenjang / Tingkat')
                    ->options(
                        Madin::whereNotIn('id', [4, 5, 6])->pluck('madin', 'id')
                    )
                    ->query(function (Builder $query, array $data, $livewire): Builder {
                        if (!filled($data['value'])) return $query;
                        $tkt = (int) $data['value'];
                        $taId = static::getFilterTahunAjaranId($livewire);

                        return $query->whereExists(function ($sub) use ($tkt, $taId) {
                            $sub->selectRaw(1)
                                ->from('hafalan_santris')
                                ->whereColumn('santri_id', 'bukuinduk.id')
                                ->where('tkt', $tkt);
                            if ($taId) {
                                $sub->where('tahun_ajaran_id', $taId);
                            }
                        });
                    }),

                Tables\Filters\SelectFilter::make('mkls')
                    ->label('Kelas Diniyyah')
                    ->options([
                        1 => 'Kelas 1',
                        2 => 'Kelas 2',
                        3 => 'Kelas 3',
                        4 => 'Kelas 4',
                        5 => 'Kelas 5',
                        6 => 'Kelas 6',
                    ])
                    ->query(function (Builder $query, array $data, $livewire): Builder {
                        if (!filled($data['value'])) return $query;
                        $mkls = (int) $data['value'];
                        $taId = static::getFilterTahunAjaranId($livewire);

                        return $query->whereExists(function ($sub) use ($mkls, $taId) {
                            $sub->selectRaw(1)
                                ->from('hafalan_santris')
                                ->whereColumn('santri_id', 'bukuinduk.id')
                                ->where('mkls', $mkls);
                            if ($taId) {
                                $sub->where('tahun_ajaran_id', $taId);
                            }
                        });
                    }),

                Tables\Filters\SelectFilter::make('mbag')
                    ->label('Bagian')
                    ->options(array_combine(range('A', 'Z'), range('A', 'Z')))
                    ->query(fn(Builder $query, array $data) => filled($data['value']) ? $query->where('mbag', $data['value']) : $query),

                Tables\Filters\SelectFilter::make('jk')
                    ->label('Jenis Kelamin')
                    ->options([
                        1 => 'Putra',
                        2 => 'Putri',
                    ])
                    ->query(fn(Builder $query, array $data) => filled($data['value']) ? $query->where('jk', $data['value']) : $query),
            ])
            ->headerActions([
                Tables\Actions\Action::make('cetak_kolektif')
                    ->label('Cetak Kolektif')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->tooltip('Cetak seluruh sertifikat santri yang sesuai filter saat ini')
                    ->url(function ($livewire) {
                        $filters = $livewire->tableFilters ?? [];
                        $taId = static::getFilterTahunAjaranId($livewire);
                        $tkt = $filters['tkt']['value'] ?? null;
                        $mkls = $filters['mkls']['value'] ?? null;
                        $mbag = $filters['mbag']['value'] ?? null;
                        $jk = $filters['jk']['value'] ?? null;

                        return route('sertifikat.cetak_kolektif', array_filter([
                            'tahun_ajaran_id' => $taId,
                            'tkt' => $tkt,
                            'mkls' => $mkls,
                            'mbag' => $mbag,
                            'jk' => $jk,
                        ]));
                    }, shouldOpenInNewTab: true),
            ])
            ->actions([
                // Tombol cetak sertifikat (icon saja)
                Tables\Actions\Action::make('cetak_sertifikat')
                    ->icon('heroicon-o-printer')
                    ->iconButton()
                    ->tooltip('Cetak Sertifikat')
                    ->color('primary')
                    ->url(function (SantriTuntas $record, $livewire) {
                        $taId = static::getFilterTahunAjaranId($livewire);

                        return route('sertifikat.cetak_single', [
                            'santri' => $record->id,
                            'tahun_ajaran_id' => $taId,
                        ]);
                    }, shouldOpenInNewTab: true),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('cetak_terpilih')
                    ->label('Cetak Sertifikat Terpilih')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->action(function (Collection $records, $livewire) {
                        $ids = $records->pluck('id')->implode(',');
                        $taId = static::getFilterTahunAjaranId($livewire);

                        $url = route('sertifikat.cetak_kolektif', [
                            'ids' => $ids,
                            'tahun_ajaran_id' => $taId,
                        ]);

                        return redirect()->away($url);
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['madin', 'unitSekolah', 'Funkelurahan', 'Funkecamatan', 'Funkabupaten', 'Funprovinsi']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSantriTuntas::route('/'),
        ];
    }
}
