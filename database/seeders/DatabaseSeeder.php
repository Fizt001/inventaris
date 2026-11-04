<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Sekolah;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ═══════════════════════════════════════════
        // 1. ROLES
        // ═══════════════════════════════════════════
        $roles = [
            'super_admin',
            'kepala_lab',
            'kepala_gudang',
            'wakasarpras',
            'kepsek',
        ];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // ═══════════════════════════════════════════
        // 2. PROFIL SEKOLAH (singleton)
        // ═══════════════════════════════════════════
        Sekolah::firstOrCreate(['id' => 1], [
            'nama_sekolah'      => 'SMK Negeri 1 Contoh',
            'npsn'              => '12345678',
            'akreditasi'        => 'A',
            'kepala_sekolah'    => 'Nama Kepala Sekolah, S.Pd., M.M.',
            'nip_kepala_sekolah' => '196801011990031001',
            'alamat'            => 'Jl. Pendidikan No. 1',
            'kecamatan'         => 'Kecamatan Contoh',
            'kabupaten_kota'    => 'Kota Contoh',
            'provinsi'          => 'Jawa Barat',
            'kode_pos'          => '40000',
            'telepon'           => '(022) 123456',
            'email'             => 'info@smknegeri1contoh.sch.id',
        ]);

        // ═══════════════════════════════════════════
        // 3. UNITS (Jurusan + Gudang)
        // ═══════════════════════════════════════════
        $units = [
            ['nama' => 'Teknik Elektronika',       'kode' => 'TEK', 'tipe' => 'jurusan', 'urutan' => 1],
            ['nama' => 'Teknik Komputer & Jaringan','kode' => 'TKJ', 'tipe' => 'jurusan', 'urutan' => 2],
            ['nama' => 'Teknik Otomotif',           'kode' => 'OTO', 'tipe' => 'jurusan', 'urutan' => 3],
            ['nama' => 'Akuntansi',                 'kode' => 'AKT', 'tipe' => 'jurusan', 'urutan' => 4],
            ['nama' => 'Gudang ATK Pusat',          'kode' => 'GDG', 'tipe' => 'gudang',  'urutan' => 5],
        ];
        foreach ($units as $u) {
            Unit::firstOrCreate(['kode' => $u['kode']], $u);
        }

        // ═══════════════════════════════════════════
        // 4. CATEGORIES (per jurusan + umum)
        // ═══════════════════════════════════════════
        $tek = Unit::where('kode', 'TEK')->first();
        $tkj = Unit::where('kode', 'TKJ')->first();
        $oto = Unit::where('kode', 'OTO')->first();
        $akt = Unit::where('kode', 'AKT')->first();
        $gdg = Unit::where('kode', 'GDG')->first();

        $categories = [
            // TEK
            ['unit_id' => $tek->id, 'nama' => 'Komponen Elektronika', 'icon' => 'heroicon-o-cpu-chip',       'warna' => 'blue',    'urutan' => 1],
            ['unit_id' => $tek->id, 'nama' => 'Bahan Praktik TEK',    'icon' => 'heroicon-o-beaker',          'warna' => 'indigo',  'urutan' => 2],
            ['unit_id' => $tek->id, 'nama' => 'Alat Ukur Elektronika','icon' => 'heroicon-o-variable',        'warna' => 'purple',  'urutan' => 3],
            ['unit_id' => $tek->id, 'nama' => 'Alat Tangan TEK',      'icon' => 'heroicon-o-wrench-screwdriver','warna' => 'gray',  'urutan' => 4],
            // TKJ
            ['unit_id' => $tkj->id, 'nama' => 'Bahan Jaringan',       'icon' => 'heroicon-o-signal',          'warna' => 'cyan',   'urutan' => 1],
            ['unit_id' => $tkj->id, 'nama' => 'Alat Jaringan',        'icon' => 'heroicon-o-wrench-screwdriver','warna' => 'teal', 'urutan' => 2],
            ['unit_id' => $tkj->id, 'nama' => 'Perangkat Komputer',   'icon' => 'heroicon-o-computer-desktop', 'warna' => 'blue',  'urutan' => 3],
            ['unit_id' => $tkj->id, 'nama' => 'Bahan Habis Pakai TKJ','icon' => 'heroicon-o-beaker',           'warna' => 'sky',   'urutan' => 4],
            // OTO
            ['unit_id' => $oto->id, 'nama' => 'Bahan Habis Pakai OTO','icon' => 'heroicon-o-beaker',           'warna' => 'orange','urutan' => 1],
            ['unit_id' => $oto->id, 'nama' => 'Bahan Praktik OTO',    'icon' => 'heroicon-o-wrench',           'warna' => 'red',   'urutan' => 2],
            ['unit_id' => $oto->id, 'nama' => 'Alat Tangan OTO',      'icon' => 'heroicon-o-wrench-screwdriver','warna' => 'amber','urutan' => 3],
            ['unit_id' => $oto->id, 'nama' => 'Alat Khusus OTO',      'icon' => 'heroicon-o-cog-6-tooth',      'warna' => 'yellow','urutan' => 4],
            ['unit_id' => $oto->id, 'nama' => 'Unit / Mock-up OTO',   'icon' => 'heroicon-o-truck',             'warna' => 'stone', 'urutan' => 5],
            // AKT
            ['unit_id' => $akt->id, 'nama' => 'ATK Akuntansi',        'icon' => 'heroicon-o-pencil',           'warna' => 'green', 'urutan' => 1],
            ['unit_id' => $akt->id, 'nama' => 'Perangkat Komputer AKT','icon' => 'heroicon-o-computer-desktop','warna' => 'emerald','urutan' => 2],
            // GDG
            ['unit_id' => $gdg->id, 'nama' => 'ATK Umum',             'icon' => 'heroicon-o-pencil-square',    'warna' => 'gray',  'urutan' => 1],
            ['unit_id' => $gdg->id, 'nama' => 'Bahan Kebersihan',     'icon' => 'heroicon-o-sparkles',         'warna' => 'lime',  'urutan' => 2],
            ['unit_id' => $gdg->id, 'nama' => 'Perlengkapan Kantor',  'icon' => 'heroicon-o-building-office',  'warna' => 'slate', 'urutan' => 3],
        ];

        foreach ($categories as $c) {
            Category::firstOrCreate(
                ['unit_id' => $c['unit_id'], 'nama' => $c['nama']],
                $c
            );
        }

        // ═══════════════════════════════════════════
        // 5. USERS (Admin + satu per role)
        // ═══════════════════════════════════════════
        $superAdmin = User::firstOrCreate(['email' => 'admin@inventaris.smk'], [
            'name'     => 'Administrator',
            'password' => bcrypt('password'),
            'aktif'    => true,
        ]);
        $superAdmin->syncRoles(['super_admin']);

        $kepalaTek = User::firstOrCreate(['email' => 'kepala.tek@inventaris.smk'], [
            'name'     => 'Kepala Lab Elektronika',
            'password' => bcrypt('password'),
            'unit_id'  => $tek->id,
            'jabatan'  => 'Kepala Lab TEK',
            'aktif'    => true,
        ]);
        $kepalaTek->syncRoles(['kepala_lab']);

        $kepalaTkj = User::firstOrCreate(['email' => 'kepala.tkj@inventaris.smk'], [
            'name'     => 'Kepala Lab TKJ',
            'password' => bcrypt('password'),
            'unit_id'  => $tkj->id,
            'jabatan'  => 'Kepala Lab TKJ',
            'aktif'    => true,
        ]);
        $kepalaTkj->syncRoles(['kepala_lab']);

        $kepalaOto = User::firstOrCreate(['email' => 'kepala.oto@inventaris.smk'], [
            'name'     => 'Kepala Lab Otomotif',
            'password' => bcrypt('password'),
            'unit_id'  => $oto->id,
            'jabatan'  => 'Kepala Lab OTO',
            'aktif'    => true,
        ]);
        $kepalaOto->syncRoles(['kepala_lab']);

        $kepalaAkt = User::firstOrCreate(['email' => 'kepala.akt@inventaris.smk'], [
            'name'     => 'Kepala Lab Akuntansi',
            'password' => bcrypt('password'),
            'unit_id'  => $akt->id,
            'jabatan'  => 'Kepala Lab AKT',
            'aktif'    => true,
        ]);
        $kepalaAkt->syncRoles(['kepala_lab']);

        $kepalaGdg = User::firstOrCreate(['email' => 'kepala.gudang@inventaris.smk'], [
            'name'     => 'Kepala Gudang ATK',
            'password' => bcrypt('password'),
            'unit_id'  => $gdg->id,
            'jabatan'  => 'Kepala Gudang',
            'aktif'    => true,
        ]);
        $kepalaGdg->syncRoles(['kepala_gudang']);

        $wakaUser = User::firstOrCreate(['email' => 'wakasarpras@inventaris.smk'], [
            'name'     => 'Wakasarpras',
            'password' => bcrypt('password'),
            'jabatan'  => 'Wakil Kepala Sarana & Prasarana',
            'aktif'    => true,
        ]);
        $wakaUser->syncRoles(['wakasarpras']);

        $kepsekUser = User::firstOrCreate(['email' => 'kepsek@inventaris.smk'], [
            'name'     => 'Kepala Sekolah',
            'password' => bcrypt('password'),
            'jabatan'  => 'Kepala Sekolah',
            'aktif'    => true,
        ]);
        $kepsekUser->syncRoles(['kepsek']);

        $this->command->info('✅ Seeder selesai! ' . count($roles) . ' roles, 5 units, ' . count($categories) . ' kategori, 7 users.');
        $this->command->table(
            ['Email', 'Role', 'Password'],
            [
                ['admin@inventaris.smk',         'super_admin',   'password'],
                ['kepala.tek@inventaris.smk',     'kepala_lab',    'password'],
                ['kepala.tkj@inventaris.smk',     'kepala_lab',    'password'],
                ['kepala.oto@inventaris.smk',     'kepala_lab',    'password'],
                ['kepala.akt@inventaris.smk',     'kepala_lab',    'password'],
                ['kepala.gudang@inventaris.smk',  'kepala_gudang', 'password'],
                ['wakasarpras@inventaris.smk',    'wakasarpras',   'password'],
                ['kepsek@inventaris.smk',         'kepsek',        'password'],
            ]
        );
    }
}
