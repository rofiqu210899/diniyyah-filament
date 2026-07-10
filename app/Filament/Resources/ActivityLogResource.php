<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $modelLabel = 'Log Aktivitas';

    protected static ?string $pluralModelLabel = 'Log Aktivitas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Aktivitas')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('created_at')
                                    ->label('Waktu')
                                    ->disabled(),
                                Forms\Components\TextInput::make('user_name')
                                    ->label('Pelaku')
                                    ->disabled(),
                                Forms\Components\TextInput::make('ip_address')
                                    ->label('Alamat IP')
                                    ->disabled(),
                                Forms\Components\TextInput::make('action')
                                    ->label('Aksi')
                                    ->disabled(),
                                Forms\Components\TextInput::make('model_type')
                                    ->label('Tipe Model')
                                    ->disabled(),
                                Forms\Components\TextInput::make('model_id')
                                    ->label('ID Model')
                                    ->disabled(),
                            ]),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->disabled()
                            ->rows(2),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\KeyValue::make('before')
                                    ->label('Data Sebelum Perubahan')
                                    ->disabled()
                                    ->columnSpan(1),
                                Forms\Components\KeyValue::make('after')
                                    ->label('Data Sesudah Perubahan')
                                    ->disabled()
                                    ->columnSpan(1),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->timezone('Asia/Jakarta')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user_name')
                    ->label('Pelaku')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'CREATED' => 'success',
                        'UPDATED' => 'warning',
                        'DELETED' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('Alamat IP')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->label('Aksi')
                    ->options([
                        'CREATED' => 'Created',
                        'UPDATED' => 'Updated',
                        'DELETED' => 'Deleted',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Read-only audit log, no bulk deletes allowed
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
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
