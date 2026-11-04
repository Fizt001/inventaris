<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit_rooms', function (Blueprint $table) {
            $table->string('penanggung_jawab')->nullable()->after('nama');
        });
    }

    public function down(): void
    {
        Schema::table('unit_rooms', function (Blueprint $table) {
            $table->dropColumn('penanggung_jawab');
        });
    }
};
