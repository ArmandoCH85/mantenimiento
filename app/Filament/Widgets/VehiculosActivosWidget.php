<?php

namespace App\Filament\Widgets;

use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class VehiculosActivosWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Obtener estadísticas de vehículos
        $totalVehiculos = Vehicle::count();
        $vehiculosActivos = Vehicle::whereNull('deleted_at')->count();
        $vehiculosInactivos = Vehicle::onlyTrashed()->count();
        
        // Estadísticas por marca
        $marcasPopulares = Vehicle::select('marca', DB::raw('count(*) as total'))
            ->groupBy('marca')
            ->orderBy('total', 'desc')
            ->limit(3)
            ->get();

        // Vehículos por año
        $vehiculosRecientes = Vehicle::where('anio', '>=', now()->year - 5)->count();
        $vehiculosAntiguos = Vehicle::where('anio', '<', now()->year - 5)->count();

        // Crear descripción de marcas populares
        $marcasTexto = $marcasPopulares->map(function ($marca) {
            return "{$marca->marca} ({$marca->total})";
        })->join(', ');

        return [
            Stat::make('🚗 Total de Vehículos', $totalVehiculos)
                ->description('Vehículos registrados en el sistema')
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary')
                ->chart([7, 12, 8, 15, 18, 22, $totalVehiculos]),

            Stat::make('✅ Vehículos Activos', $vehiculosActivos)
                ->description('Vehículos en operación')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([5, 8, 12, 15, 18, 20, $vehiculosActivos]),

            Stat::make('🏭 Marcas Populares', $marcasPopulares->count())
                ->description($marcasTexto ?: 'No hay datos disponibles')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),

            Stat::make('🆕 Vehículos Recientes', $vehiculosRecientes)
                ->description('Modelos 2020 o más nuevos')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('warning')
                ->chart([2, 4, 6, 8, 10, 12, $vehiculosRecientes]),

            Stat::make('⚠️ Vehículos Antiguos', $vehiculosAntiguos)
                ->description('Modelos anteriores a 2020')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('📊 Estado General', round(($vehiculosActivos / max($totalVehiculos, 1)) * 100, 1) . '%')
                ->description('Porcentaje de vehículos activos')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($vehiculosActivos / max($totalVehiculos, 1) > 0.8 ? 'success' : 'warning'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}