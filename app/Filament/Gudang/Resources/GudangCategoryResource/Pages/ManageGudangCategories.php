<?php

namespace App\Filament\Gudang\Resources\GudangCategoryResource\Pages;

use App\Filament\Gudang\Resources\GudangCategoryResource;
use App\Models\Unit;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageGudangCategories extends ManageRecords
{
    protected static string $resource = GudangCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Kategori Gudang Baru')
                ->icon('heroicon-o-plus')
                ->modalWidth('md')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['unit_id'] = auth()->user()?->unit_id ?? Unit::where('kode', 'GDG')->value('id') ?? 5;
                    return $data;
                }),
        ];
    }
}
