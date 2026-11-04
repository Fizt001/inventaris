<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->string('nama');                        // e.g. "Lab 1", "Ruang Teori"
            $table->string('kode', 20)->nullable();        // e.g. "R.LAB-1"
            $table->unsignedSmallInteger('kapasitas')->nullable(); // jumlah siswa
            $table->string('lantai', 10)->nullable();      // e.g. "1", "2"
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_rooms');
    }
};
