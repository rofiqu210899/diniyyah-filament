<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MustahiqResource\Pages;
use App\Filament\Resources\MustahiqResource\RelationManagers;
use App\Models\Madin;
use App\Models\Mustahiq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MustahiqResource extends Resource
{
    protected static ?string $model = Mustahiq::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Data Mustahiq')

                    ->schema([

                        Forms\Components\TextInput::make('nama_mustahiq')
                            ->label('Nama Mustahiq')
                            ->required()
                            ->maxLength(100),

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

                        Forms\Components\Select::make('mbag')
                            ->label('Bagian')
                            ->options([
                                'A' => 'A',
                                'B' => 'B',
                                'C' => 'C',
                                'D' => 'D',
                                'E' => 'E',
                                'F' => 'F',
                                'G' => 'G',
                                'H' => 'H',
                                'I' => 'I',
                                'J' => 'J',
                                'K' => 'K',
                                'L' => 'L',
                                'M' => 'M',
                                'N' => 'N',
                                'O' => 'O',
                                'P' => 'P',
                                'Q' => 'Q',
                                'R' => 'R',
                                'S' => 'S',
                                'T' => 'T',
                                'U' => 'U',
                                'V' => 'V',
                                'W' => 'W',
                                'X' => 'X',
                                'Y' => 'Y',
                                'Z' => 'Z',
                            ])
                            ->searchable()
                            ->required(),

                    ])

                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('nama_mustahiq')
                    ->label('Mustahiq')
                    ->searchable(),

                Tables\Columns\TextColumn::make('madin.madin')
                    ->label('Jenjang')
                    ->sortable(),

                Tables\Columns\TextColumn::make('kelas')
                    ->label('Kelas')
                    ->getStateUsing(function ($record) {

                        return "{$record->mkls}{$record->mbag}";
                    }),

            ])
            ->defaultSort('tkt')
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
            'index' => Pages\ListMustahiqs::route('/'),
            'create' => Pages\CreateMustahiq::route('/create'),
            'edit' => Pages\EditMustahiq::route('/{record}/edit'),
        ];
    }
}
