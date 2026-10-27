<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('estudiante')->after('password');
            $table->string('cedula', 10)->nullable()->unique()->after('role');
            $table->foreignId('pais_id')->nullable()->after('cedula')->constrained('paises')->nullOnDelete();
            $table->foreignId('provincia_id')->nullable()->after('pais_id')->constrained('provincias')->nullOnDelete();
            $table->foreignId('ciudad_id')->nullable()->after('provincia_id')->constrained('ciudades')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['ciudad_id']);
            $table->dropForeign(['provincia_id']);
            $table->dropForeign(['pais_id']);
            $table->dropColumn(['role', 'cedula', 'pais_id', 'provincia_id', 'ciudad_id']);
        });
    }
};
