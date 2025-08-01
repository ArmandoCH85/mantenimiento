<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soat_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->string('numero_poliza', 30)->nullable();
            $table->string('compania_aseguradora', 100)->nullable();
            $table->decimal('valor_pagado', 10, 2)->nullable();
            
            $table->enum('estado', ['vigente', 'proximo_a_vencer', 'vencido'])->default('vigente');
            
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->index(['vehicle_id', 'estado']);
            $table->index('fecha_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soat_records');
    }
};