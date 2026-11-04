<?php

namespace App\Filament\Admin\Pages;

use App\Models\Pegawai;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class ManagePegawais extends InlineManagePage
{
    protected static ?string $navigationIcon  = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Data Guru / Staff';
    protected static ?string $title           = 'Data Guru & Staff';
    protected static ?string $slug            = 'manage-pegawais';
    protected static ?int    $navigationSort  = 6;

    public function getCreateHeading(): string
    {
        return 'Tambah Data Guru / Staff';
    }

    private function formSchema(): array
    {
        return [
            Forms\Components\Grid::make(4)->schema([
                Forms\Components\TextInput::make('nip')
                    ->label('NIP')
                    ->maxLength(30),

                Forms\Components\TextInput::make('nama')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(100),

                Forms\Components\TextInput::make('jabatan')
                    ->label('Jabatan')
                    ->maxLength(100),

                Forms\Components\Select::make('unit_id')
                    ->label('Unit / Lab')
                    ->options(Unit::where('aktif', true)->orderBy('urutan')->pluck('nama', 'id'))
                    ->searchable()
                    ->nullable()
                    ->native(false),
            ]),
            Forms\Components\Select::make('status')
                ->label('Status')
                ->options(['aktif' => 'Aktif', 'tidak_aktif' => 'Tidak Aktif'])
                ->default('aktif')
                ->native(false),
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
        Pegawai::create($data);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Pegawai::query()->with('unit')->orderBy('nama'))
            ->columns([
                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->fontFamily('mono')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('unit.nama')
                    ->label('Unit / Lab')
                    ->badge()
                    ->color('primary')
                    ->placeholder('Umum'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state === 'aktif' ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state) => $state === 'aktif' ? 'Aktif' : 'Tidak Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Guru / Staff')
                    ->modalWidth('2xl')
                    ->form($this->formSchema()),

                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Hapus Data')
                    ->modalDescription('Data guru/staff ini akan dihapus. Lanjutkan?')
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->emptyStateIcon('heroicon-o-identification')
            ->emptyStateHeading('Belum ada data guru / staff')
            ->emptyStateDescription('Tambahkan data menggunakan form di atas.');
    }
}
