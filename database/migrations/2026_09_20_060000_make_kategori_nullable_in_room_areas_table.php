<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_areas', function (Blueprint $table) {
            $table->string('kategori', 50)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('room_areas', function (Blueprint $table) {
            $table->string('kategori', 50)->nullable(false)->default('praktik')->change();
        });
    }
};
