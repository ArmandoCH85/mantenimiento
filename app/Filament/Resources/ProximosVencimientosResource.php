<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProximosVencimientosResource\Pages;
use App\Models\SoatRecord;
use App\Models\RevisionTecnicaRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProximosVencimientosResource extends Resource
{
    protected static ?string $model = SoatRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Próximos Vencimientos';

    protected static ?string $modelLabel = 'Próximo Vencimiento';

    protected static ?string $pluralModelLabel = 'Próximos Vencimientos';

    protected static ?string $navigationGroup = 'Reportes';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return SoatRecord::query()
            ->where('fecha_vencimiento', '>=', now())
            ->where('fecha_vencimiento', '<=', now()->addDays(30))
            ->with('vehicle');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(self::getProximosVencimientosQuery())
            ->columns([
                Tables\Columns\TextColumn::make('vehicle.placa')
                    ->label('Placa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle.marca')
                    ->label('Marca')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle.modelo')
                    ->label('Modelo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('tipo_documento')
                    ->label('Tipo')
                    ->getStateUsing(fn () => 'SOAT')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('numero_poliza')
                    ->label('Número')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_vencimiento')
                    ->label('Fecha Vencimiento')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('dias_restantes')
                    ->label('Días Restantes')
                    ->getStateUsing(function ($record) {
                        return (int) now()->diffInDays($record->fecha_vencimiento, false);
                    })
                    ->badge()
                    ->color(function ($state) {
                        if ($state <= 7) return 'danger';
                        if ($state <= 15) return 'warning';
                        return 'success';
                    })
                    ->sortable(false),
                Tables\Columns\TextColumn::make('compania_aseguradora')
                    ->label('Compañía')
                    ->searchable(),
                Tables\Columns\TextColumn::make('valor_pagado')
                    ->label('Valor')
                    ->money('PEN')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vehicle_id')
                    ->label('Vehículo')
                    ->relationship('vehicle', 'placa')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('compania_aseguradora')
                    ->label('Compañía Aseguradora')
                    ->options([
                        'Seguros Bolívar' => 'Seguros Bolívar',
                        'SURA' => 'SURA',
                        'Mapfre' => 'Mapfre',
                        'AXA Colpatria' => 'AXA Colpatria',
                        'Allianz' => 'Allianz',
                        'Liberty Seguros' => 'Liberty Seguros',
                        'Previsora Seguros' => 'Previsora Seguros',
                        'Equidad Seguros' => 'Equidad Seguros',
                    ]),
                Tables\Filters\SelectFilter::make('urgencia')
                    ->label('Nivel de Urgencia')
                    ->options([
                        'critico' => 'Crítico (≤ 7 días)',
                        'advertencia' => 'Advertencia (8-15 días)',
                        'normal' => 'Normal (16-30 días)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] === 'critico',
                            fn (Builder $query): Builder => $query->where('fecha_vencimiento', '<=', now()->addDays(7))
                        )->when(
                            $data['value'] === 'advertencia',
                            fn (Builder $query): Builder => $query->whereBetween('fecha_vencimiento', [now()->addDays(8), now()->addDays(15)])
                        )->when(
                            $data['value'] === 'normal',
                            fn (Builder $query): Builder => $query->whereBetween('fecha_vencimiento', [now()->addDays(16), now()->addDays(30)])
                        );
                    }),
            ])
            ->actions([
                // No necesitamos acciones de edición para reportes
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Acciones de exportación se manejarán a nivel de página
                ]),
            ])
            ->defaultSort('fecha_vencimiento', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getProximosVencimientosQuery(): Builder
    {
        return SoatRecord::query()
            ->where('fecha_vencimiento', '>=', now())
            ->where('fecha_vencimiento', '<=', now()->addDays(30))
            ->with('vehicle')
            ->orderBy('fecha_vencimiento', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProximosVencimientos::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}