<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingSertifikatResource\Pages;
use App\Models\SettingSertifikat;
use App\Models\TahunAjaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingSertifikatResource extends Resource
{
    protected static ?string $model = SettingSertifikat::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Setting Sertifikat';

    protected static ?string $modelLabel = 'Setting Sertifikat';

    protected static ?string $pluralModelLabel = 'Setting Sertifikat';

    protected static ?string $navigationGroup = 'Sertifikat';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Pengaturan Sertifikat')
                    ->persistTabInQueryString()
                    ->columnSpanFull()
                    ->tabs([

                        // TAB 1: KERTAS & TATA LETAK
                        Forms\Components\Tabs\Tab::make('Kertas & Tata Letak')
                            ->icon('heroicon-o-document')
                            ->schema([
                                Forms\Components\Section::make('Page Setup (Ukuran & Orientasi Kertas)')
                                    ->description('Atur dimensi kertas persis seperti pengaturan Page Setup di Microsoft Word.')
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('nama_setting')
                                            ->label('Nama Profil Pengaturan')
                                            ->required()
                                            ->default('Format Tanda Penghargaan')
                                            ->columnSpan(2),

                                        Forms\Components\Select::make('tahun_ajaran_id')
                                            ->label('Tahun Ajaran Khusus')
                                            ->options(
                                                TahunAjaran::orderByDesc('id')->pluck('nama_tahun_ajaran', 'id')
                                            )
                                            ->placeholder('Semua Tahun Ajaran (Default Global)')
                                            ->searchable()
                                            ->columnSpan(1)
                                            ->helperText('Kosongkan untuk menjadikannya pengaturan umum.'),

                                        Forms\Components\Select::make('paper_size')
                                            ->label('Ukuran Kertas')
                                            ->options([
                                                'F4' => 'F4 / Folio (215 × 330 mm)',
                                                'A4' => 'A4 (210 × 297 mm)',
                                                'Letter' => 'Letter (215.9 × 279.4 mm)',
                                                'Legal' => 'Legal (215.9 × 355.6 mm)',
                                                'Custom' => 'Ukuran Kustom (Custom Dimension)',
                                            ])
                                            ->default('F4')
                                            ->live()
                                            ->required(),

                                        Forms\Components\Select::make('orientation')
                                            ->label('Orientasi Kertas')
                                            ->options([
                                                'portrait' => 'Portrait (Tegak)',
                                                'landscape' => 'Landscape (Mendatar)',
                                            ])
                                            ->default('portrait')
                                            ->required(),

                                        Forms\Components\ColorPicker::make('bg_color')
                                            ->label('Warna Dasar Kertas')
                                            ->default('#ffffff'),

                                        Forms\Components\TextInput::make('custom_width')
                                            ->label('Lebar Kustom')
                                            ->numeric()
                                            ->suffix('mm')
                                            ->visible(fn(Get $get) => $get('paper_size') === 'Custom')
                                            ->required(fn(Get $get) => $get('paper_size') === 'Custom'),

                                        Forms\Components\TextInput::make('custom_height')
                                            ->label('Tinggi Kustom')
                                            ->numeric()
                                            ->suffix('mm')
                                            ->visible(fn(Get $get) => $get('paper_size') === 'Custom')
                                            ->required(fn(Get $get) => $get('paper_size') === 'Custom'),
                                    ]),

                                Forms\Components\Section::make('Margin Kertas (Margins)')
                                    ->description('Batas jarak tepi kertas ke konten sertifikat (dalam milimeter / mm).')
                                    ->columns(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('margin_top')
                                            ->label('Margin Atas (Top)')
                                            ->numeric()
                                            ->default(50)
                                            ->suffix('mm')
                                            ->helperText('Beri ruang yang cukup jika kertas menggunakan kop tercetak.')
                                            ->required(),

                                        Forms\Components\TextInput::make('margin_right')
                                            ->label('Margin Kanan (Right)')
                                            ->numeric()
                                            ->default(25)
                                            ->suffix('mm')
                                            ->required(),

                                        Forms\Components\TextInput::make('margin_bottom')
                                            ->label('Margin Bawah (Bottom)')
                                            ->numeric()
                                            ->default(20)
                                            ->suffix('mm')
                                            ->required(),

                                        Forms\Components\TextInput::make('margin_left')
                                            ->label('Margin Kiri (Left)')
                                            ->numeric()
                                            ->default(25)
                                            ->suffix('mm')
                                            ->required(),
                                    ]),
                            ]),

                        // TAB 2: JUDUL & FORMAT BIODATA
                        Forms\Components\Tabs\Tab::make('Judul & Isi Teks')
                            ->icon('heroicon-o-pencil-square')
                            ->schema([
                                Forms\Components\Section::make('Judul & Penomoran Otomatis')
                                    ->description('Atur format nomor surat dan posisi nomor urut siswa otomatis.')
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('judul_sertifikat')
                                            ->label('Judul Dokumen')
                                            ->default('Tanda Penghargaan')
                                            ->columnSpan(3)
                                            ->required(),

                                        Forms\Components\TextInput::make('nomor_format_template')
                                            ->label('Format / Template Nomor Sertifikat')
                                            ->default('51.2/[nomor]/E.24/MADINA/II/2026')
                                            ->helperText('Gunakan [nomor] atau [urut] untuk posisi nomor urut otomatis. Variabel lain: [tahun], [bulan_romawi], [nis]')
                                            ->columnSpan(2)
                                            ->required(),

                                        Forms\Components\TextInput::make('nomor_start_sequence')
                                            ->label('Nomor Awal (Mulai Dari)')
                                            ->numeric()
                                            ->default(649)
                                            ->helperText('Contoh: 1, 100, atau 649 (akan bertambah 1 per santri)')
                                            ->required(),

                                        Forms\Components\Select::make('nomor_digit_padding')
                                            ->label('Format Digit Angka')
                                            ->options([
                                                0 => 'Tanpa Awalan Nol (1, 2, 3 ... 649, 650)',
                                                2 => '2 Digit (01, 02, 03 ...)',
                                                3 => '3 Digit (001, 002, 003 ...)',
                                                4 => '4 Digit (0001, 0002, 0003 ...)',
                                            ])
                                            ->default(0)
                                            ->columnSpan(1)
                                            ->required(),

                                        Forms\Components\TextInput::make('atas_prestasinya')
                                            ->label('Teks "Atas Prestasinya"')
                                            ->default('Hafal ALFIYYAH 1002 NADHOM')
                                            ->helperText('Bisa diisi manual atau gunakan placeholder: [hafalan], [kelas], [jenjang], [tahun_ajaran]')
                                            ->columnSpan(2),
                                    ]),

                                Forms\Components\Section::make('Tipografi & Ukuran Font (Word-like Typography)')
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\Select::make('font_family')
                                            ->label('Font Family (Gaya Font)')
                                            ->options([
                                                'Times New Roman' => 'Times New Roman (Serif Formal Tradisional)',
                                                'Arial' => 'Arial (Sans-serif Bersih)',
                                                'Georgia' => 'Georgia (Serif Elegan)',
                                                'Garamond' => 'Garamond (Serif Klasik)',
                                                'Playfair Display' => 'Playfair Display (Serif Premium)',
                                                'Roboto' => 'Roboto (Sans-serif Modern)',
                                            ])
                                            ->default('Times New Roman')
                                            ->required(),

                                        Forms\Components\TextInput::make('line_spacing')
                                            ->label('Line Spacing (Jarak Baris)')
                                            ->numeric()
                                            ->step(0.05)
                                            ->minValue(0.8)
                                            ->maxValue(3.0)
                                            ->default(1.2)
                                            ->suffix('x')
                                            ->required(),

                                        Forms\Components\TextInput::make('font_size_title')
                                            ->label('Ukuran Font Judul')
                                            ->numeric()
                                            ->default(20)
                                            ->suffix('pt')
                                            ->required(),

                                        Forms\Components\TextInput::make('font_size_body')
                                            ->label('Ukuran Font Biodata / Isi')
                                            ->numeric()
                                            ->default(12)
                                            ->suffix('pt')
                                            ->required(),

                                        Forms\Components\TextInput::make('font_size_footer')
                                            ->label('Ukuran Font Tanda Tangan')
                                            ->numeric()
                                            ->default(11)
                                            ->suffix('pt')
                                            ->required(),
                                    ]),
                            ]),

                        // TAB 3: TANGGAL & KOTAK FOTO
                        Forms\Components\Tabs\Tab::make('Tanggal & Foto')
                            ->icon('heroicon-o-camera')
                            ->schema([
                                Forms\Components\Section::make('Tempat & Tanggal Terbit')
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('tempat_terbit')
                                            ->label('Kota / Tempat')
                                            ->default('Blokagung')
                                            ->required(),

                                        Forms\Components\TextInput::make('tanggal_hijriah')
                                            ->label('Tanggal Hijriah')
                                            ->default('6 Romadhon 1447 H.')
                                            ->placeholder('Contoh: 6 Romadhon 1447 H.')
                                            ->required(),

                                        Forms\Components\Toggle::make('use_current_date')
                                            ->label('Gunakan Tanggal Masehi Saat Ini')
                                            ->default(true)
                                            ->live(),

                                        Forms\Components\DatePicker::make('tanggal_terbit')
                                            ->label('Tanggal Masehi Khusus')
                                            ->visible(fn(Get $get) => ! (bool) $get('use_current_date')),
                                    ]),

                                Forms\Components\Section::make('Kotak Pas Foto')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\Toggle::make('show_foto_box')
                                            ->label('Tampilkan Kotak Pas Foto')
                                            ->default(true)
                                            ->live(),

                                        Forms\Components\TextInput::make('foto_box_label')
                                            ->label('Label Kotak Foto')
                                            ->default('Foto 3x4')
                                            ->visible(fn(Get $get) => (bool) $get('show_foto_box')),
                                    ]),
                            ]),

                        // TAB 4: PENANDATANGAN & STEMPEL
                        Forms\Components\Tabs\Tab::make('Penandatangan')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Forms\Components\Section::make('Penandatangan 1 (Atas Kanan / Posisi Samping Foto)')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\Toggle::make('show_ttd_1')
                                            ->label('Tampilkan Penandatangan 1')
                                            ->default(true)
                                            ->live()
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('ttd_1_jabatan')
                                            ->label('Jabatan')
                                            ->default('PKM. Muhafadhoh')
                                            ->visible(fn(Get $get) => (bool) $get('show_ttd_1')),

                                        Forms\Components\TextInput::make('ttd_1_nama')
                                            ->label('Nama Penandatangan')
                                            ->default('ANDIKO DWI SAPUTRA, S.T.T')
                                            ->visible(fn(Get $get) => (bool) $get('show_ttd_1')),

                                        Forms\Components\FileUpload::make('ttd_1_image_path')
                                            ->label('Scan TTD 1 (PNG Transparan)')
                                            ->disk('public')
                                            ->directory('sertifikat/ttd')
                                            ->visibility('public')
                                            ->image()
                                            ->imagePreviewHeight('70')
                                            ->visible(fn(Get $get) => (bool) $get('show_ttd_1')),
                                    ]),

                                Forms\Components\Section::make('Penandatangan Bawah (Bagian "Mengetahui,")')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('label_mengetahui')
                                            ->label('Teks Pengantar Mengetahui')
                                            ->default('Mengetahui,')
                                            ->columnSpanFull(),

                                        // TTD 2 (Bawah Kiri)
                                        Forms\Components\Fieldset::make('Penandatangan Bawah Kiri')
                                            ->schema([
                                                Forms\Components\Toggle::make('show_ttd_2')
                                                    ->label('Tampilkan')
                                                    ->default(true)
                                                    ->live(),

                                                Forms\Components\TextInput::make('ttd_2_jabatan')
                                                    ->label('Jabatan')
                                                    ->default('Kabid. Pendidikan dan Pengajaran')
                                                    ->visible(fn(Get $get) => (bool) $get('show_ttd_2')),

                                                Forms\Components\TextInput::make('ttd_2_nama')
                                                    ->label('Nama Lengkap')
                                                    ->default('DR. KH. ABDUL KHOLIQ SYAFA\'AT, MA.')
                                                    ->visible(fn(Get $get) => (bool) $get('show_ttd_2')),

                                                Forms\Components\FileUpload::make('ttd_2_image_path')
                                                    ->label('Scan TTD (PNG Transparan)')
                                                    ->disk('public')
                                                    ->directory('sertifikat/ttd')
                                                    ->visibility('public')
                                                    ->image()
                                                    ->imagePreviewHeight('70')
                                                    ->visible(fn(Get $get) => (bool) $get('show_ttd_2')),
                                            ])
                                            ->columnSpan(1),

                                        // TTD 3 (Bawah Kanan)
                                        Forms\Components\Fieldset::make('Penandatangan Bawah Kanan')
                                            ->schema([
                                                Forms\Components\Toggle::make('show_ttd_3')
                                                    ->label('Tampilkan')
                                                    ->default(true)
                                                    ->live(),

                                                Forms\Components\TextInput::make('ttd_3_jabatan')
                                                    ->label('Jabatan')
                                                    ->default('Kepala Madrasah')
                                                    ->visible(fn(Get $get) => (bool) $get('show_ttd_3')),

                                                Forms\Components\TextInput::make('ttd_3_nama')
                                                    ->label('Nama Lengkap')
                                                    ->default('INDY NAJMU TSAQIB, S.Pd.I')
                                                    ->visible(fn(Get $get) => (bool) $get('show_ttd_3')),

                                                Forms\Components\FileUpload::make('ttd_3_image_path')
                                                    ->label('Scan TTD (PNG Transparan)')
                                                    ->disk('public')
                                                    ->directory('sertifikat/ttd')
                                                    ->visibility('public')
                                                    ->image()
                                                    ->imagePreviewHeight('70')
                                                    ->visible(fn(Get $get) => (bool) $get('show_ttd_3')),

                                                Forms\Components\FileUpload::make('stempel_image_path')
                                                    ->label('Scan Stempel (PNG Transparan)')
                                                    ->disk('public')
                                                    ->directory('sertifikat/stempel')
                                                    ->visibility('public')
                                                    ->image()
                                                    ->imagePreviewHeight('70')
                                                    ->visible(fn(Get $get) => (bool) $get('show_ttd_3')),
                                            ])
                                            ->columnSpan(1),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_setting')
                    ->label('Profil Pengaturan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('tahunAjaran.nama_tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->placeholder('Semua (Default)')
                    ->badge()
                    ->color(fn($record) => $record->tahun_ajaran_id ? 'primary' : 'gray'),

                Tables\Columns\TextColumn::make('paper_size')
                    ->label('Kertas')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn($record) => "{$record->paper_size} (" . ucfirst($record->orientation) . ")"),

                Tables\Columns\TextColumn::make('judul_sertifikat')
                    ->label('Judul Dokumen')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('font_family')
                    ->label('Font Family')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y H:i')
                    ->timezone('Asia/Jakarta')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Pratinjau')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn(SettingSertifikat $record) => route('sertifikat.preview_setting', ['setting' => $record->id]), shouldOpenInNewTab: true),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettingSertifikats::route('/'),
            'create' => Pages\CreateSettingSertifikat::route('/create'),
            'edit' => Pages\EditSettingSertifikat::route('/{record}/edit'),
        ];
    }
}
