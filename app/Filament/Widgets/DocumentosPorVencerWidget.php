<?php

namespace App\Filament\Widgets;

use App\Models\SoatRecord;
use App\Models\RevisionTecnicaRecord;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DocumentosPorVencerWidget extends BaseWidget
{
    protected static ?string $heading = '📋 Documentos Próximos a Vencer';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('vehicle.placa')
                    ->label('🚗 Placa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('tipo_documento')
                    ->label('📄 Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'SOAT' => 'info',
                        'Revisión Técnica' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('numero_documento')
                    ->label('🔢 Número')
                    ->searchable(),

                Tables\Columns\TextColumn::make('fecha_vencimiento')
                    ->label('📅 Vence')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $this->getDateColor($record->fecha_vencimiento)),

                Tables\Columns\TextColumn::make('dias_restantes')
                    ->label('⏰ Días Restantes')
                    ->getStateUsing(function ($record) {
                        $dias = Carbon::parse($record->fecha_vencimiento)->diffInDays(now(), false);
                        if ($dias > 0) {
                            return "Vencido hace {$dias} días";
                        } elseif ($dias == 0) {
                            return "Vence hoy";
                        } else {
                            return abs($dias) . " días";
                        }
                    })
                    ->badge()
                    ->color(function ($record) {
                        $dias = Carbon::parse($record->fecha_vencimiento)->diffInDays(now(), false);
                        if ($dias > 0) return 'danger';
                        if ($dias == 0) return 'warning';
                        if (abs($dias) <= 30) return 'warning';
                        return 'success';
                    }),

                Tables\Columns\TextColumn::make('estado_urgencia')
                    ->label('🚨 Urgencia')
                    ->getStateUsing(function ($record) {
                        $dias = Carbon::parse($record->fecha_vencimiento)->diffInDays(now(), false);
                        if ($dias > 0) return '🔴 VENCIDO';
                        if ($dias == 0) return '🟠 HOY';
                        if (abs($dias) <= 7) return '🟡 ESTA SEMANA';
                        if (abs($dias) <= 30) return '🟢 ESTE MES';
                        return '✅ OK';
                    })
                    ->badge()
                    ->color(function ($record) {
                        $dias = Carbon::parse($record->fecha_vencimiento)->diffInDays(now(), false);
                        if ($dias > 0) return 'danger';
                        if ($dias == 0) return 'warning';
                        if (abs($dias) <= 7) return 'warning';
                        if (abs($dias) <= 30) return 'info';
                        return 'success';
                    }),
            ])
            ->defaultSort('fecha_vencimiento', 'asc')
            ->striped()
            ->paginated([10, 25, 50])
            ->poll('30s')
            ->emptyStateHeading('🎉 ¡Excelente!')
            ->emptyStateDescription('No hay documentos próximos a vencer')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    protected function getTableQuery(): Builder
    {
        // Obtener SOAT próximos a vencer (próximos 60 días o ya vencidos)
        $soatQuery = SoatRecord::query()
            ->with('vehicle')
            ->where('fecha_vencimiento', '<=', now()->addDays(60))
            ->select([
                'id',
                'vehicle_id',
                'fecha_vencimiento',
                'numero_poliza as numero_documento',
                \DB::raw("'SOAT' as tipo_documento"),
                'created_at',
                'updated_at'
            ]);

        // Obtener Revisiones Técnicas próximas a vencer (próximos 60 días o ya vencidas)
        $revisionQuery = RevisionTecnicaRecord::query()
            ->with('vehicle')
            ->where('fecha_vencimiento', '<=', now()->addDays(60))
            ->select([
                'id',
                'vehicle_id',
                'fecha_vencimiento',
                'numero_certificado as numero_documento',
                \DB::raw("'Revisión Técnica' as tipo_documento"),
                'created_at',
                'updated_at'
            ]);

        // Unir ambas consultas
        return $soatQuery->union($revisionQuery);
    }

    protected function getDateColor($fecha): string
    {
        $dias = Carbon::parse($fecha)->diffInDays(now(), false);
        
        if ($dias > 0) return 'danger'; // Vencido
        if ($dias == 0) return 'warning'; // Vence hoy
        if (abs($dias) <= 7) return 'warning'; // Próxima semana
        if (abs($dias) <= 30) return 'info'; // Próximo mes
        
        return 'success'; // Más de 30 días
    }
}