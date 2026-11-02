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
        Schema::table('laporan', function (Blueprint $table): void {
            $table->index(['latitude', 'longitude'], 'laporan_coordinates_index');
            $table->index(['user_id', 'latitude', 'longitude'], 'laporan_owner_coordinates_index');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->index(['role', 'name'], 'users_role_name_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_role_name_index');
        });

        Schema::table('laporan', function (Blueprint $table): void {
            $table->dropIndex('laporan_owner_coordinates_index');
            $table->dropIndex('laporan_coordinates_index');
        });
    }
};
