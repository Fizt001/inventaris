<?php

namespace App\Filament\Admin\Pages;

use App\Models\Unit;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ManageUsers extends InlineManagePage
{
    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Pengguna & Akses';
    protected static ?string $navigationLabel = 'Pengguna & Penugasan';
    protected static ?string $title           = 'Pengguna & Penugasan Lab / Gudang';
    protected static ?string $slug            = 'manage-users';
    protected static ?int    $navigationSort  = 1;

    public function getCreateHeading(): string
    {
        return 'Tambah Pengguna & Penugasan Baru';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Identitas Pengguna')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Budi Santoso, S.T.'),

                        Forms\Components\TextInput::make('email')
                            ->label('Email Login')
                            ->email()
                            ->required()
                            ->unique('users', 'email')
                            ->placeholder('budi@inventaris.smk'),

                        Forms\Components\TextInput::make('password')
                            ->label('Password Awal')
                            ->password()
                            ->required()
                            ->minLength(6)
                            ->placeholder('Minimal 6 karakter'),
                    ]),

                Forms\Components\Fieldset::make('Peran & Penugasan Unit / Lab / Gudang')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->label('Peran (Hak Akses)')
                            ->options([
                                'kepala_lab'    => 'Kepala Lab (Akses Panel /kepalab)',
                                'kepala_gudang' => 'Kepala Gudang (Akses Panel /gudang)',
                                'wakasarpras'   => 'Wakasarpras (Akses Panel /wakasarpras)',
                                'kepsek'        => 'Kepala Sekolah (Akses Panel /kepsek)',
                                'super_admin'   => 'Administrator Sistem (Akses Panel /admin)',
                            ])
                            ->required()
                            ->reactive()
                            ->helperText('Menentukan panel login pengguna.'),

                        Forms\Components\Select::make('unit_id')
                            ->label('Unit / Jurusan / Gudang yang Ditugaskan')
                            ->options(Unit::where('aktif', true)->orderBy('urutan')->pluck('nama', 'id'))
                            ->required(fn (callable $get) => in_array($get('role'), ['kepala_lab', 'kepala_gudang']))
                            ->nullable()
                            ->helperText('Pengguna akan otomatis mengelola data unit ini saat login.'),

                        Forms\Components\TextInput::make('jabatan')
                            ->label('Jabatan / Keterangan')
                            ->placeholder('Contoh: Kepala Lab Elektronika Industri')
                            ->maxLength(100),
                    ]),
            ])
            ->statePath('createData');
    }

    protected function handleCreate(array $data): void
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'unit_id'  => $data['unit_id'] ?? null,
            'jabatan'  => $data['jabatan'] ?? null,
            'aktif'    => true,
        ]);

        if (! empty($data['role'])) {
            $user->assignRole($data['role']);
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::with(['unit', 'roles']))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pengguna')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email Login')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Peran (Role)')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'super_admin'   => 'danger',
                        'kepala_lab'    => 'indigo',
                        'kepala_gudang' => 'emerald',
                        'wakasarpras'   => 'warning',
                        'kepsek'        => 'purple',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match($state) {
                        'super_admin'   => 'Super Admin',
                        'kepala_lab'    => 'Kepala Lab',
                        'kepala_gudang' => 'Kepala Gudang',
                        'wakasarpras'   => 'Wakasarpras',
                        'kepsek'        => 'Kepala Sekolah',
                        default         => $state,
                    }),

                Tables\Columns\TextColumn::make('unit.nama')
                    ->label('Unit / Penugasan')
                    ->placeholder('Semua Unit / Tidak Ada')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->placeholder('-'),

                Tables\Columns\IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->mutateRecordDataUsing(function (array $data, User $record): array {
                        $data['role'] = $record->roles->first()?->name;
                        return $data;
                    })
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required(),

                        Forms\Components\TextInput::make('email')
                            ->label('Email Login')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('password')
                            ->label('Password Baru (Kosongkan jika tidak diubah)')
                            ->password()
                            ->nullable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state)),

                        Forms\Components\Select::make('role')
                            ->label('Peran (Hak Akses)')
                            ->options([
                                'kepala_lab'    => 'Kepala Lab (/kepalab)',
                                'kepala_gudang' => 'Kepala Gudang (/gudang)',
                                'wakasarpras'   => 'Wakasarpras (/wakasarpras)',
                                'kepsek'        => 'Kepala Sekolah (/kepsek)',
                                'super_admin'   => 'Administrator Sistem (/admin)',
                            ])
                            ->required()
                            ->reactive(),

                        Forms\Components\Select::make('unit_id')
                            ->label('Unit / Jurusan / Gudang yang Ditugaskan')
                            ->options(Unit::where('aktif', true)->orderBy('urutan')->pluck('nama', 'id'))
                            ->nullable(),

                        Forms\Components\TextInput::make('jabatan')
                            ->label('Jabatan')
                            ->nullable(),

                        Forms\Components\Toggle::make('aktif')
                            ->label('Status Akun Aktif')
                            ->default(true),
                    ])
                    ->using(function (User $record, array $data): User {
                        $role = $data['role'] ?? null;
                        unset($data['role']);

                        $record->update($data);

                        if ($role) {
                            $record->syncRoles([$role]);
                        }

                        return $record;
                    }),

                Tables\Actions\DeleteAction::make()
                    ->disabled(fn (User $record) => $record->id === auth()->id())
                    ->modalHeading('Hapus Pengguna')
                    ->modalDescription(fn (User $record) => "Akun '{$record->name}' akan dihapus permanen. Lanjutkan?"),
            ])
            ->defaultSort('name');
    }
}
