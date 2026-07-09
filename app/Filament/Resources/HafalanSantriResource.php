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

    protected static ?string $navigationLabel = 'Data Input Hafalan';

    protected static ?string $modelLabel = 'Data Input Hafalan';

    protected static ?string $pluralModelLabel = 'Data Input Hafalan';

    protected static ?string $navigationGroup = 'Laporan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Input')
                    ->dateTime('d M Y H:i')
                    ->timezone('Asia/Jakarta')
                    ->sortable(),

                Tables\Columns\TextColumn::make('santri.noin')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('santri.nm')
                    ->label('Nama Santri')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jenjang.madin')
                    ->label('Jenjang')
                    ->sortable(),

                Tables\Columns\TextColumn::make('mkls')
                    ->label('Kelas')
                    ->formatStateUsing(fn($state) => "Kelas {$state}")
                    ->sortable(),

                Tables\Columns\TextColumn::make('dataHafalan.nama_hafalan')
                    ->label('Nama Hafalan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('dataHafalan.kriteria')
                    ->label('Kriteria')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Wajib' => 'danger',
                        'Sunnah' => 'success',
                        'Wisuda' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('tahunAjaran.nama_tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Filter Tanggal Input (Rentang)
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
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
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

                // Filter Jenjang
                Tables\Filters\SelectFilter::make('tkt')
                    ->label('Jenjang')
                    ->options(
                        Madin::pluck('madin', 'id')
                    ),

                // Filter Kelas
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

                // Filter Tahun Ajaran (Default: Aktif)
                Tables\Filters\SelectFilter::make('tahun_ajaran_id')
                    ->label('Tahun Ajaran')
                    ->options(
                        TahunAjaran::orderByDesc('id')
                            ->pluck('nama_tahun_ajaran', 'id')
                    )
                    ->default(fn() => TahunAjaran::getAktif()?->id),
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
            ->with(['santri', 'dataHafalan', 'tahunAjaran', 'jenjang']);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHafalanSantris::route('/'),
        ];
    }
}
