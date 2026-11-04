<?php

namespace App\Filament\Gudang\Resources;

use App\Filament\Gudang\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Ruang dan Master';
    protected static ?string $navigationLabel = 'Direktori Supplier';
    protected static ?string $modelLabel = 'Supplier';
    protected static ?string $pluralModelLabel = 'Daftar Supplier / Rekanan';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama')
                ->label('Nama Perusahaan / Toko / Vendor')
                ->required()
                ->maxLength(150)
                ->placeholder('PT / CV / Toko ...'),

            Forms\Components\TextInput::make('kontak_person')
                ->label('Contact Person')
                ->placeholder('Nama kontak perwakilan vendor')
                ->maxLength(100),

            Forms\Components\TextInput::make('telepon')
                ->label('Nomor Telepon / WhatsApp')
                ->tel()
                ->maxLength(50),

            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->maxLength(100),

            Forms\Components\Textarea::make('alamat')
                ->label('Alamat Lengkap')
                ->rows(2)
                ->columnSpanFull(),

            Forms\Components\Textarea::make('catatan')
                ->label('Catatan Rekanan')
                ->rows(2)
                ->placeholder('Catatan tempo pembayaran, diskon, dll.')
                ->columnSpanFull(),

            Forms\Components\Toggle::make('aktif')
                ->label('Status Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Supplier / Vendor')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('kontak_person')
                    ->label('Kontak Person')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('telepon')
                    ->label('Telepon / WA')
                    ->searchable()
                    ->copyable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Jumlah Barang')
                    ->counts('items')
                    ->badge()
                    ->color('primary')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth('2xl'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('nama');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSuppliers::route('/'),
        ];
    }
}
