<?php

namespace App\Filament\Kepalab\Resources;

use App\Filament\Kepalab\Resources\RoomAreaResource\Pages;
use App\Models\RoomArea;
use App\Models\UnitRoom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RoomAreaResource extends Resource
{
    protected static ?string $model = RoomArea::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Ruang dan Master';
    protected static ?string $navigationLabel = 'Ruang & Area Lab';
    protected static ?string $modelLabel = 'Ruang & Area Lab';
    protected static ?string $pluralModelLabel = 'Daftar Ruang & Area Lab';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('room', fn ($q) => $q->where('unit_id', auth()->user()?->unit_id))
            ->with(['room.unit']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('unit_room_id')
                    ->label('Ruang / Lab')
                    ->options(fn () => UnitRoom::where('unit_id', auth()->user()?->unit_id)
                        ->where('aktif', true)
                        ->orderBy('urutan')
                        ->orderBy('nama')
                        ->pluck('nama', 'id')
                    )
                    ->required()
                    ->searchable()
                    ->helperText('Pilih lab tempat area kerja berada.'),

                Forms\Components\TextInput::make('nama')
                    ->label('Nama Area Kerja / Bagian')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Contoh: Area Praktik Perakitan, Area Solder, Meja Praktik A'),

                Forms\Components\TextInput::make('penanggung_jawab')
                    ->label('Penanggung Jawab Area')
                    ->placeholder('Contoh: Budi Santoso, S.T. & Siti Aminah (Bisa lebih dari 1 orang)')
                    ->helperText('Dapat diisi 2 orang atau laboran/guru pendamping lab.')
                    ->maxLength(200)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan / Fasilitas')
                    ->placeholder('Catatan fasilitas atau peruntukan area')
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
                    ->label('Ruang / Lab')
                    ->getTitleFromRecordUsing(fn (RoomArea $record) =>
                        $record->room ? "Lab: {$record->room->nama}" : 'Tanpa Lab'
                    )
                    ->collapsible()
                    ->titlePrefixedWithLabel(false)
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Area Kerja')
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
                    ->modalHeading('Hapus Area Lab')
                    ->modalDescription(fn (RoomArea $record) => "Hapus area '{$record->nama}'?"),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRoomAreas::route('/'),
        ];
    }
}
