<?php

namespace App\Filament\Kepalab\Resources;

use App\Filament\Kepalab\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Manajemen Kepala Lab';
    protected static ?string $navigationLabel = 'Kategori Barang Lab';
    protected static ?string $modelLabel = 'Kategori';
    protected static ?string $pluralModelLabel = 'Master Kategori Alat & Bahan';
    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return session('lab_active_role', 'toolman') === 'kepala_lab';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('unit_id', auth()->user()?->unit_id);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('unit_id')
                ->default(fn () => auth()->user()?->unit_id),

            Forms\Components\TextInput::make('nama')
                ->label('Nama Kelompok / Kategori')
                ->required()
                ->maxLength(100)
                ->placeholder('Contoh: Alat Ukur, Peralatan Jaringan, Bahan Habis Pakai')
                ->helperText('Pengelompokan alat dan bahan praktik di lab Anda.'),

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
                    ->label('Nama Kelompok / Kategori')
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
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Hapus Kategori')
                    ->modalDescription('Kategori ini akan dihapus. Barang yang menggunakan kategori ini tidak akan terhapus.'),
            ])
            ->defaultSort('urutan');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCategories::route('/'),
        ];
    }
}
