<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ItemResource\Pages;
use App\Models\Category;
use App\Models\Item;
use App\Models\RoomArea;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\UnitRoom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Monitoring Inventaris';
    protected static ?string $modelLabel = 'Inventaris';
    protected static ?string $pluralModelLabel = 'Monitoring Inventaris (Seluruh Unit)';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Penempatan & Lokasi Fisik')
                ->description('Tentukan jurusan, ruang lab, dan area penempatan inventaris')
                ->icon('heroicon-o-map-pin')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('unit_id')
                        ->label('Jurusan / Unit')
                        ->options(Unit::where('aktif', true)->orderBy('urutan')->pluck('nama', 'id'))
                        ->required()
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(function (callable $set) {
                            $set('category_id', null);
                            $set('unit_room_id', null);
                            $set('room_area_id', null);
                        }),

                    Forms\Components\Select::make('unit_room_id')
                        ->label('Ruang / Lab')
                        ->placeholder('Pilih Lab / Ruang')
                        ->options(fn (callable $get) =>
                            $get('unit_id')
                                ? UnitRoom::where('unit_id', $get('unit_id'))->where('aktif', true)->orderBy('urutan')->orderBy('nama')->pluck('nama', 'id')
                                : []
                        )
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(fn (callable $set) => $set('room_area_id', null))
                        ->nullable(),

                    Forms\Components\Select::make('room_area_id')
                        ->label('Ruang & Area Lab')
                        ->placeholder('Pilih Area Kerja')
                        ->options(fn (callable $get) =>
                            $get('unit_room_id')
                                ? RoomArea::where('unit_room_id', $get('unit_room_id'))->where('aktif', true)->orderBy('urutan')->orderBy('nama')->pluck('nama', 'id')
                                : []
                        )
                        ->searchable()
                        ->nullable(),

                    Forms\Components\TextInput::make('rak_posisi')
                        ->label('Posisi Rak / Lemari / Meja')
                        ->placeholder('Contoh: Lemari A / Rak 2 / Meja Praktikum 3')
                        ->maxLength(100)
                        ->columnSpan(3),
                ]),

            Forms\Components\Section::make('Identitas Barang')
                ->icon('heroicon-o-archive-box')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('kode')
                        ->label('Kode Barang / Inventaris')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->placeholder('Bebas, contoh: SDR-01 atau OSC-COE-01')
                        ->helperText('Kode inventaris ditentukan mandiri oleh Kepala Lab.'),

                    Forms\Components\TextInput::make('nama')
                        ->label('Nama Barang')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),

                    Forms\Components\Select::make('category_id')
                        ->label('Kategori')
                        ->options(fn (callable $get) =>
                            $get('unit_id')
                                ? Category::where('unit_id', $get('unit_id'))->orderBy('urutan')->pluck('nama', 'id')
                                : Category::orderBy('nama')->pluck('nama', 'id')
                        )
                        ->searchable()
                        ->nullable(),

                    Forms\Components\Select::make('tipe_barang')
                        ->label('Tipe Barang')
                        ->options(Item::TIPE_LABELS)
                        ->required()
                        ->default('consumable')
                        ->native(false),

                    Forms\Components\Select::make('satuan')
                        ->label('Satuan')
                        ->options(Item::SATUAN_OPTIONS)
                        ->required()
                        ->default('pcs')
                        ->native(false),

                    Forms\Components\Textarea::make('deskripsi')
                        ->label('Deskripsi / Spesifikasi')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Harga & Supplier')
                ->icon('heroicon-o-currency-dollar')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('harga_satuan')
                        ->label('Harga Satuan Terakhir')
                        ->prefix('Rp')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Diisi otomatis saat penerimaan barang'),

                    Forms\Components\TextInput::make('harga_referensi')
                        ->label('Harga Referensi (untuk Pengajuan)')
                        ->prefix('Rp')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->required()
                        ->helperText('Harga yang dipakai untuk kalkulasi pengajuan anggaran'),

                    Forms\Components\Toggle::make('ppn_termasuk')
                        ->label('Harga sudah termasuk PPN?')
                        ->default(false),

                    Forms\Components\TextInput::make('stok_minimum')
                        ->label('Stok Minimum (Alert)')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Sistem akan alert jika stok mencapai nilai ini'),

                    Forms\Components\Select::make('supplier_id')
                        ->label('Supplier Utama')
                        ->options(Supplier::where('aktif', true)->orderBy('nama')->pluck('nama', 'id'))
                        ->searchable()
                        ->nullable()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('nama')->required(),
                            Forms\Components\TextInput::make('telepon'),
                            Forms\Components\TextInput::make('email'),
                        ])
                        ->createOptionUsing(fn (array $data) => Supplier::create($data)->id),

                    Forms\Components\Textarea::make('catatan_harga')
                        ->label('Catatan Harga')
                        ->rows(2),
                ]),

            Forms\Components\Section::make('Media & Status')
                ->icon('heroicon-o-photo')
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('foto')
                        ->label('Foto Barang')
                        ->image()
                        ->directory('items')
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('4:3')
                        ->maxSize(2048),

                    Forms\Components\Toggle::make('aktif')
                        ->label('Barang Aktif')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\ImageColumn::make('foto')
                    ->label('')
                    ->circular()
                    ->size(36)
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode(substr($record->nama, 0, 2)) . '&background=4F46E5&color=fff&size=36'),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn ($record) => $record->category?->nama),

                Tables\Columns\TextColumn::make('lokasi_lengkap')
                    ->label('Lokasi / Area')
                    ->state(function (Item $record) {
                        $parts = [];
                        if ($record->room) $parts[] = $record->room->nama;
                        if ($record->roomArea) $parts[] = $record->roomArea->nama;
                        if ($record->rak_posisi) $parts[] = "({$record->rak_posisi})";
                        return count($parts) ? implode(' → ', $parts) : '-';
                    })
                    ->description(fn (Item $record) => $record->unit ? "[{$record->unit->kode}] {$record->unit->nama}" : null)
                    ->wrap()
                    ->searchable(query: fn (Builder $query, string $search) =>
                        $query->whereHas('room', fn ($q) => $q->where('nama', 'like', "%{$search}%"))
                              ->orWhereHas('roomArea', fn ($q) => $q->where('nama', 'like', "%{$search}%"))
                              ->orWhere('rak_posisi', 'like', "%{$search}%")
                    ),

                Tables\Columns\TextColumn::make('tipe_barang')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'consumable'  => 'danger',
                        'returnable'  => 'warning',
                        'fixed_asset' => 'success',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => Item::TIPE_LABELS[$state] ?? $state),

                Tables\Columns\TextColumn::make('stock.stok_tersedia')
                    ->label('Stok')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($record) => match($record->status_stok ?? 'aman') {
                        'habis'   => 'danger',
                        'menipis' => 'warning',
                        default   => 'success',
                    })
                    ->suffix(fn ($record) => ' ' . $record->satuan)
                    ->default(0),

                Tables\Columns\TextColumn::make('harga_referensi')
                    ->label('Harga Referensi')
                    ->money('IDR')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_id')
                    ->label('Jurusan / Unit')
                    ->options(Unit::where('aktif', true)->orderBy('urutan')->pluck('nama', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('unit_room_id')
                    ->label('Ruang / Lab')
                    ->options(fn () => UnitRoom::with('unit')->where('aktif', true)->get()->mapWithKeys(fn (UnitRoom $r) => [
                        $r->id => ($r->unit ? "[{$r->unit->kode}] " : '') . $r->nama,
                    ]))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('room_area_id')
                    ->label('Area Kerja')
                    ->options(fn () => RoomArea::with('room.unit')->where('aktif', true)->get()->mapWithKeys(fn (RoomArea $a) => [
                        $a->id => ($a->room?->unit ? "[{$a->room->unit->kode}] " : '') . ($a->room?->nama ? "{$a->room->nama} → " : '') . $a->nama,
                    ]))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('tipe_barang')
                    ->label('Tipe Barang')
                    ->options(Item::TIPE_LABELS),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->options(Category::orderBy('nama')->pluck('nama', 'id'))
                    ->searchable(),

                Tables\Filters\TernaryFilter::make('aktif')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->modalWidth('4xl')
                    ->form(fn (Form $form) => static::form($form)),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Hapus Barang')
                    ->modalDescription(fn ($record) => "Barang '{$record->nama}' akan dihapus permanen. Lanjutkan?")
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nama')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'view'   => Pages\ViewItem::route('/{record}'),
            'edit'   => Pages\EditItem::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['unit', 'room', 'roomArea', 'category', 'stock', 'supplier']);
    }
}
