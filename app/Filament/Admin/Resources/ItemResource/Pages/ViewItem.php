<?php

namespace App\Filament\Admin\Resources\ItemResource\Pages;

use App\Filament\Admin\Resources\ItemResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewItem extends ViewRecord
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Identitas Barang')->columns(3)->schema([
                TextEntry::make('kode')->label('Kode Barang')->badge()->color('gray'),
                TextEntry::make('nama')->label('Nama')->columnSpan(2),
                TextEntry::make('unit.nama')->label('Unit / Lab')->badge()->color('primary'),
                TextEntry::make('category.nama')->label('Kategori'),
                TextEntry::make('tipe_barang')->label('Tipe')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'consumable' => 'danger', 'returnable' => 'warning', 'fixed_asset' => 'success', default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => \App\Models\Item::TIPE_LABELS[$state] ?? $state),
                TextEntry::make('satuan')->label('Satuan'),
                TextEntry::make('deskripsi')->label('Deskripsi')->columnSpanFull(),
            ]),

            Section::make('Harga & Stok')->columns(4)->schema([
                TextEntry::make('harga_satuan')->label('Harga Satuan')->money('IDR'),
                TextEntry::make('harga_referensi')->label('Harga Referensi')->money('IDR'),
                TextEntry::make('stock.stok_tersedia')->label('Stok Tersedia')
                    ->suffix(fn ($record) => ' ' . $record->satuan)
                    ->badge()
                    ->color(fn ($record) => match($record->status_stok) {
                        'habis' => 'danger', 'menipis' => 'warning', default => 'success',
                    }),
                TextEntry::make('stok_minimum')->label('Stok Minimum')->suffix(fn ($record) => ' ' . $record->satuan),
                TextEntry::make('supplier.nama')->label('Supplier Utama'),
                TextEntry::make('ppn_termasuk')->label('PPN')->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Termasuk PPN' : 'Belum PPN')
                    ->color(fn ($state) => $state ? 'success' : 'warning'),
            ]),

            Section::make('Foto')->schema([
                ImageEntry::make('foto')->label('')->height(200),
            ])->hidden(fn ($record) => !$record->foto),
        ]);
    }
}
