<?php

namespace App\Filament\Gudang\Resources;

use App\Filament\Gudang\Resources\GudangCategoryResource\Pages;
use App\Models\Category;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GudangCategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Ruang dan Master';
    protected static ?string $navigationLabel = 'Master Kategori Gudang';
    protected static ?string $modelLabel = 'Kategori';
    protected static ?string $pluralModelLabel = 'Kategori Barang Gudang';
    protected static ?int $navigationSort = 1;

    protected static function getGudangUnitId(): int
    {
        return auth()->user()?->unit_id ?? Unit::where('kode', 'GDG')->value('id') ?? 5;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('unit_id', static::getGudangUnitId());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('unit_id')
                ->default(fn () => static::getGudangUnitId()),

            Forms\Components\TextInput::make('nama')
                ->label('Nama Kategori / Kelompok Barang')
                ->required()
                ->maxLength(100)
                ->placeholder('Contoh: Alat Tulis Kantor, Kertas, Perlengkapan Kebersihan'),

            Forms\Components\TextInput::make('urutan')
                ->label('Urutan Tampil')
                ->numeric()
                ->default(0)
                ->minValue(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Kategori / Kelompok')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('urutan')
                    ->label('Urutan')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth('md'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('urutan');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageGudangCategories::route('/'),
        ];
    }
}
