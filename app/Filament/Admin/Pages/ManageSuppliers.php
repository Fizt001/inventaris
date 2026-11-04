<?php

namespace App\Filament\Admin\Pages;

use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class ManageSuppliers extends InlineManagePage
{
    protected static ?string $navigationIcon  = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Direktori Supplier';
    protected static ?string $title           = 'Direktori Supplier / Vendor';
    protected static ?string $slug            = 'manage-suppliers';
    protected static ?int    $navigationSort  = 5;

    public function getCreateHeading(): string
    {
        return 'Tambah Supplier Baru';
    }

    private function formSchema(): array
    {
        return [
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama Supplier / Toko')
                    ->required()
                    ->maxLength(150),

                Forms\Components\TextInput::make('kontak_person')
                    ->label('Kontak Person')
                    ->maxLength(100),

                Forms\Components\TextInput::make('kota')
                    ->label('Kota')
                    ->maxLength(100),
            ]),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('telepon')
                    ->label('Telepon / WA')
                    ->tel()
                    ->maxLength(20),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(100),

                Forms\Components\TextInput::make('npwp')
                    ->label('NPWP')
                    ->maxLength(30),
            ]),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Textarea::make('alamat')
                    ->label('Alamat')
                    ->rows(2),

                Forms\Components\Textarea::make('catatan')
                    ->label('Catatan')
                    ->rows(2),
            ]),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->formSchema())
            ->statePath('createData');
    }

    protected function handleCreate(array $data): void
    {
        Supplier::create($data);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Supplier::query()->where('aktif', true)->orderBy('nama'))
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Supplier')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('kontak_person')
                    ->label('Kontak')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('telepon')
                    ->label('Telepon')
                    ->icon('heroicon-m-phone')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('kota')
                    ->label('Kota')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->icon('heroicon-m-envelope')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('npwp')
                    ->label('NPWP')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Supplier')
                    ->modalWidth('2xl')
                    ->form($this->formSchema()),

                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Hapus Supplier')
                    ->modalDescription('Data supplier ini akan dihapus. Lanjutkan?')
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->emptyStateIcon('heroicon-o-truck')
            ->emptyStateHeading('Belum ada supplier')
            ->emptyStateDescription('Tambahkan supplier baru menggunakan form di atas.');
    }
}
