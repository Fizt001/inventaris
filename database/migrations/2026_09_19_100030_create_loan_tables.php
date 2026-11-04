<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Header Peminjaman Alat
        Schema::create('loan_requests', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->foreignId('unit_id')->constrained('units');
            $table->enum('tipe_peminjam', ['siswa', 'guru', 'staff'])->default('siswa');
            $table->string('nama_peminjam');
            $table->string('kelas_jabatan')->nullable();
            $table->foreignId('siswa_id')->nullable()->constrained('siswas')->nullOnDelete();
            $table->foreignId('pegawai_id')->nullable()->constrained('pegawais')->nullOnDelete();
            $table->text('keperluan')->nullable();
            $table->date('tgl_pinjam');
            $table->date('tgl_rencana_kembali');
            $table->date('tgl_kembali_aktual')->nullable();
            $table->enum('status', [
                'draft', 'disetujui', 'dipinjam', 'sebagian_kembali', 'selesai', 'terlambat'
            ])->default('draft');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tgl_disetujui')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Detail Item per Peminjaman
        Schema::create('loan_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_request_id')->constrained('loan_requests')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items');
            $table->integer('qty_dipinjam');
            $table->integer('qty_kembali')->default(0);
            $table->enum('kondisi_awal', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik');
            $table->enum('kondisi_kembali', ['baik', 'rusak_ringan', 'rusak_berat', 'hilang'])->nullable();
            $table->text('catatan_kondisi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_request_items');
        Schema::dropIfExists('loan_requests');
    }
};
