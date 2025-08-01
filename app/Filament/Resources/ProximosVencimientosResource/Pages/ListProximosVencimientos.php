<?php

namespace App\Filament\Resources\ProximosVencimientosResource\Pages;

use App\Filament\Resources\ProximosVencimientosResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProximosVencimientos extends ListRecords
{
    protected static string $resource = ProximosVencimientosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportar_excel')
                ->label('Exportar a Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $data = ProximosVencimientosResource::getProximosVencimientosQuery()->get();
                    
                    // Crear contenido Excel con formato HTML mejorado
                    $excel = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background-color: #dc2626; color: white; padding: 15px; text-align: center; margin-bottom: 20px; border-radius: 5px; }
        .info { background-color: #fef2f2; padding: 10px; margin-bottom: 20px; border-radius: 5px; border-left: 4px solid #dc2626; }
        table { width: 100%; border-collapse: collapse; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        th { background-color: #dc2626; color: white; padding: 12px; text-align: left; font-weight: bold; }
        td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background-color: #f9fafb; }
        tr:hover { background-color: #fef2f2; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge-danger { background-color: #fef2f2; color: #dc2626; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-warning { background-color: #fef3c7; color: #d97706; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-info { background-color: #dbeafe; color: #2563eb; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; color: #6b7280; font-size: 12px; }
        .stats { display: flex; justify-content: space-around; margin: 20px 0; }
        .stat-box { background: #f9fafb; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #e5e7eb; }
        .urgent { background-color: #fef2f2 !important; }
        .warning { background-color: #fef3c7 !important; }
        .normal { background-color: #f0f9ff !important; }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚠️ REPORTE DE PRÓXIMOS VENCIMIENTOS</h1>
        <p>Control de Documentos y Mantenimientos por Vencer</p>
    </div>
    
    <div class="info">
        <strong>📅 Fecha de generación:</strong> ' . now()->format('d/m/Y H:i:s') . '<br>
        <strong>📋 Total de documentos:</strong> ' . $data->count() . ' documentos por vencer<br>
        <strong>⚠️ Prioridad:</strong> Documentos ordenados por fecha de vencimiento
    </div>';
    
                    // Calcular estadísticas
                    $hoy = now();
                    $vencidosHoy = $data->filter(function($item) use ($hoy) {
                        try {
                            $fechaVencimiento = \Carbon\Carbon::parse($item->fecha_vencimiento);
                            return $fechaVencimiento->isToday();
                        } catch (\Exception $e) {
                            return false;
                        }
                    })->count();
                    
                    $vencenEn7Dias = $data->filter(function($item) use ($hoy) {
                        try {
                            $fechaVencimiento = \Carbon\Carbon::parse($item->fecha_vencimiento);
                            return $fechaVencimiento->between($hoy, $hoy->copy()->addDays(7));
                        } catch (\Exception $e) {
                            return false;
                        }
                    })->count();
                    
                    $vencenEn30Dias = $data->filter(function($item) use ($hoy) {
                        try {
                            $fechaVencimiento = \Carbon\Carbon::parse($item->fecha_vencimiento);
                            return $fechaVencimiento->between($hoy, $hoy->copy()->addDays(30));
                        } catch (\Exception $e) {
                            return false;
                        }
                    })->count();
                    
                    $excel .= '
    <div class="stats">
        <div class="stat-box">
            <h3>🚨 Vencen Hoy</h3>
            <p style="font-size: 24px; color: #dc2626; font-weight: bold;">' . $vencidosHoy . '</p>
            <p style="color: #6b7280;">Documentos</p>
        </div>
        <div class="stat-box">
            <h3>⚠️ Próximos 7 días</h3>
            <p style="font-size: 24px; color: #d97706; font-weight: bold;">' . $vencenEn7Dias . '</p>
            <p style="color: #6b7280;">Documentos</p>
        </div>
        <div class="stat-box">
            <h3>📅 Próximos 30 días</h3>
            <p style="font-size: 24px; color: #2563eb; font-weight: bold;">' . $vencenEn30Dias . '</p>
            <p style="color: #6b7280;">Documentos</p>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>🚗 Placa</th>
                <th>🏭 Marca</th>
                <th>🚙 Modelo</th>
                <th>📋 Tipo Documento</th>
                <th>📄 Número</th>
                <th>📅 Fecha Vencimiento</th>
                <th>⏰ Días Restantes</th>
                <th>🚨 Prioridad</th>
                <th>🏢 Compañía/Centro</th>
                <th>💰 Valor Pagado (PEN)</th>
            </tr>
        </thead>
        <tbody>';
                    
                    foreach ($data as $record) {
                        // Formatear la fecha de vencimiento
                        $fechaVencimiento = 'N/A';
                        $diasRestantes = 'N/A';
                        $prioridad = 'Normal';
                        $claseFila = 'normal';
                        $badgeClass = 'badge-info';
                        
                        if ($record->fecha_vencimiento) {
                            try {
                                if (is_string($record->fecha_vencimiento)) {
                                    $fechaVencimientoCarbon = \Carbon\Carbon::parse($record->fecha_vencimiento);
                                } else {
                                    $fechaVencimientoCarbon = $record->fecha_vencimiento;
                                }
                                
                                $fechaVencimiento = $fechaVencimientoCarbon->format('d/m/Y');
                                $diasRestantes = $fechaVencimientoCarbon->diffInDays(now(), false);
                                
                                if ($diasRestantes <= 0) {
                                    $prioridad = '🚨 URGENTE';
                                    $claseFila = 'urgent';
                                    $badgeClass = 'badge-danger';
                                    $diasRestantes = abs($diasRestantes) . ' días vencido';
                                } elseif ($diasRestantes <= 7) {
                                    $prioridad = '⚠️ ALTA';
                                    $claseFila = 'warning';
                                    $badgeClass = 'badge-warning';
                                    $diasRestantes = $diasRestantes . ' días';
                                } elseif ($diasRestantes <= 30) {
                                    $prioridad = '📅 MEDIA';
                                    $claseFila = 'normal';
                                    $badgeClass = 'badge-info';
                                    $diasRestantes = $diasRestantes . ' días';
                                } else {
                                    $prioridad = '✅ BAJA';
                                    $claseFila = 'normal';
                                    $badgeClass = 'badge-info';
                                    $diasRestantes = $diasRestantes . ' días';
                                }
                            } catch (\Exception $e) {
                                $fechaVencimiento = $record->fecha_vencimiento;
                            }
                        }
                        
                        $excel .= '<tr class="' . $claseFila . '">
                            <td><strong style="color: #1f2937;">' . ($record->vehicle->placa ?? '') . '</strong></td>
                            <td>' . ($record->vehicle->marca ?? '') . '</td>
                            <td>' . ($record->vehicle->modelo ?? '') . '</td>
                            <td>' . ($record->tipo_documento ?? '') . '</td>
                            <td>' . ($record->numero_documento ?? '') . '</td>
                            <td class="text-center"><strong>' . $fechaVencimiento . '</strong></td>
                            <td class="text-center">' . $diasRestantes . '</td>
                            <td class="text-center"><span class="' . $badgeClass . '">' . $prioridad . '</span></td>
                            <td>' . ($record->compania_centro ?? 'N/A') . '</td>
                            <td class="text-right">S/ ' . number_format($record->valor_pagado ?? 0, 2) . '</td>
                        </tr>';
                    }
                    
                    $excel .= '</tbody>
        <tfoot>
            <tr style="background-color: #374151; color: white; font-weight: bold;">
                <td colspan="6" class="text-right"><strong>TOTAL DE DOCUMENTOS:</strong></td>
                <td class="text-center"><strong>' . $data->count() . '</strong></td>
                <td colspan="2" class="text-center"><strong>Requieren seguimiento</strong></td>
                <td class="text-right"><strong>S/ ' . number_format($data->sum('valor_pagado'), 2) . '</strong></td>
            </tr>
        </tfoot>
    </table>
    
    <div class="footer">
        <p>📊 Reporte generado automáticamente por el Sistema de Gestión de Mantenimiento Vehicular</p>
        <p>🚨 Prioridades: URGENTE (vencido/hoy), ALTA (≤7 días), MEDIA (≤30 días), BAJA (>30 días)</p>
        <p>⚠️ Revisar inmediatamente los documentos marcados como URGENTE y ALTA prioridad</p>
    </div>
</body>
</html>';
                    
                    return response()->streamDownload(function () use ($excel) {
                        echo $excel;
                    }, 'proximos_vencimientos_' . now()->format('Y-m-d_H-i-s') . '.xls', [
                        'Content-Type' => 'application/vnd.ms-excel',
                        'Content-Disposition' => 'attachment; filename="proximos_vencimientos_' . now()->format('Y-m-d_H-i-s') . '.xls"',
                    ]);
                }),
        ];
    }
}