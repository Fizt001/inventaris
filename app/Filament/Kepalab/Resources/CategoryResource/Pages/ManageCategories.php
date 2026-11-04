<?php

namespace App\Filament\Kepalab\Resources\CategoryResource\Pages;

use App\Filament\Kepalab\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCategories extends ManageRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Kategori Baru')
                ->icon('heroicon-o-plus')
                ->modalWidth('md')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['unit_id'] = auth()->user()?->getEffectiveUnitId();
                    return $data;
                }),
        ];
    }
}
