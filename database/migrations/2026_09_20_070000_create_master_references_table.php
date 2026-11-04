<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_references', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 50)->index(); // 'tipe_unit', 'nama_area', dsb.
            $table->string('nama');                  // Label nama tampilan
            $table->string('kode', 50)->nullable();  // Nilai / slug / key
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // Ubah kolom tipe di units dari enum ke string agar fleksibel dan dinamis
        Schema::table('units', function (Blueprint $table) {
            $table->string('tipe', 50)->default('jurusan')->change();
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->enum('tipe', ['jurusan', 'gudang', 'sarana'])->default('jurusan')->change();
        });

        Schema::dropIfExists('master_references');
    }
};
