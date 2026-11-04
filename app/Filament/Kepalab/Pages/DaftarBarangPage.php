<?php

namespace App\Filament\Kepalab\Pages;

use App\Filament\Admin\Pages\InlineManagePage;
use App\Models\Category;
use App\Models\Item;
use App\Models\RoomArea;
use App\Models\Stock;
use App\Models\UnitRoom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DaftarBarangPage extends InlineManagePage
{
    protected static ?string $navigationIcon  = 'heroicon-o-plus-circle';
    protected static ?string $navigationGroup = 'Inventaris dan Stok';
    protected static ?string $navigationLabel = 'Daftarkan Barang';
    protected static ?string $title           = 'Pendaftaran Master Barang & Komponen Lab';
    protected static ?string $slug            = 'daftar-barang';
    protected static ?int    $navigationSort  = 1;

    public function getCreateHeading(): string
    {
        return 'Pendaftaran Cepat Kode & Nama Barang / Komponen';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(12)->schema([
                    Forms\Components\TextInput::make('kode')
                        ->label('Kode Barang / Komponen')
                        ->required()
                        ->unique('items', 'kode')
                        ->maxLength(50)
                        ->autofocus()
                        ->placeholder('Contoh: RES-10K, SDR-01, CAP-100UF, OSC-01')
                        ->helperText('Kode unik barang sesuai format lab (tekan Enter untuk langsung simpan).')
                        ->extraInputAttributes(['style' => 'text-transform:uppercase'])
                        ->columnSpan(['default' => 12, 'sm' => 4, 'lg' => 3]),

                    Forms\Components\TextInput::make('nama')
                        ->label('Nama Barang / Komponen')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Resistor 10K Ohm 1/4W, Solder Listrik 40W, Osiloskop Digital')
                        ->helperText('Nama alat atau bahan yang akan didaftarkan ke sistem.')
                        ->columnSpan(['default' => 12, 'sm' => 8, 'lg' => 9]),
                ]),
            ])
            ->statePath('createData');
    }

    protected function handleCreate(array $data): void
    {
        $unitId = auth()->user()?->unit_id;
        $kode   = strtoupper(trim($data['kode']));
        $nama   = trim($data['nama']);

        $item = Item::create([
            'unit_id'         => $unitId,
            'kode'            => $kode,
            'nama'            => $nama,
            'category_id'     => null, // Diatur massal via ceklis di bawah
            'tipe_barang'     => 'peralatan',
            'satuan'          => 'unit',
            'unit_room_id'    => null,
            'room_area_id'    => null,
            'rak_posisi'      => null,
            'harga_referensi' => null,
            'deskripsi'       => null,
            'stok_minimum'    => 1,
            'aktif'           => true,
        ]);

        Stock::firstOrCreate(
            ['item_id' => $item->id, 'unit_id' => $unitId],
            [
                'stok_total'    => 0,
                'stok_tersedia' => 0,
                'stok_dipinjam' => 0,
                'stok_rusak'    => 0,
            ]
        );
    }

    public function table(Table $table): Table
    {
        $unitId = auth()->user()?->unit_id;

        return $table
            ->query(
                Item::query()
                    ->where('unit_id', $unitId)
                    ->with(['room', 'roomArea', 'category', 'stock'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->badge()
                    ->color('indigo'),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Barang / Komponen')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('satuan')
                    ->label('Satuan')
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('tipe_barang')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => Item::TIPE_COLORS[$state] ?? 'gray')
                    ->formatStateUsing(fn ($state) => Item::TIPE_LABELS[$state] ?? $state),
            ])
            ->defaultGroup(
                Tables\Grouping\Group::make('category_id')
                    ->label('Kategori / Kelompok')
                    ->getTitleFromRecordUsing(fn (Item $record): string => $record->category?->nama ?? '⚠️ Belum Dikelompokkan')
                    ->collapsible()
            )
            ->recordAction(Tables\Actions\ViewAction::class)
            ->filters([
                Tables\Filters\Filter::make('belum_dikelompokkan')
                    ->label('⚠️ Belum Dikelompokkan')
                    ->query(fn (Builder $query): Builder => $query->whereNull('category_id')),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->options(fn () => Category::where('unit_id', $unitId)->pluck('nama', 'id')),

                Tables\Filters\SelectFilter::make('unit_room_id')
                    ->label('Ruang / Lab')
                    ->options(fn () => UnitRoom::where('unit_id', $unitId)->where('aktif', true)->pluck('nama', 'id')),

                Tables\Filters\SelectFilter::make('tipe_barang')
                    ->label('Tipe')
                    ->options(Item::TIPE_OPTIONS),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail')
                    ->icon('heroicon-m-eye')
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->infolist([
                        \Filament\Infolists\Components\Section::make('Identitas Barang / Komponen')
                            ->columns(2)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('kode')
                                    ->label('Kode Barang')
                                    ->badge()
                                    ->color('indigo')
                                    ->fontFamily('mono'),

                                \Filament\Infolists\Components\TextEntry::make('nama')
                                    ->label('Nama Barang / Komponen')
                                    ->weight('bold'),

                                \Filament\Infolists\Components\TextEntry::make('satuan')
                                    ->label('Satuan')
                                    ->badge()
                                    ->color('gray'),

                                \Filament\Infolists\Components\TextEntry::make('category.nama')
                                    ->label('Kategori / Kelompok')
                                    ->badge()
                                    ->placeholder('⚠️ Belum Dikelompokkan'),

                                \Filament\Infolists\Components\TextEntry::make('tipe_barang')
                                    ->label('Tipe Barang')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => Item::TIPE_LABELS[$state] ?? $state)
                                    ->color(fn ($state) => Item::TIPE_COLORS[$state] ?? 'gray'),

                                \Filament\Infolists\Components\IconEntry::make('aktif')
                                    ->label('Status Aktif')
                                    ->boolean(),
                            ]),

                        \Filament\Infolists\Components\Section::make('Penempatan di Lab')
                            ->columns(2)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('room.nama')
                                    ->label('Ruang / Lab')
                                    ->placeholder('⚠️ Belum Ditentukan'),

                                \Filament\Infolists\Components\TextEntry::make('roomArea.nama')
                                    ->label('Area Lab')
                                    ->placeholder('-'),

                                \Filament\Infolists\Components\TextEntry::make('rak_posisi')
                                    ->label('Catatan Posisi / Meja / Rak')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ]),

                        \Filament\Infolists\Components\Section::make('Nilai / Spesifikasi Teknis')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('deskripsi')
                                    ->label('Spesifikasi Teknis')
                                    ->placeholder('Tidak ada catatan spesifikasi khusus.')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->form([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('kode')
                                ->label('Kode Barang')
                                ->required()
                                ->unique(ignoreRecord: true),

                            Forms\Components\TextInput::make('nama')
                                ->label('Nama Barang')
                                ->required(),

                            Forms\Components\Select::make('satuan')
                                ->label('Satuan')
                                ->options(Item::SATUAN_OPTIONS)
                                ->required(),

                            Forms\Components\Select::make('category_id')
                                ->label('Kategori / Kelompok')
                                ->options(fn () => Category::where('unit_id', $unitId)->pluck('nama', 'id'))
                                ->nullable()
                                ->searchable(),

                            Forms\Components\Select::make('tipe_barang')
                                ->label('Tipe Barang')
                                ->options(Item::TIPE_OPTIONS)
                                ->required(),

                            Forms\Components\Select::make('unit_room_id')
                                ->label('Ruang / Lab')
                                ->options(fn () => UnitRoom::where('unit_id', $unitId)->where('aktif', true)->pluck('nama', 'id'))
                                ->nullable()
                                ->reactive(),

                            Forms\Components\Select::make('room_area_id')
                                ->label('Area Lab')
                                ->options(fn (callable $get) =>
                                    $get('unit_room_id')
                                        ? RoomArea::where('unit_room_id', $get('unit_room_id'))->where('aktif', true)->pluck('nama', 'id')
                                        : []
                                )
                                ->nullable(),

                            Forms\Components\TextInput::make('rak_posisi')
                                ->label('Catatan Posisi / Meja / Rak (Opsional)')
                                ->nullable(),

                            Forms\Components\Toggle::make('aktif')
                                ->label('Status Aktif')
                                ->default(true),

                            Forms\Components\Textarea::make('deskripsi')
                                ->label('Nilai / Spesifikasi Teknis (Opsional)')
                                ->placeholder('Contoh: Nilai 10K Ohm 1/4W toleransi 5%, Elemen keramik 220V 40W')
                                ->rows(3)
                                ->columnSpanFull()
                                ->helperText('Input spesifikasi teknis khusus jika ada (bisa dikosongkan).'),
                        ]),
                    ]),

                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Hapus Data Barang')
                    ->modalDescription(fn (Item $record) => "Hapus '{$record->nama}'?"),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('kelompokkan_massal')
                        ->label('⚡ Kelompokkan Massal yang Diceklis')
                        ->icon('heroicon-o-squares-plus')
                        ->color('success')
                        ->modalHeading('Pengelompokan Massal Barang Terpilih')
                        ->modalDescription('Terapkan Kategori, Tipe, Satuan, dan Lokasi Lab sekaligus ke semua barang yang telah Anda ceklis.')
                        ->modalWidth('2xl')
                        ->form([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('category_id')
                                    ->label('Kategori / Kelompok')
                                    ->options(fn () => Category::where('unit_id', $unitId)->pluck('nama', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->helperText('Pilih kategori untuk barang yang diceklis.')
                                    ->columnSpanFull(),

                                Forms\Components\Select::make('tipe_barang')
                                    ->label('Tipe Barang')
                                    ->options(Item::TIPE_OPTIONS)
                                    ->default('peralatan')
                                    ->required(),

                                Forms\Components\Select::make('satuan')
                                    ->label('Satuan')
                                    ->options(Item::SATUAN_OPTIONS)
                                    ->default('unit')
                                    ->required(),

                                Forms\Components\Select::make('unit_room_id')
                                    ->label('Ruang / Lab')
                                    ->options(fn () => UnitRoom::where('unit_id', $unitId)->where('aktif', true)->pluck('nama', 'id'))
                                    ->nullable()
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set) => $set('room_area_id', null))
                                    ->helperText('Pilih lab tempat penyimpanan.'),

                                Forms\Components\Select::make('room_area_id')
                                    ->label('Area Lab')
                                    ->options(fn (callable $get) =>
                                        $get('unit_room_id')
                                            ? RoomArea::where('unit_room_id', $get('unit_room_id'))->where('aktif', true)->pluck('nama', 'id')
                                            : []
                                    )
                                    ->nullable()
                                    ->helperText('Pilih area kerja di lab.'),

                                Forms\Components\TextInput::make('rak_posisi')
                                    ->label('Catatan Posisi / Meja / Rak (Opsional)')
                                    ->placeholder('Misal: Rak Komponen 1, Meja Praktik A')
                                    ->columnSpanFull(),
                            ]),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $updateData = [
                                'category_id'  => $data['category_id'],
                                'tipe_barang'  => $data['tipe_barang'],
                                'satuan'       => $data['satuan'],
                            ];
                            if (! empty($data['unit_room_id'])) {
                                $updateData['unit_room_id'] = $data['unit_room_id'];
                            }
                            if (isset($data['room_area_id'])) {
                                $updateData['room_area_id'] = $data['room_area_id'];
                            }
                            if (! empty($data['rak_posisi'])) {
                                $updateData['rak_posisi'] = $data['rak_posisi'];
                            }

                            $count = $records->count();
                            $records->each(fn (Item $item) => $item->update($updateData));

                            Notification::make()
                                ->title("Berhasil Mengelompokkan {$count} Barang!")
                                ->body('Semua barang yang diceklis telah diperbarui kategori, tipe, satuan, dan lokasi lab-nya.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
