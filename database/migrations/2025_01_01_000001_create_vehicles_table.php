<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('placa', 10)->unique();
            $table->string('marca', 50);
            $table->string('modelo', 50);
            $table->year('anio')->nullable();
            $table->string('numero_motor', 50)->nullable();
            $table->string('numero_chasis', 50)->nullable();
            $table->string('color', 30)->nullable();
            $table->string('tipo_vehiculo', 30)->nullable();
            $table->date('fecha_compra')->nullable();
            $table->string('propietario_actual', 100)->nullable();
            $table->unsignedInteger('kilometraje_actual')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('placa');
            $table->index(['marca', 'modelo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};