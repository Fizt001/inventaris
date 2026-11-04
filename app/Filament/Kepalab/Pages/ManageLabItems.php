<?php

namespace App\Filament\Kepalab\Pages;

use App\Models\Category;
use App\Models\Item;
use App\Models\RoomArea;
use App\Models\RoomStorage;
use App\Models\Stock;
use App\Models\UnitRoom;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ManageLabItems extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Inventaris dan Stok';
    protected static ?string $navigationLabel = 'Inventaris Lab (Monitoring)';
    protected static ?string $title           = 'Wadah Monitoring Inventaris Lab';
    protected static ?string $slug            = 'items';
    protected static ?int    $navigationSort  = 3;

    protected static string $view = 'filament.kepalab.pages.manage-lab-items';

    public function getTotalItemCount(): int
    {
        return Item::where('unit_id', auth()->user()?->unit_id)->where('aktif', true)->count();
    }

    public function getTotalStokTersedia(): int
    {
        return (int) Stock::where('unit_id', auth()->user()?->unit_id)->sum('stok_tersedia');
    }

    public function getTotalStokDipinjam(): int
    {
        return (int) Stock::where('unit_id', auth()->user()?->unit_id)->sum('stok_dipinjam');
    }

    public function getStokMenipisCount(): int
    {
        return Item::where('unit_id', auth()->user()?->unit_id)
            ->where('aktif', true)
            ->whereHas('stock', fn ($q) => $q->where('stok_tersedia', '<=', 2))
            ->count();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('daftar_barang')
                ->label('+ Daftarkan Barang Baru')
                ->icon('heroicon-m-plus-circle')
                ->color('primary')
                ->url('/kepalab/daftar-barang'),

            Action::make('barang_masuk')
                ->label('+ Catat Barang Masuk')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->url('/kepalab/barang-masuk'),

            Action::make('peminjaman')
                ->label('+ Catat Peminjaman / Pemakaian')
                ->icon('heroicon-m-clipboard-document-check')
                ->color('warning')
                ->url('/kepalab/peminjaman'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Item::query()
                    ->where('unit_id', auth()->user()?->unit_id)
                    ->with(['room', 'roomArea', 'storage', 'category', 'stock'])
            )
            ->defaultGroup(
                Tables\Grouping\Group::make('unit_room_id')
                    ->label('Ruang / Lab')
                    ->getTitleFromRecordUsing(fn (Item $record) =>
                        $record->room ? "Lab: {$record->room->nama}" : 'Tanpa Lab'
                    )
                    ->collapsible()
                    ->titlePrefixedWithLabel(false)
            )
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->badge()
                    ->color('indigo'),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Barang & Spesifikasi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Item $record) =>
                        ($record->category?->nama ? "[{$record->category->nama}] " : '') .
                        ($record->deskripsi ? "Spec: {$record->deskripsi}" : '')
                    )
                    ->wrap(),

                Tables\Columns\TextColumn::make('roomArea.nama')
                    ->label('Area Lab')
                    ->badge()
                    ->color('info')
                    ->placeholder('Seluruh Lab')
                    ->description(fn (Item $record) => $record->rak_posisi ? "Posisi: {$record->rak_posisi}" : null),

                Tables\Columns\TextColumn::make('tipe_barang')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => Item::TIPE_COLORS[$state] ?? 'gray')
                    ->formatStateUsing(fn ($state) => Item::TIPE_LABELS[$state] ?? $state),

                Tables\Columns\TextColumn::make('stock.stok_total')
                    ->label('Total Masuk')
                    ->alignCenter()
                    ->badge()
                    ->color('gray')
                    ->suffix(fn (Item $record) => ' ' . $record->satuan)
                    ->default(0),

                Tables\Columns\TextColumn::make('stock.stok_dipinjam')
                    ->label('Dipinjam')
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => (int)$state > 0 ? 'warning' : 'gray')
                    ->suffix(fn (Item $record) => ' ' . $record->satuan)
                    ->default(0),

                Tables\Columns\TextColumn::make('stock.stok_tersedia')
                    ->label('Sisa Fisik Tersedia')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (Item $record) =>
                        ($record->stock?->stok_tersedia ?? 0) <= ($record->stok_minimum ?? 1) ? 'danger' : 'success'
                    )
                    ->suffix(fn (Item $record) => ' ' . $record->satuan)
                    ->default(0)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('harga_referensi')
                    ->label('Nilai Satuan')
                    ->money('IDR')
                    ->alignRight()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_room_id')
                    ->label('Ruang / Lab')
                    ->options(fn () => UnitRoom::where('unit_id', auth()->user()?->unit_id)
                        ->where('aktif', true)
                        ->pluck('nama', 'id')
                    ),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->options(fn () => Category::where('unit_id', auth()->user()?->unit_id)
                        ->pluck('nama', 'id')
                    ),

                Tables\Filters\SelectFilter::make('tipe_barang')
                    ->label('Tipe')
                    ->options(Item::TIPE_OPTIONS),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Ubah Lokasi / Spec')
                    ->slideOver()
                    ->modalWidth('3xl')
                    ->form([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('kode')->disabled(),
                            Forms\Components\TextInput::make('nama')->required(),

                            Forms\Components\Select::make('category_id')
                                ->label('Kategori')
                                ->options(fn () => Category::where('unit_id', auth()->user()?->unit_id)->pluck('nama', 'id'))
                                ->required(),

                            Forms\Components\Select::make('unit_room_id')
                                ->label('Ruang / Lab')
                                ->options(fn () => UnitRoom::where('unit_id', auth()->user()?->unit_id)->where('aktif', true)->pluck('nama', 'id'))
                                ->required()
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
                                ->label('Catatan Posisi / Meja'),

                            Forms\Components\Textarea::make('deskripsi')
                                ->label('Spesifikasi Teknis')
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),
                    ]),

                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Hapus Inventaris')
                    ->modalDescription(fn (Item $record) => "Hapus '{$record->nama}' dari inventaris lab?"),
            ])
            ->defaultSort('nama')
            ->striped();
    }
}
