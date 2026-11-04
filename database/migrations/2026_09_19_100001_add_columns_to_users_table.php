<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom role & unit ke users
        Schema::table('users', function (Blueprint $table) {
            $table->string('unit_id')->nullable()->after('email');
            $table->string('jabatan')->nullable()->after('unit_id');
            $table->boolean('aktif')->default(true)->after('jabatan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['unit_id', 'jabatan', 'aktif']);
        });
    }
};
