<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Aset Tetap (per unit)
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('kode_aset')->unique();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->foreignId('unit_id')->constrained('units');
            $table->string('nama_aset');
            $table->string('merk')->nullable();
            $table->string('no_seri')->nullable();
            $table->json('spesifikasi')->nullable();
            $table->date('tgl_perolehan')->nullable();
            $table->decimal('harga_perolehan', 15, 2)->default(0);
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat', 'tidak_aktif'])->default('baik');
            $table->string('lokasi')->nullable();
            $table->string('foto')->nullable();
            $table->string('dokumen_faktur')->nullable();
            $table->string('dokumen_garansi')->nullable();
            $table->date('tgl_garansi_berakhir')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // Riwayat Perawatan Aset
        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->date('tgl_perawatan');
            $table->enum('jenis', ['servis', 'perbaikan', 'kalibrasi', 'pengecekan'])->default('pengecekan');
            $table->text('deskripsi');
            $table->decimal('biaya', 15, 2)->default(0);
            $table->string('teknisi')->nullable();
            $table->enum('hasil', ['baik', 'perlu_tindak_lanjut'])->default('baik');
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenances');
        Schema::dropIfExists('assets');
    }
};
