<?php

namespace App\Filament\Resources\GastosPorVehiculoResource\Pages;

use App\Filament\Resources\GastosPorVehiculoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGastosPorVehiculo extends ListRecords
{
    protected static string $resource = GastosPorVehiculoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportar_excel')
                ->label('Exportar a Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $data = GastosPorVehiculoResource::getGastosPorVehiculoQuery()->get();
                    
                    // Crear contenido Excel con formato HTML mejorado
                    $excel = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background-color: #059669; color: white; padding: 15px; text-align: center; margin-bottom: 20px; border-radius: 5px; }
        .info { background-color: #f0fdf4; padding: 10px; margin-bottom: 20px; border-radius: 5px; border-left: 4px solid #059669; }
        table { width: 100%; border-collapse: collapse; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        th { background-color: #059669; color: white; padding: 12px; text-align: left; font-weight: bold; }
        td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background-color: #f9fafb; }
        tr:hover { background-color: #f0fdf4; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge-success { background-color: #f0fdf4; color: #059669; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .money { font-weight: bold; color: #059669; }
        .km { color: #6b7280; font-style: italic; }
        .footer { margin-top: 20px; text-align: center; color: #6b7280; font-size: 12px; }
        .stats { display: flex; justify-content: space-around; margin: 20px 0; }
        .stat-box { background: #f9fafb; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="header">
        <h1>💰 REPORTE DE GASTOS POR VEHÍCULO</h1>
        <p>Análisis Financiero de Mantenimiento Vehicular</p>
    </div>
    
    <div class="info">
        <strong>📅 Fecha de generación:</strong> ' . now()->format('d/m/Y H:i:s') . '<br>
        <strong>🚗 Total de vehículos:</strong> ' . $data->count() . ' vehículos analizados<br>
        <strong>💰 Moneda:</strong> Soles Peruanos (PEN)
    </div>';
    
                    // Calcular estadísticas
                    $totalGeneral = $data->sum('gasto_total');
                    $promedioGeneral = $data->avg('gasto_total');
                    $vehiculoMasCaro = $data->sortByDesc('gasto_total')->first();
                    
                    $excel .= '
    <div class="stats">
        <div class="stat-box">
            <h3>💸 Gasto Total</h3>
            <p style="font-size: 24px; color: #059669; font-weight: bold;">S/ ' . number_format($totalGeneral, 2) . '</p>
        </div>
        <div class="stat-box">
            <h3>📊 Promedio por Vehículo</h3>
            <p style="font-size: 24px; color: #059669; font-weight: bold;">S/ ' . number_format($promedioGeneral, 2) . '</p>
        </div>
        <div class="stat-box">
            <h3>🏆 Vehículo con Mayor Gasto</h3>
            <p style="font-size: 18px; color: #dc2626; font-weight: bold;">' . ($vehiculoMasCaro->placa ?? 'N/A') . '</p>
            <p style="color: #6b7280;">S/ ' . number_format($vehiculoMasCaro->gasto_total ?? 0, 2) . '</p>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>🚗 Placa</th>
                <th>🏭 Marca</th>
                <th>🚙 Modelo</th>
                <th>📅 Año</th>
                <th>🔧 Total Mantenimientos</th>
                <th>💰 Gasto Total (PEN)</th>
                <th>📊 Gasto Promedio (PEN)</th>
                <th>📅 Último Mantenimiento</th>
                <th>🛣️ Kilometraje Actual</th>
            </tr>
        </thead>
        <tbody>';
                    
                    foreach ($data as $record) {
                        // Formatear la fecha si existe
                        $ultimoMantenimiento = 'N/A';
                        if ($record->ultimo_mantenimiento) {
                            try {
                                $ultimoMantenimiento = \Carbon\Carbon::parse($record->ultimo_mantenimiento)->format('d/m/Y');
                            } catch (\Exception $e) {
                                $ultimoMantenimiento = $record->ultimo_mantenimiento;
                            }
                        }
                        
                        // Determinar color según el gasto
                        $gastoColor = '#059669';
                        if ($record->gasto_total > $promedioGeneral * 1.5) {
                            $gastoColor = '#dc2626';
                        } elseif ($record->gasto_total > $promedioGeneral) {
                            $gastoColor = '#d97706';
                        }
                        
                        $excel .= '<tr>
                            <td><strong style="color: #1f2937;">' . ($record->placa ?? '') . '</strong></td>
                            <td>' . ($record->marca ?? '') . '</td>
                            <td>' . ($record->modelo ?? '') . '</td>
                            <td class="text-center">' . ($record->anio ?? '') . '</td>
                            <td class="text-center"><span class="badge-success">' . ($record->total_mantenimientos ?? 0) . '</span></td>
                            <td class="text-right money" style="color: ' . $gastoColor . '; font-weight: bold;">S/ ' . number_format($record->gasto_total ?? 0, 2) . '</td>
                            <td class="text-right money">S/ ' . number_format($record->gasto_promedio ?? 0, 2) . '</td>
                            <td class="text-center">' . $ultimoMantenimiento . '</td>
                            <td class="text-right km">' . number_format($record->kilometraje_actual ?? 0, 0) . ' km</td>
                        </tr>';
                    }
                    
                    $excel .= '</tbody>
        <tfoot>
            <tr style="background-color: #374151; color: white; font-weight: bold;">
                <td colspan="5" class="text-right"><strong>TOTALES GENERALES:</strong></td>
                <td class="text-right"><strong>S/ ' . number_format($totalGeneral, 2) . '</strong></td>
                <td class="text-right"><strong>S/ ' . number_format($promedioGeneral, 2) . '</strong></td>
                <td colspan="2" class="text-center"><strong>' . $data->count() . ' vehículos</strong></td>
            </tr>
        </tfoot>
    </table>
    
    <div class="footer">
        <p>📊 Reporte generado automáticamente por el Sistema de Gestión de Mantenimiento Vehicular</p>
        <p>💡 Los gastos en rojo superan 150% del promedio, en naranja superan el promedio</p>
        <p>🔧 Este análisis ayuda a identificar vehículos con costos de mantenimiento elevados</p>
    </div>
</body>
</html>';
                    
                    return response()->streamDownload(function () use ($excel) {
                        echo $excel;
                    }, 'gastos_por_vehiculo_' . now()->format('Y-m-d_H-i-s') . '.xls', [
                        'Content-Type' => 'application/vnd.ms-excel',
                        'Content-Disposition' => 'attachment; filename="gastos_por_vehiculo_' . now()->format('Y-m-d_H-i-s') . '.xls"',
                    ]);
                }),
        ];
    }
}