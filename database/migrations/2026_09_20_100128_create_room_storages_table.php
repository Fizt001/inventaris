<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_storages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_area_id')->constrained('room_areas')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->string('kode', 30)->nullable();
            $table->string('jenis', 30)->default('lemari'); // lemari, rak, meja, laci, kotak, lainnya
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('room_storage_id')->nullable()->after('room_area_id')->constrained('room_storages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_storage_id');
        });

        Schema::dropIfExists('room_storages');
    }
};
