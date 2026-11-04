<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('unit_room_id')->nullable()->after('unit_id')->constrained('unit_rooms')->nullOnDelete();
            $table->foreignId('room_area_id')->nullable()->after('unit_room_id')->constrained('room_areas')->nullOnDelete();
            $table->string('rak_posisi', 100)->nullable()->after('room_area_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['room_area_id']);
            $table->dropForeign(['unit_room_id']);
            $table->dropColumn(['unit_room_id', 'room_area_id', 'rak_posisi']);
        });
    }
};
