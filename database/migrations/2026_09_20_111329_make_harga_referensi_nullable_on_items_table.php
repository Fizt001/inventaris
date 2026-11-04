<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('harga_referensi', 15, 2)->nullable()->default(null)->change();
            $table->decimal('harga_satuan', 15, 2)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('harga_referensi', 15, 2)->default(0)->change();
            $table->decimal('harga_satuan', 15, 2)->default(0)->change();
        });
    }
};
