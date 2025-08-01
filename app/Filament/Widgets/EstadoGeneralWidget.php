<?php

namespace App\Filament\Widgets;

use App\Models\Vehicle;
use App\Models\SoatRecord;
use App\Models\RevisionTecnicaRecord;
use App\Models\Maintenance;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class EstadoGeneralWidget extends ChartWidget
{
    protected static ?string $heading = '📊 Estado General del Sistema';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Obtener datos de vehículos
        $totalVehiculos = Vehicle::count();
        $vehiculosActivos = Vehicle::whereNull('deleted_at')->count();

        // Obtener datos de documentos
        $soatVigentes = SoatRecord::where('fecha_vencimiento', '>', now())->count();
        $soatVencidos = SoatRecord::where('fecha_vencimiento', '<=', now())->count();
        $revisionVigente = RevisionTecnicaRecord::where('fecha_vencimiento', '>', now())->count();
        $revisionVencida = RevisionTecnicaRecord::where('fecha_vencimiento', '<=', now())->count();

        // Obtener datos de mantenimientos
        $mantenimientosCompletados = Maintenance::where('estado', 'completado')->count();
        $mantenimientosPendientes = Maintenance::whereIn('estado', ['programado', 'pendiente'])->count();

        return [
            'datasets' => [
                [
                    'label' => 'Estado del Sistema',
                    'data' => [
                        $vehiculosActivos,
                        $soatVigentes,
                        $revisionVigente,
                        $mantenimientosCompletados,
                        $soatVencidos + $revisionVencida,
                        $mantenimientosPendientes
                    ],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',   // Verde - Vehículos activos
                        'rgba(59, 130, 246, 0.8)',  // Azul - SOAT vigentes
                        'rgba(168, 85, 247, 0.8)',  // Púrpura - Revisión vigente
                        'rgba(16, 185, 129, 0.8)',  // Verde agua - Mantenimientos completados
                        'rgba(239, 68, 68, 0.8)',   // Rojo - Documentos vencidos
                        'rgba(245, 158, 11, 0.8)',  // Amarillo - Mantenimientos pendientes
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(59, 130, 246)',
                        'rgb(168, 85, 247)',
                        'rgb(16, 185, 129)',
                        'rgb(239, 68, 68)',
                        'rgb(245, 158, 11)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                '🚗 Vehículos Activos',
                '🛡️ SOAT Vigentes',
                '🔧 Revisión Vigente',
                '✅ Mantenimientos OK',
                '❌ Documentos Vencidos',
                '⏳ Mantenimientos Pendientes'
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'title' => [
                    'display' => true,
                    'text' => 'Resumen Ejecutivo - Estado Actual del Sistema',
                    'font' => [
                        'size' => 16,
                        'weight' => 'bold',
                    ],
                ],
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 20,
                        'font' => [
                            'size' => 12,
                        ],
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'titleColor' => 'white',
                    'bodyColor' => 'white',
                    'borderColor' => 'rgba(255, 255, 255, 0.1)',
                    'borderWidth' => 1,
                    'callbacks' => [
                        'label' => 'function(context) {
                            const label = context.label || "";
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ": " + value + " (" + percentage + "%)";
                        }'
                    ],
                ],
            ],
            'cutout' => '50%',
            'elements' => [
                'arc' => [
                    'borderWidth' => 2,
                ],
            ],
        ];
    }

    public function getDescription(): ?string
    {
        $totalVehiculos = Vehicle::count();
        $documentosVencidos = SoatRecord::where('fecha_vencimiento', '<=', now())->count() + 
                             RevisionTecnicaRecord::where('fecha_vencimiento', '<=', now())->count();
        $eficiencia = $totalVehiculos > 0 ? round((($totalVehiculos - $documentosVencidos) / $totalVehiculos) * 100, 1) : 100;
        
        $estado = $eficiencia >= 90 ? '🟢 Excelente' : ($eficiencia >= 70 ? '🟡 Bueno' : '🔴 Requiere Atención');
        
        return "🎯 Eficiencia del Sistema: {$eficiencia}% | Estado: {$estado} | Total Vehículos: {$totalVehiculos}";
    }
}