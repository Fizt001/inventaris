<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_room_id')->constrained('unit_rooms')->cascadeOnDelete();
            $table->string('nama');                                // Contoh: "Ruang Penyimpanan Alat", "Area Praktik Solder"
            $table->string('kode', 30)->nullable();                // Contoh: "COE-AR01"
            $table->enum('kategori', [
                'praktik', 'penyimpanan', 'instruktur', 'server', 'teori', 'lainnya'
            ])->default('praktik');
            $table->string('penanggung_jawab')->nullable();        // Nama guru / teknisi PJ area
            $table->unsignedSmallInteger('kapasitas')->nullable(); // Kapasitas siswa / pos kerja
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('assets') && !Schema::hasColumn('assets', 'room_area_id')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->foreignId('room_area_id')->nullable()->after('lokasi')->constrained('room_areas')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assets') && Schema::hasColumn('assets', 'room_area_id')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->dropForeign(['room_area_id']);
                $table->dropColumn('room_area_id');
            });
        }

        Schema::dropIfExists('room_areas');
    }
};
