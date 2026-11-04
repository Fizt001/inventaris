<?php

namespace App\Filament\Gudang\Resources;

use App\Filament\Gudang\Resources\GudangAreaResource\Pages;
use App\Models\RoomArea;
use App\Models\Unit;
use App\Models\UnitRoom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GudangAreaResource extends Resource
{
    protected static ?string $model = RoomArea::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Ruang dan Master';
    protected static ?string $navigationLabel = 'Ruang & Area Gudang';
    protected static ?string $modelLabel = 'Area Gudang';
    protected static ?string $pluralModelLabel = 'Daftar Ruang & Area Gudang';
    protected static ?int $navigationSort = 1;

    protected static function getGudangUnitId(): int
    {
        return auth()->user()?->unit_id
            ?? Unit::where('tipe', 'gudang')->value('id')
            ?? 1;
    }

    public static function getEloquentQuery(): Builder
    {
        $unitId = static::getGudangUnitId();

        return parent::getEloquentQuery()
            ->whereHas('room', fn ($q) => $q->where('unit_id', $unitId))
            ->with(['room.unit']);
    }

    public static function form(Form $form): Form
    {
        $unitId = static::getGudangUnitId();

        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('unit_room_id')
                    ->label('Ruang / Gedung Gudang')
                    ->options(fn () => UnitRoom::where('unit_id', $unitId)
                        ->where('aktif', true)
                        ->orderBy('urutan')
                        ->orderBy('nama')
                        ->pluck('nama', 'id')
                    )
                    ->required()
                    ->searchable()
                    ->helperText('Pilih gedung gudang tempat area berada.'),

                Forms\Components\TextInput::make('nama')
                    ->label('Nama Area / Zona Gudang')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Contoh: Area ATK & Kertas, Zona Logistik, Rak Peralatan'),

                Forms\Components\TextInput::make('penanggung_jawab')
                    ->label('Penanggung Jawab Area')
                    ->placeholder('Contoh: Budi Santoso & Ahmad (Bisa lebih dari 1 orang)')
                    ->helperText('Dapat diisi 2 orang atau staf penjaga area gudang.')
                    ->maxLength(200)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan / Fasilitas')
                    ->placeholder('Catatan peruntukan atau fasilitas area gudang')
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('aktif')
                    ->label('Status Area Aktif')
                    ->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->defaultGroup(
                Tables\Grouping\Group::make('unit_room_id')
                    ->label('Ruang / Gedung Gudang')
                    ->getTitleFromRecordUsing(fn (RoomArea $record) =>
                        $record->room ? "Gudang: {$record->room->nama}" : 'Gudang Utama'
                    )
                    ->collapsible()
                    ->titlePrefixedWithLabel(false)
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Area / Zona')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('penanggung_jawab')
                    ->label('Penanggung Jawab')
                    ->searchable()
                    ->placeholder('-')
                    ->icon('heroicon-m-user')
                    ->color('gray')
                    ->wrap(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->placeholder('-')
                    ->wrap(),

                Tables\Columns\IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth('2xl')
                    ->slideOver(),

                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Hapus Area Gudang')
                    ->modalDescription(fn (RoomArea $record) => "Hapus area '{$record->nama}'?"),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageGudangAreas::route('/'),
        ];
    }
}
