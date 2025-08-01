<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'trabajo_realizado',
        'pieza_afectada',
        'comentarios',
        'precio_mantenimiento',
        'nombre_taller',
        'fecha_mantenimiento',
        'kilometraje',
        'tipo_mantenimiento',
        'estado',
        'proximo_mantenimiento',
        'nivel_prioridad',
        'adjuntos',
    ];

    protected $casts = [
        'fecha_mantenimiento' => 'date',
        'proximo_mantenimiento' => 'date',
        'precio_mantenimiento' => 'decimal:2',
        'adjuntos' => 'array',
    ];

    protected $dates = ['deleted_at'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}