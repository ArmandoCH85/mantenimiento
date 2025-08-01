<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->text('trabajo_realizado');
            $table->string('pieza_afectada', 100);
            $table->text('comentarios')->nullable();
            $table->decimal('precio_mantenimiento', 10, 2);
            $table->string('nombre_taller', 100);
            $table->date('fecha_mantenimiento');
            $table->unsignedInteger('kilometraje')->nullable();
            $table->enum('tipo_mantenimiento', ['preventivo', 'correctivo', 'emergencia'])->nullable();
            $table->enum('estado', ['completado', 'pendiente', 'en_proceso'])->default('completado');
            $table->date('proximo_mantenimiento')->nullable();
            $table->enum('nivel_prioridad', ['baja', 'media', 'alta', 'critica'])->default('media');
            $table->json('adjuntos')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->index(['vehicle_id', 'fecha_mantenimiento']);
            $table->index('nombre_taller');
            $table->index(['tipo_mantenimiento', 'estado']);
            $table->index('proximo_mantenimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};