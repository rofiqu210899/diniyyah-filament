<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TahunAjaranResource\Pages;
use App\Models\TahunAjaran;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Table;

class TahunAjaranResource extends Resource
{
    protected static ?string $model = TahunAjaran::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Tahun Ajaran';

    protected static ?string $modelLabel = 'Tahun Ajaran';

    protected static ?string $pluralModelLabel = 'Tahun Ajaran';
    protected static ?string $navigationGroup = 'Pengaturan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Data Tahun Ajaran')

                    ->schema([

                        Select::make('nama_tahun_ajaran')
                            ->label('Tahun Ajaran')
                            ->options([
                                '2026-2027' => '2026-2027',
                                '2027-2028' => '2027-2028',
                                '2029-2030' => '2029-2030',
                            ])
                            ->required(),
                    ])
                    ->columns(1),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('nama_tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_aktif')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

            ])
            ->actions([

                Tables\Actions\Action::make('set_aktif')
                    ->label('Set Aktif')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Set Aktif')
                    ->modalDescription(fn(TahunAjaran $record) => "Apakah Anda yakin ingin mengaktifkan tahun ajaran \"{$record->nama_tahun_ajaran} - {$record->semester}\"? Tahun ajaran lain akan dinonaktifkan.")
                    ->hidden(fn(TahunAjaran $record) => $record->is_aktif)
                    ->action(function (TahunAjaran $record) {
                        $record->setAktif();

                        Notification::make()
                            ->title('Tahun ajaran berhasil diaktifkan')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('set_nonaktif')
                    ->label('Nonaktifkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Nonaktifkan')
                    ->modalDescription('Apakah Anda yakin ingin menonaktifkan tahun ajaran ini?')
                    ->visible(fn(TahunAjaran $record) => $record->is_aktif)
                    ->action(function (TahunAjaran $record) {
                        $record->update(['is_aktif' => false]);

                        Notification::make()
                            ->title('Tahun ajaran dinonaktifkan')
                            ->warning()
                            ->send();
                    }),

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
            'index' => Pages\ListTahunAjarans::route('/'),
            'create' => Pages\CreateTahunAjaran::route('/create'),
            'edit' => Pages\EditTahunAjaran::route('/{record}/edit'),
        ];
    }
}
