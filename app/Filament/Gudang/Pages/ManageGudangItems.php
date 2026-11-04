<?php

namespace App\Filament\Gudang\Pages;

use App\Filament\Admin\Pages\InlineManagePage;
use App\Models\Category;
use App\Models\Item;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class ManageGudangItems extends InlineManagePage
{
    protected static ?string $navigationIcon  = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Inventaris dan Stok';
    protected static ?string $navigationLabel = 'Stok Barang Gudang';
    protected static ?string $title           = 'Stok Barang Gudang (ATK & Logistik)';
    protected static ?string $slug            = 'gudang-items';
    protected static ?int    $navigationSort  = 1;

    protected static function getGudangUnitId(): int
    {
        return auth()->user()?->unit_id ?? Unit::where('kode', 'GDG')->value('id') ?? 5;
    }

    public function getCreateHeading(): string
    {
        return 'Pencatatan Barang Gudang Baru di Laman';
    }

    public function form(Form $form): Form
    {
        $gudangUnitId = static::getGudangUnitId();

        return $form
            ->schema([
                Forms\Components\Fieldset::make('1. Identitas Barang Gudang')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('kode')
                            ->label('Kode Barang / SKU')
                            ->required()
                            ->unique('items', 'kode')
                            ->maxLength(50)
                            ->placeholder('Contoh: ATK-001, KRT-A4')
                            ->helperText('Kode barang sesuai sistem logistik sekolah.'),

                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Barang')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Kertas HVS A4 80gr PaperOne'),

                        Forms\Components\Select::make('category_id')
                            ->label('Kategori Gudang')
                            ->options(fn () => Category::where('unit_id', $gudangUnitId)
                                ->orderBy('nama')
                                ->pluck('nama', 'id')
                            )
                            ->searchable()
                            ->nullable(),
                    ]),

                Forms\Components\Fieldset::make('2. Stok, Rak & Vendor')
                    ->columns(4)
                    ->schema([
                        Forms\Components\Select::make('satuan')
                            ->label('Satuan')
                            ->options(Item::SATUAN_OPTIONS)
                            ->required()
                            ->default('pcs')
                            ->native(false),

                        Forms\Components\TextInput::make('stok_awal')
                            ->label('Jumlah / Stok Awal Fisik')
                            ->numeric()
                            ->required()
                            ->default(10)
                            ->minValue(0),

                        Forms\Components\TextInput::make('stok_minimum')
                            ->label('Batas Minimum Stok')
                            ->numeric()
                            ->default(5)
                            ->minValue(0)
                            ->helperText('Peringatan jika stok menipis.'),

                        Forms\Components\TextInput::make('rak_posisi')
                            ->label('Lokasi Rak / Gudang')
                            ->placeholder('Contoh: Rak B-3, Lemari 1')
                            ->maxLength(100),

                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier / Vendor Langganan')
                            ->options(Supplier::where('aktif', true)->orderBy('nama')->pluck('nama', 'id'))
                            ->searchable()
                            ->nullable(),

                        Forms\Components\TextInput::make('harga_referensi')
                            ->label('Estimasi Harga Beli (Rp)')
                            ->prefix('Rp')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Spesifikasi / Keterangan')
                            ->rows(1)
                            ->columnSpan(2),
                    ]),
            ])
            ->statePath('createData');
    }

    protected function handleCreate(array $data): void
    {
        $unitId = static::getGudangUnitId();

        $item = Item::create([
            'unit_id'         => $unitId,
            'kode'            => $data['kode'],
            'nama'            => $data['nama'],
            'category_id'     => $data['category_id'] ?? null,
            'satuan'          => $data['satuan'] ?? 'pcs',
            'tipe_barang'     => 'consumable',
            'rak_posisi'      => $data['rak_posisi'] ?? null,
            'supplier_id'     => $data['supplier_id'] ?? null,
            'harga_referensi' => $data['harga_referensi'] ?? 0,
            'stok_minimum'    => $data['stok_minimum'] ?? 5,
            'deskripsi'       => $data['deskripsi'] ?? null,
            'aktif'           => true,
        ]);

        $stokAwal = (int) ($data['stok_awal'] ?? 0);

        Stock::create([
            'item_id'       => $item->id,
            'unit_id'       => $unitId,
            'stok_total'    => $stokAwal,
            'stok_tersedia' => $stokAwal,
            'stok_dipinjam' => 0,
            'stok_rusak'    => 0,
        ]);
    }

    public function table(Table $table): Table
    {
        $gudangUnitId = static::getGudangUnitId();

        return $table
            ->query(
                Item::query()
                    ->where('unit_id', $gudangUnitId)
                    ->with(['category', 'supplier', 'stock'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode / SKU')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->badge()
                    ->color('emerald'),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Item $record) => $record->category?->nama),

                Tables\Columns\TextColumn::make('rak_posisi')
                    ->label('Lokasi Rak')
                    ->placeholder('-')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('supplier.nama')
                    ->label('Supplier / Vendor')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('stock.stok_tersedia')
                    ->label('Stok Fisik')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (Item $record) => ($record->stock?->stok_tersedia ?? 0) <= ($record->stok_minimum ?? 0) ? 'danger' : 'success')
                    ->suffix(fn (Item $record) => ' ' . $record->satuan)
                    ->default(0),

                Tables\Columns\IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->options(fn () => Category::where('unit_id', $gudangUnitId)->pluck('nama', 'id')),

                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->options(Supplier::where('aktif', true)->pluck('nama', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->modalWidth('3xl')
                    ->form([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('kode')
                                ->label('Kode / SKU')
                                ->required()
                                ->unique(ignoreRecord: true),

                            Forms\Components\TextInput::make('nama')
                                ->label('Nama Barang')
                                ->required(),

                            Forms\Components\Select::make('category_id')
                                ->label('Kategori')
                                ->options(fn () => Category::where('unit_id', $gudangUnitId)->pluck('nama', 'id')),

                            Forms\Components\Select::make('satuan')
                                ->label('Satuan')
                                ->options(Item::SATUAN_OPTIONS)
                                ->required(),

                            Forms\Components\TextInput::make('stok_minimum')
                                ->label('Batas Minimum Stok')
                                ->numeric(),

                            Forms\Components\TextInput::make('rak_posisi')
                                ->label('Lokasi Rak'),

                            Forms\Components\Select::make('supplier_id')
                                ->label('Supplier')
                                ->options(Supplier::where('aktif', true)->pluck('nama', 'id')),

                            Forms\Components\TextInput::make('harga_referensi')
                                ->label('Harga Beli (Rp)')
                                ->numeric()
                                ->prefix('Rp'),

                            Forms\Components\Toggle::make('aktif')
                                ->label('Status Aktif')
                                ->default(true),

                            Forms\Components\Textarea::make('deskripsi')
                                ->label('Deskripsi / Catatan')
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),
                    ]),

                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Hapus Barang Gudang')
                    ->modalDescription(fn (Item $record) => "Hapus '{$record->nama}' dari stok gudang?"),
            ])
            ->defaultSort('nama')
            ->striped();
    }
}
