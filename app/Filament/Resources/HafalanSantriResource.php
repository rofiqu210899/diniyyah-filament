<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HafalanSantriResource\Pages;
use App\Models\HafalanSantri;
use App\Models\Madin;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HafalanSantriResource extends Resource
{
    protected static ?string $model = HafalanSantri::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Data Kolektif';

    protected static ?string $modelLabel = 'Data Kolektif';

    protected static ?string $pluralModelLabel = 'Data Kolektif';

    protected static ?string $navigationGroup = 'Laporan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('TANGGAL INPUT')
                    ->dateTime('d M Y H:i')
                    ->timezone('Asia/Jakarta')
                    ->sortable(),

                Tables\Columns\TextColumn::make('santri.noin')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('santri.nm')
                    ->label('NAMA LENGKAP')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->label('JENIS KELAMIN')
                    ->getStateUsing(fn($record) => match ((int) $record->santri?->jk) {
                        1 => 'Putra',
                        2 => 'Putri',
                        default => '-',
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Putra' => 'info',
                        'Putri' => 'pink',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('ttl')
                    ->label('TTL')
                    ->getStateUsing(function ($record) {
                        $santri = $record->santri;
                        if (!$santri) return '-';
                        $bulanIndo = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
                        $namaBulan = $bulanIndo[(int) $santri->bln] ?? $santri->bln;
                        return "{$santri->tl}, {$santri->tlhr} {$namaBulan} {$santri->th}";
                    }),

                Tables\Columns\TextColumn::make('santri.nayah')
                    ->label('NAMA ORANG TUA')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('alamat')
                    ->label('ALAMAT')
                    ->getStateUsing(function ($record) {
                        $santri = $record->santri;
                        if (!$santri) return '-';
                        return "{$santri->Funkelurahan?->nama_kel}, {$santri->Funkecamatan?->nama_kec}, {$santri->Funkabupaten?->nama_kabkot}, {$santri->Funprovinsi?->nama_prov}";
                    }),

                Tables\Columns\TextColumn::make('kelas_diniyyah')
                    ->label('KELAS DINIYYAH')
                    ->getStateUsing(fn($record) => "{$record->mkls} {$record->santri?->mbag} {$record->jenjang?->madin}"),

                Tables\Columns\TextColumn::make('santri.unitSekolah.unit')
                    ->label('UNIT SEKOLAH')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_mustahiq')
                    ->label('NAMA MUSTAHIQ')
                    ->getStateUsing(function ($record) {
                        static $mustahiqCache = [];
                        $key = "{$record->mkls}_{$record->santri?->mbag}_{$record->tkt}_{$record->santri?->jk}_{$record->tahun_ajaran_id}";
                        if (!array_key_exists($key, $mustahiqCache)) {
                            $mustahiq = \App\Models\Mustahiq::where('mkls', $record->mkls)
                                ->where('mbag', $record->santri?->mbag)
                                ->where('tkt', $record->tkt)
                                ->where('jk', $record->santri?->jk)
                                ->where('tahun_ajaran_id', $record->tahun_ajaran_id)
                                ->first();
                            $mustahiqCache[$key] = $mustahiq?->nama_mustahiq ?? '-';
                        }
                        return $mustahiqCache[$key];
                    }),

                Tables\Columns\TextColumn::make('dataHafalan.nama_hafalan')
                    ->label('HAFALAN')
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([

                Tables\Filters\Filter::make('created_at')
                    ->label('Tanggal Input')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Dari: ' . Carbon::parse($data['created_from'])->format('d M Y'))
                                ->removeField('created_from');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Sampai: ' . Carbon::parse($data['created_until'])->format('d M Y'))
                                ->removeField('created_until');
                        }
                        return $indicators;
                    }),


                Tables\Filters\SelectFilter::make('tkt')
                    ->label('Jenjang')
                    ->options(
                        Madin::pluck('madin', 'id')
                    ),


                Tables\Filters\SelectFilter::make('mkls')
                    ->label('Kelas')
                    ->options([
                        1 => 'Kelas 1',
                        2 => 'Kelas 2',
                        3 => 'Kelas 3',
                        4 => 'Kelas 4',
                        5 => 'Kelas 5',
                        6 => 'Kelas 6',
                    ]),


                Tables\Filters\SelectFilter::make('tahun_ajaran_id')
                    ->label('Tahun Ajaran')
                    ->options(
                        TahunAjaran::orderByDesc('id')
                            ->pluck('nama_tahun_ajaran', 'id')
                    )
                    ->default(fn() => TahunAjaran::getAktif()?->id),


                Tables\Filters\SelectFilter::make('jk')
                    ->label('Jenis Kelamin')
                    ->options([
                        1 => 'Putra',
                        2 => 'Putri',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'])) return $query;
                        return $query->whereHas('santri', fn(Builder $q) =>
                            $q->where('jk', $data['value'])
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (blank($data['value'])) return null;
                        return 'Jenis Kelamin: ' . ($data['value'] == 1 ? 'Putra' : 'Putri');
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'santri.Funkelurahan',
                'santri.Funkecamatan',
                'santri.Funkabupaten',
                'santri.Funprovinsi',
                'santri.unitSekolah',
                'dataHafalan',
                'tahunAjaran',
                'jenjang'
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHafalanSantris::route('/'),
        ];
    }
}
