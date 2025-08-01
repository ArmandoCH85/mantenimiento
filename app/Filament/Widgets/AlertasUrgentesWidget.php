<?php

namespace App\Filament\Widgets;

use App\Models\SoatRecord;
use App\Models\RevisionTecnicaRecord;
use App\Models\Maintenance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AlertasUrgentesWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        // Documentos vencidos
        $soatVencidos = SoatRecord::where('fecha_vencimiento', '<', now())->count();
        $revisionVencida = RevisionTecnicaRecord::where('fecha_vencimiento', '<', now())->count();
        $totalVencidos = $soatVencidos + $revisionVencida;

        // Documentos por vencer en 30 días
        $soatPorVencer = SoatRecord::whereBetween('fecha_vencimiento', [now(), now()->addDays(30)])->count();
        $revisionPorVencer = RevisionTecnicaRecord::whereBetween('fecha_vencimiento', [now(), now()->addDays(30)])->count();
        $totalPorVencer = $soatPorVencer + $revisionPorVencer;

        // Mantenimientos pendientes (próximo mantenimiento vencido)
        $mantenimientosPendientes = Maintenance::where('proximo_mantenimiento', '<', now())
            ->whereIn('estado', ['programado', 'pendiente'])
            ->count();

        // Costo total de mantenimientos este mes
        $costoEsteMes = Maintenance::whereMonth('fecha_mantenimiento', now()->month)
            ->whereYear('fecha_mantenimiento', now()->year)
            ->sum('precio_mantenimiento');

        // Mantenimientos realizados este mes
        $mantenimientosEsteMes = Maintenance::whereMonth('fecha_mantenimiento', now()->month)
            ->whereYear('fecha_mantenimiento', now()->year)
            ->count();

        return [
            Stat::make('🚨 Documentos Vencidos', $totalVencidos)
                ->description("SOAT: {$soatVencidos} | Rev. Técnica: {$revisionVencida}")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($totalVencidos > 0 ? 'danger' : 'success')
                ->chart($totalVencidos > 0 ? [1, 3, 5, 8, $totalVencidos] : [0]),

            Stat::make('⚠️ Por Vencer (30 días)', $totalPorVencer)
                ->description("SOAT: {$soatPorVencer} | Rev. Técnica: {$revisionPorVencer}")
                ->descriptionIcon('heroicon-m-clock')
                ->color($totalPorVencer > 0 ? 'warning' : 'success')
                ->chart($totalPorVencer > 0 ? [2, 4, 6, $totalPorVencer] : [0]),

            Stat::make('🔧 Mantenimientos Pendientes', $mantenimientosPendientes)
                ->description('Mantenimientos programados vencidos')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color($mantenimientosPendientes > 0 ? 'warning' : 'success'),

            Stat::make('💰 Gasto Este Mes', '$' . number_format($costoEsteMes, 0, ',', '.'))
                ->description("{$mantenimientosEsteMes} mantenimientos realizados")
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info')
                ->chart([
                    $costoEsteMes * 0.3,
                    $costoEsteMes * 0.5,
                    $costoEsteMes * 0.7,
                    $costoEsteMes * 0.9,
                    $costoEsteMes
                ]),
        ];
    }

    protected function getColumns(): int
    {
        return 2;
    }
}