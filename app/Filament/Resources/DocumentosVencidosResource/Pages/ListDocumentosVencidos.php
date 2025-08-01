<?php

namespace App\Filament\Resources\DocumentosVencidosResource\Pages;

use App\Filament\Resources\DocumentosVencidosResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDocumentosVencidos extends ListRecords
{
    protected static string $resource = DocumentosVencidosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportar_excel')
                ->label('Exportar a Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $data = DocumentosVencidosResource::getDocumentosVencidosQuery()->get();
                    
                    // Crear contenido Excel con formato HTML mejorado
                    $excel = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background-color: #dc2626; color: white; padding: 15px; text-align: center; margin-bottom: 20px; border-radius: 5px; }
        .info { background-color: #f3f4f6; padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        th { background-color: #dc2626; color: white; padding: 12px; text-align: left; font-weight: bold; }
        td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background-color: #f9fafb; }
        tr:hover { background-color: #f3f4f6; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge-danger { background-color: #fef2f2; color: #dc2626; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .money { font-weight: bold; color: #059669; }
        .footer { margin-top: 20px; text-align: center; color: #6b7280; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 REPORTE DE DOCUMENTOS VENCIDOS</h1>
        <p>Sistema de Gestión de Mantenimiento Vehicular</p>
    </div>
    
    <div class="info">
        <strong>📅 Fecha de generación:</strong> ' . now()->format('d/m/Y H:i:s') . '<br>
        <strong>📊 Total de registros:</strong> ' . $data->count() . ' documentos vencidos<br>
        <strong>💰 Moneda:</strong> Soles Peruanos (PEN)
    </div>
    
    <table>
        <thead>
            <tr>
                <th>🚗 Placa</th>
                <th>🏭 Marca</th>
                <th>🚙 Modelo</th>
                <th>📄 Tipo Documento</th>
                <th>🔢 Número</th>
                <th>📅 Fecha Vencimiento</th>
                <th>⏰ Días Vencido</th>
                <th>🏢 Compañía</th>
                <th>💰 Valor Pagado (PEN)</th>
            </tr>
        </thead>
        <tbody>';
                    
                    $totalValor = 0;
                    foreach ($data as $record) {
                        // Formatear la fecha de vencimiento
                        $fechaVencimiento = 'N/A';
                        if ($record->fecha_vencimiento) {
                            try {
                                if (is_string($record->fecha_vencimiento)) {
                                    $fechaVencimiento = \Carbon\Carbon::parse($record->fecha_vencimiento)->format('d/m/Y');
                                } else {
                                    $fechaVencimiento = $record->fecha_vencimiento->format('d/m/Y');
                                }
                            } catch (\Exception $e) {
                                $fechaVencimiento = $record->fecha_vencimiento;
                            }
                        }
                        
                        $valor = $record->valor_pagado ?? 0;
                        $totalValor += $valor;
                        
                        $excel .= '<tr>
                            <td><strong>' . ($record->vehicle->placa ?? '') . '</strong></td>
                            <td>' . ($record->vehicle->marca ?? '') . '</td>
                            <td>' . ($record->vehicle->modelo ?? '') . '</td>
                            <td><span class="badge-danger">' . $record->tipo_documento . '</span></td>
                            <td class="text-center">' . $record->numero_documento . '</td>
                            <td class="text-center">' . $fechaVencimiento . '</td>
                            <td class="text-center"><strong>' . $record->dias_vencido . ' días</strong></td>
                            <td>' . ($record->compania_aseguradora ?? '') . '</td>
                            <td class="text-right money">S/ ' . number_format($valor, 2) . '</td>
                        </tr>';
                    }
                    
                    $excel .= '</tbody>
        <tfoot>
            <tr style="background-color: #374151; color: white; font-weight: bold;">
                <td colspan="8" class="text-right"><strong>TOTAL GENERAL:</strong></td>
                <td class="text-right"><strong>S/ ' . number_format($totalValor, 2) . '</strong></td>
            </tr>
        </tfoot>
    </table>
    
    <div class="footer">
        <p>📊 Reporte generado automáticamente por el Sistema de Gestión de Mantenimiento Vehicular</p>
        <p>⚠️ Este reporte muestra todos los documentos que han vencido y requieren atención inmediata</p>
    </div>
</body>
</html>';
                    
                    return response()->streamDownload(function () use ($excel) {
                        echo $excel;
                    }, 'documentos_vencidos_' . now()->format('Y-m-d_H-i-s') . '.xls', [
                        'Content-Type' => 'application/vnd.ms-excel',
                        'Content-Disposition' => 'attachment; filename="documentos_vencidos_' . now()->format('Y-m-d_H-i-s') . '.xls"',
                    ]);
                }),
        ];
    }
}