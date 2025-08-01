<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GastosPorVehiculoResource\Pages;
use App\Models\Maintenance;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class GastosPorVehiculoResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Gastos por Vehículo';

    protected static ?string $modelLabel = 'Gasto por Vehículo';

    protected static ?string $pluralModelLabel = 'Gastos por Vehículo';

    protected static ?string $navigationGroup = 'Reportes';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereRaw('1 = 0');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(self::getGastosPorVehiculoQuery())
            ->columns([
                Tables\Columns\TextColumn::make('placa')
                    ->label('Placa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('marca')
                    ->label('Marca')
                    ->searchable(),
                Tables\Columns\TextColumn::make('modelo')
                    ->label('Modelo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('anio')
                    ->label('Año')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_mantenimientos')
                    ->label('Total Mantenimientos')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gasto_total')
                    ->label('Gasto Total')
                    ->money('PEN')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('PEN')
                            ->label('Total General'),
                    ]),
                Tables\Columns\TextColumn::make('gasto_promedio')
                    ->label('Gasto Promedio')
                    ->money('PEN')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Average::make()
                            ->money('PEN')
                            ->label('Promedio General'),
                    ]),
                Tables\Columns\TextColumn::make('ultimo_mantenimiento')
                    ->label('Último Mantenimiento')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kilometraje_actual')
                    ->label('Kilometraje Actual')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state) . ' km'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('placa')
                    ->label('Filtrar por Placa')
                    ->options(function () {
                        return Vehicle::distinct()->orderBy('placa')->pluck('placa', 'placa')->toArray();
                    })
                    ->searchable()
                    ->placeholder('Seleccione una placa'),
                Tables\Filters\SelectFilter::make('marca')
                    ->label('Marca')
                    ->options(function () {
                        return Vehicle::distinct()->pluck('marca', 'marca')->toArray();
                    }),
                Tables\Filters\SelectFilter::make('anio')
                    ->label('Año')
                    ->options(function () {
                        return Vehicle::distinct()->orderBy('anio', 'desc')->pluck('anio', 'anio')->toArray();
                    }),
                Tables\Filters\Filter::make('rango_fechas')
                    ->form([
                        Forms\Components\DatePicker::make('fecha_desde')
                            ->label('Fecha Desde'),
                        Forms\Components\DatePicker::make('fecha_hasta')
                            ->label('Fecha Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['fecha_desde'],
                                fn (Builder $query, $date): Builder => $query->whereHas('maintenances', function ($q) use ($date) {
                                    $q->where('fecha_mantenimiento', '>=', $date);
                                }),
                            )
                            ->when(
                                $data['fecha_hasta'],
                                fn (Builder $query, $date): Builder => $query->whereHas('maintenances', function ($q) use ($date) {
                                    $q->where('fecha_mantenimiento', '<=', $date);
                                }),
                            );
                    }),
                Tables\Filters\Filter::make('con_gastos')
                    ->label('Solo vehículos con gastos')
                    ->query(fn (Builder $query): Builder => $query->having('gasto_total', '>', 0)),
            ])
            ->actions([ ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Acciones de exportación se manejarán a nivel de página
                ]),
            ])
            ->defaultSort('gasto_total', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getGastosPorVehiculoQuery(): Builder
    {
        return Vehicle::query()
            ->select([
                'vehicles.id',
                'vehicles.placa',
                'vehicles.marca',
                'vehicles.modelo',
                'vehicles.anio',
                'vehicles.kilometraje_actual',
                DB::raw('COUNT(maintenances.id) as total_mantenimientos'),
                DB::raw('COALESCE(SUM(maintenances.precio_mantenimiento), 0) as gasto_total'),
                DB::raw('COALESCE(AVG(maintenances.precio_mantenimiento), 0) as gasto_promedio'),
                DB::raw('MAX(maintenances.fecha_mantenimiento) as ultimo_mantenimiento')
            ])
            ->leftJoin('maintenances', function ($join) {
                $join->on('vehicles.id', '=', 'maintenances.vehicle_id')
                     ->whereNull('maintenances.deleted_at');
            })
            ->groupBy([
                'vehicles.id',
                'vehicles.placa',
                'vehicles.marca',
                'vehicles.modelo',
                'vehicles.anio',
                'vehicles.kilometraje_actual'
            ])
            ->whereNull('vehicles.deleted_at');
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
            'index' => Pages\ListGastosPorVehiculo::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}