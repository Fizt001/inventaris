<?php

namespace App\Filament\Admin\Resources\ItemResource\Pages;

use App\Filament\Admin\Resources\ItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Barang'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua'       => Tab::make('Semua'),
            'consumable'  => Tab::make('Habis Pakai')->modifyQueryUsing(fn (Builder $query) => $query->where('tipe_barang', 'consumable')),
            'returnable'  => Tab::make('Pinjam-Kembali')->modifyQueryUsing(fn (Builder $query) => $query->where('tipe_barang', 'returnable')),
            'fixed_asset' => Tab::make('Aset Tetap')->modifyQueryUsing(fn (Builder $query) => $query->where('tipe_barang', 'fixed_asset')),
        ];
    }
}
