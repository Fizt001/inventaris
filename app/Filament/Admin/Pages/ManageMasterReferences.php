<?php

namespace App\Filament\Admin\Pages;

use App\Models\MasterReference;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class ManageMasterReferences extends InlineManagePage
{
    protected static ?string $navigationIcon  = 'heroicon-o-bookmark-square';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Master Referensi';
    protected static ?string $title           = 'Master Data Referensi';
    protected static ?string $slug            = 'manage-master-references';
    protected static ?int    $navigationSort  = 7;

    public function getCreateHeading(): string
    {
        return 'Tambah Data Referensi Baru';
    }

    /** Schema input utama (1 baris kompak pada desktop) */
    private function compactFields(): array
    {
        return [
            Forms\Components\Grid::make(12)->schema([
                Forms\Components\Select::make('kategori')
                    ->label('Kategori Referensi')
                    ->options(MasterReference::KATEGORI_LABELS)
                    ->required()
                    ->native(false)
                    ->default('tipe_unit')
                    ->columnSpan(['default' => 12, 'sm' => 6, 'lg' => 3]),

                Forms\Components\TextInput::make('nama')
                    ->label('Nama Referensi')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Contoh: Bengkel Otomotif')
                    ->columnSpan(['default' => 12, 'sm' => 6, 'lg' => 5]),

                Forms\Components\TextInput::make('keterangan')
                    ->label('Keterangan')
                    ->maxLength(200)
                    ->placeholder('Deskripsi fungsi (opsional)')
                    ->columnSpan(['default' => 12, 'sm' => 12, 'lg' => 4]),
            ]),
        ];
    }

    /** Schema form lengkap untuk modal Edit */
    private function fullEditFields(): array
    {
        return [
            Forms\Components\Grid::make(12)->schema([
                Forms\Components\Select::make('kategori')
                    ->label('Kategori Referensi')
                    ->options(MasterReference::KATEGORI_LABELS)
                    ->required()
                    ->native(false)
                    ->columnSpan(['default' => 12, 'md' => 6]),

                Forms\Components\TextInput::make('nama')
                    ->label('Nama Referensi')
                    ->required()
                    ->maxLength(100)
                    ->columnSpan(['default' => 12, 'md' => 6]),

                Forms\Components\TextInput::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0)
                    ->columnSpan(['default' => 6, 'md' => 6]),

                Forms\Components\Checkbox::make('aktif')
                    ->label('Aktif')
                    ->default(true)
                    ->inline()
                    ->columnSpan(['default' => 6, 'md' => 6]),

                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rows(2)
                    ->placeholder('Catatan atau keterangan referensi')
                    ->columnSpan(12),
            ]),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                ...$this->compactFields(),

                Forms\Components\Section::make('Opsi Tambahan (Urutan & Status)')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->collapsible()
                    ->collapsed()
                    ->compact()
                    ->schema([
                        Forms\Components\Grid::make(12)->schema([
                            Forms\Components\TextInput::make('urutan')
                                ->label('Urutan Tampil')
                                ->numeric()
                                ->default(0)
                                ->columnSpan(['default' => 6, 'md' => 3]),

                            Forms\Components\Checkbox::make('aktif')
                                ->label('Status Aktif')
                                ->default(true)
                                ->inline()
                                ->columnSpan(['default' => 6, 'md' => 3]),
                        ]),
                    ]),
            ])
            ->statePath('createData');
    }

    protected function handleCreate(array $data): void
    {
        $data['aktif']  = $data['aktif'] ?? true;
        $data['urutan'] = $data['urutan'] ?? 0;
        $data['kode']   = $data['kode'] ?? \Illuminate\Support\Str::slug($data['nama'], '_');

        MasterReference::create($data);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MasterReference::query()->orderBy('kategori')->orderBy('urutan')->orderBy('nama'))
            ->paginated(false)
            ->defaultGroup(
                Tables\Grouping\Group::make('kategori')
                    ->getTitleFromRecordUsing(fn (MasterReference $record) => MasterReference::KATEGORI_LABELS[$record->kategori] ?? ucfirst($record->kategori))
                    ->collapsible()
                    ->titlePrefixedWithLabel(false)
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Referensi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->placeholder('-')
                    ->limit(60),

                Tables\Columns\TextColumn::make('urutan')
                    ->label('Urutan')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->label('Filter Kategori')
                    ->options(MasterReference::KATEGORI_LABELS),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Data Referensi')
                    ->modalWidth('3xl')
                    ->slideOver()
                    ->form($this->fullEditFields())
                    ->using(function (MasterReference $record, array $data): MasterReference {
                        $record->update($data);
                        return $record;
                    }),

                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Hapus Data Referensi')
                    ->modalDescription('Referensi ini akan dihapus permanen. Lanjutkan?')
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->emptyStateIcon('heroicon-o-bookmark-square')
            ->emptyStateHeading('Belum ada data referensi')
            ->emptyStateDescription('Tambahkan data referensi baru menggunakan form di atas.');
    }
}
