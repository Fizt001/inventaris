<?php

namespace App\Filament\Admin\Pages;

use App\Models\Siswa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class ManageSiswas extends InlineManagePage
{
    protected static ?string $navigationIcon  = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Data Siswa';
    protected static ?string $title           = 'Data Siswa';
    protected static ?string $slug            = 'manage-siswas';
    protected static ?int    $navigationSort  = 5;

    public function getCreateHeading(): string
    {
        return 'Tambah Data Siswa';
    }

    private function formSchema(): array
    {
        return [
            Forms\Components\Grid::make(4)->schema([
                Forms\Components\TextInput::make('nis')
                    ->label('NIS')
                    ->maxLength(20),

                Forms\Components\TextInput::make('nama')
                    ->label('Nama Siswa')
                    ->required()
                    ->maxLength(100),

                Forms\Components\TextInput::make('kelas')
                    ->label('Kelas')
                    ->required()
                    ->maxLength(20)
                    ->placeholder('XII TEK 1'),

                Forms\Components\TextInput::make('jurusan')
                    ->label('Jurusan')
                    ->maxLength(50),
            ]),
            Forms\Components\Select::make('status')
                ->label('Status')
                ->options(['aktif' => 'Aktif', 'alumni' => 'Alumni', 'keluar' => 'Keluar'])
                ->default('aktif')
                ->native(false)
                ->columnSpanFull(),
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
        Siswa::create($data);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Siswa::query()->orderBy('kelas')->orderBy('nama'))
            ->columns([
                Tables\Columns\TextColumn::make('nis')
                    ->label('NIS')
                    ->fontFamily('mono')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kelas')
                    ->label('Kelas')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                Tables\Columns\TextColumn::make('jurusan')
                    ->label('Jurusan')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'aktif'  => 'success',
                        'alumni' => 'info',
                        'keluar' => 'danger',
                        default  => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['aktif' => 'Aktif', 'alumni' => 'Alumni', 'keluar' => 'Keluar'])
                    ->default('aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Data Siswa')
                    ->modalWidth('2xl')
                    ->form($this->formSchema()),

                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Hapus Data Siswa')
                    ->modalDescription('Data siswa ini akan dihapus. Lanjutkan?')
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->emptyStateHeading('Belum ada data siswa')
            ->emptyStateDescription('Tambahkan data siswa menggunakan form di atas.');
    }
}
