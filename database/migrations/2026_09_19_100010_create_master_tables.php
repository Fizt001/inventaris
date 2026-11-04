<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Profil Sekolah (singleton - 1 row)
        Schema::create('sekolah', function (Blueprint $table) {
            $table->id();
            // Identitas
            $table->string('nama_sekolah');
            $table->string('npsn', 30)->nullable();
            $table->string('nss', 30)->nullable();
            $table->string('nama_yayasan')->nullable();
            $table->string('akreditasi', 10)->nullable()->default('A');
            $table->string('kepala_sekolah')->nullable();
            $table->string('nip_kepala_sekolah', 30)->nullable();
            // Alamat
            $table->text('alamat')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kabupaten_kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kode_pos', 10)->nullable();
            // Kontak
            $table->string('telepon', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website', 150)->nullable();
            // Visi Misi
            $table->text('visi')->nullable();
            $table->text('misi')->nullable();
            // Visual
            $table->string('logo')->nullable();
            $table->string('logo_dinas')->nullable();
            $table->string('foto_sekolah_1')->nullable();
            $table->string('foto_sekolah_2')->nullable();
            $table->string('foto_sekolah_3')->nullable();
            $table->timestamps();
        });

        // Unit / Lab / Gudang
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode', 10)->unique();
            $table->enum('tipe', ['jurusan', 'gudang', 'sarana'])->default('jurusan');
            $table->string('kepala_nama')->nullable();
            $table->string('kepala_nip', 30)->nullable();
            $table->string('kepala_jabatan')->nullable();
            $table->string('ruangan')->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // Kategori Barang
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('nama');
            $table->string('slug')->nullable();
            $table->string('icon')->nullable()->default('heroicon-o-tag');
            $table->string('warna', 20)->nullable()->default('gray');
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // Master Supplier
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode', 20)->unique()->nullable();
            $table->string('kontak_person')->nullable();
            $table->string('telepon', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('alamat')->nullable();
            $table->string('kota')->nullable();
            $table->string('npwp', 30)->nullable();
            $table->string('rekening_bank')->nullable();
            $table->string('nama_rekening')->nullable();
            $table->text('catatan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // Master Barang (INTI)
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('unit_id')->constrained('units');
            $table->enum('tipe_barang', ['consumable', 'returnable', 'fixed_asset'])->default('consumable');
            $table->string('satuan', 30)->default('pcs');
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('harga_referensi', 15, 2)->default(0);
            $table->boolean('ppn_termasuk')->default(false);
            $table->integer('stok_minimum')->default(0);
            $table->string('foto')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->text('catatan_harga')->nullable();
            $table->text('spesifikasi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // Riwayat Harga per Barang per Supplier
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('harga', 15, 2);
            $table->date('tanggal');
            $table->enum('sumber', ['pembelian', 'manual'])->default('manual');
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Master Siswa (untuk dropdown peminjaman)
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 20)->nullable();
            $table->string('nama');
            $table->string('kelas');
            $table->string('jurusan')->nullable();
            $table->enum('status', ['aktif', 'alumni', 'keluar'])->default('aktif');
            $table->timestamps();
        });

        // Master Guru/Staff (untuk dropdown peminjaman)
        Schema::create('pegawais', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 30)->nullable();
            $table->string('nama');
            $table->string('jabatan')->nullable();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->enum('status', ['aktif', 'tidak_aktif'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawais');
        Schema::dropIfExists('siswas');
        Schema::dropIfExists('price_histories');
        Schema::dropIfExists('items');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('units');
        Schema::dropIfExists('sekolah');
    }
};
