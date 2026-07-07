<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataHafalanResource\Pages;
use App\Models\DataHafalan;
use App\Models\Madin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DataHafalanResource extends Resource
{
    protected static ?string $model = DataHafalan::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Data Hafalan';

    protected static ?string $modelLabel = 'Data Hafalan';

    protected static ?string $pluralModelLabel = 'Data Hafalan';

    protected static ?string $navigationGroup = 'Pengaturan';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Data Hafalan Kelas')

                    ->schema([

                        Forms\Components\Select::make('tkt')
                            ->label('Jenjang')
                            ->options(
                                Madin::pluck('madin', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('mkls')
                            ->label('Kelas')
                            ->options([
                                1 => 'Kelas 1',
                                2 => 'Kelas 2',
                                3 => 'Kelas 3',
                                4 => 'Kelas 4',
                                5 => 'Kelas 5',
                                6 => 'Kelas 6',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('nama_hafalan')
                            ->label('Nama Hafalan')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Contoh: Juz Amma, Surat Al-Baqarah, dll'),

                        Forms\Components\Select::make('kriteria')
                            ->label('Kriteria')
                            ->options([
                                'Wajib' => 'Wajib',
                                'Sunnah' => 'Sunnah',
                            ])
                            ->default('Wajib')
                            ->required(),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('Keterangan tambahan (opsional)')
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('madin.madin')
                    ->label('Jenjang')
                    ->sortable(),

                Tables\Columns\TextColumn::make('mkls')
                    ->label('Kelas')
                    ->sortable()
                    ->formatStateUsing(fn($state) => "Kelas {$state}"),

                Tables\Columns\TextColumn::make('nama_hafalan')
                    ->label('Nama Hafalan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('kriteria')
                    ->label('Kriteria')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Wajib' => 'danger',
                        'Sunnah' => 'success',
                    }),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->toggleable(),

            ])
            ->defaultSort('tkt')
            ->groups([
                Tables\Grouping\Group::make('madin.madin')
                    ->label('Jenjang'),
            ])
            ->filters([

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

                Tables\Filters\SelectFilter::make('kriteria')
                    ->label('Kriteria')
                    ->options([
                        'Wajib' => 'Wajib',
                        'Sunnah' => 'Sunnah',
                    ]),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
            'index' => Pages\ListDataHafalans::route('/'),
            'create' => Pages\CreateDataHafalan::route('/create'),
            'edit' => Pages\EditDataHafalan::route('/{record}/edit'),
        ];
    }
}
