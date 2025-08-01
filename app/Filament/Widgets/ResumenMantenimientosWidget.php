<?php

namespace App\Filament\Widgets;

use App\Models\Maintenance;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ResumenMantenimientosWidget extends ChartWidget
{
    protected static ?string $heading = '🔧 Resumen de Mantenimientos';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Obtener mantenimientos de los últimos 12 meses
        $mantenimientosPorMes = Maintenance::select(
                DB::raw('MONTH(fecha_mantenimiento) as mes'),
                DB::raw('YEAR(fecha_mantenimiento) as anio'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(precio_mantenimiento) as costo_total')
            )
            ->where('fecha_mantenimiento', '>=', now()->subMonths(12))
            ->groupBy('anio', 'mes')
            ->orderBy('anio', 'asc')
            ->orderBy('mes', 'asc')
            ->get();

        // Preparar datos para el gráfico
        $labels = [];
        $cantidades = [];
        $costos = [];

        // Generar etiquetas para los últimos 12 meses
        for ($i = 11; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $mes = $fecha->month;
            $anio = $fecha->year;
            
            $labels[] = $fecha->format('M Y');
            
            // Buscar datos para este mes
            $datosDelMes = $mantenimientosPorMes->where('mes', $mes)->where('anio', $anio)->first();
            
            $cantidades[] = $datosDelMes ? $datosDelMes->total : 0;
            $costos[] = $datosDelMes ? (float) $datosDelMes->costo_total : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Cantidad de Mantenimientos',
                    'data' => $cantidades,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Costo Total (Miles COP)',
                    'data' => array_map(fn($costo) => round($costo / 1000, 1), $costos),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'plugins' => [
                'title' => [
                    'display' => true,
                    'text' => 'Tendencia de Mantenimientos - Últimos 12 Meses',
                    'font' => [
                        'size' => 16,
                        'weight' => 'bold',
                    ],
                ],
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'titleColor' => 'white',
                    'bodyColor' => 'white',
                    'borderColor' => 'rgba(255, 255, 255, 0.1)',
                    'borderWidth' => 1,
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Período',
                        'font' => [
                            'weight' => 'bold',
                        ],
                    ],
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ],
                ],
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Cantidad de Mantenimientos',
                        'color' => 'rgb(59, 130, 246)',
                        'font' => [
                            'weight' => 'bold',
                        ],
                    ],
                    'grid' => [
                        'color' => 'rgba(59, 130, 246, 0.1)',
                    ],
                    'ticks' => [
                        'color' => 'rgb(59, 130, 246)',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Costo Total (Miles COP)',
                        'color' => 'rgb(16, 185, 129)',
                        'font' => [
                            'weight' => 'bold',
                        ],
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                        'color' => 'rgba(16, 185, 129, 0.1)',
                    ],
                    'ticks' => [
                        'color' => 'rgb(16, 185, 129)',
                    ],
                ],
            ],
            'elements' => [
                'point' => [
                    'radius' => 4,
                    'hoverRadius' => 6,
                ],
            ],
        ];
    }

    public function getDescription(): ?string
    {
        $totalMantenimientos = Maintenance::count();
        $costoTotal = Maintenance::sum('precio_mantenimiento');
        $promedioMensual = Maintenance::where('fecha_mantenimiento', '>=', now()->subMonths(12))->count() / 12;
        
        return "📊 Total: {$totalMantenimientos} mantenimientos | 💰 Costo total: $" . number_format($costoTotal, 0, ',', '.') . " COP | 📈 Promedio mensual: " . round($promedioMensual, 1);
    }
}