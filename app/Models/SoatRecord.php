<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoatRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'fecha_emision',
        'fecha_vencimiento',
        'numero_poliza',
        'compania_aseguradora',
        'valor_pagado',
        'estado',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'valor_pagado' => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}