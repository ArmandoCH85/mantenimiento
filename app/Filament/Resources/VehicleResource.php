<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Filament\Resources\VehicleResource\RelationManagers\SoatRecordsRelationManager;
use App\Filament\Resources\VehicleResource\RelationManagers\RevisionTecnicaRecordsRelationManager;
use App\Filament\Resources\VehicleResource\RelationManagers\MaintenancesRelationManager;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Vehículos';

    protected static ?string $modelLabel = 'Vehículo';

    protected static ?string $pluralModelLabel = 'Vehículos';

    protected static ?string $navigationGroup = 'Gestión de Flota';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'placa';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->description('Datos principales de identificación del vehículo')
                    ->icon('heroicon-o-identification')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('placa')
                                    ->label('Número de Placa')
                                    ->required()
                                    ->maxLength(20)
                                    ->placeholder('Ej: ABC123')
                                    ->columnSpan(1),
                                Forms\Components\Select::make('tipo_vehiculo')
                                    ->label('Tipo de Vehículo')
                                    ->options([
                                        'automovil' => 'Automóvil',
                                        'camioneta' => 'Camioneta',
                                        'motocicleta' => 'Motocicleta',
                                        'camion' => 'Camión',
                                        'bus' => 'Bus',
                                        'otro' => 'Otro',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->columnSpan(1),
                            ])->columns(2),
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('marca')
                                    ->label('Marca')
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('Ej: Toyota, Honda, Ford...')
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('modelo')
                                    ->label('Modelo')
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('Ej: Corolla, Civic, Ranger...')
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('anio')
                                    ->label('Año')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue(date('Y') + 1)
                                    ->columnSpan(1),
                            ])->columns(3),
                        Forms\Components\TextInput::make('color')
                            ->label('Color')
                            ->maxLength(30)
                            ->placeholder('Ej: Rojo, Azul, Negro...')
                            ->columnSpan(1),
                    ]),

                Forms\Components\Section::make('Información Técnica')
                    ->description('Detalles técnicos y de identificación del vehículo')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('numero_motor')
                                    ->label('Número de Motor')
                                    ->maxLength(100)
                                    ->placeholder('Ingrese el número de motor')
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('numero_chasis')
                                    ->label('Número de Chasis')
                                    ->maxLength(100)
                                    ->placeholder('Ingrese el número de chasis')
                                    ->columnSpan(1),
                            ])->columns(2),
                        Forms\Components\TextInput::make('kilometraje_actual')
                            ->label('Kilometraje Actual')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('km')
                            ->placeholder('0')
                            ->columnSpan(1),
                    ]),

                Forms\Components\Section::make('Información de Compra')
                    ->description('Datos relacionados con la adquisición del vehículo')
                    ->icon('heroicon-o-calendar-days')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\DatePicker::make('fecha_compra')
                                    ->label('Fecha de Compra')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('propietario_actual')
                                    ->label('Propietario Actual')
                                    ->maxLength(100)
                                    ->placeholder('Nombre del propietario actual')
                                    ->columnSpan(1),
                            ])->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('placa', 'asc')
            ->striped()
            ->poll('30s')
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->columns([
                Tables\Columns\TextColumn::make('placa')
                    ->searchable()
                    ->label('Placa')
                    ->sortable()
                    ->weight('bold')
                    ->width('100px')
                    ->tooltip('Número de placa del vehículo')
                    ->copyable()
                    ->copyMessage('Placa copiada al portapapeles'),
                Tables\Columns\TextColumn::make('marca_modelo')
                    ->label('Marca/Modelo')
                    ->getStateUsing(fn ($record) => $record->marca . ' ' . $record->modelo)
                    ->searchable(['marca', 'modelo'])
                    ->limit(25)
                    ->width('200px')
                    ->tooltip(fn ($record) => $record->marca . ' ' . $record->modelo)
                    ->description(fn ($record) => $record->color, position: 'below'),
                Tables\Columns\TextColumn::make('anio')
                    ->label('Año')
                    ->numeric()
                    ->sortable()
                    ->width('70px')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tipo_vehiculo')
                    ->badge()
                    ->label('Tipo')
                    ->width('100px')
                    ->color(fn (string $state): string => match ($state) {
                        'automovil' => 'primary',
                        'camioneta' => 'success',
                        'motocicleta' => 'warning',
                        'camion' => 'danger',
                        'bus' => 'info',
                        'otro' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'automovil' => 'heroicon-o-truck',
                        'camioneta' => 'heroicon-o-truck',
                        'motocicleta' => 'heroicon-o-bolt',
                        'camion' => 'heroicon-o-truck',
                        'bus' => 'heroicon-o-truck',
                        'otro' => 'heroicon-o-question-mark-circle',
                        default => 'heroicon-o-truck',
                    }),
                Tables\Columns\TextColumn::make('kilometraje_actual')
                    ->label('Kilometraje')
                    ->numeric(
                        thousandsSeparator: '.',
                        decimalPlaces: 0,
                    )
                    ->sortable()
                    ->width('90px')
                    ->toggleable()
                    ->suffix(' km'),
                Tables\Columns\TextColumn::make('propietario_actual')
                    ->label('Propietario')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->propietario_actual)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('fecha_compra')
                    ->date('d/m/Y')
                    ->label('Fecha Compra')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->label('Creado')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->label('Actualizado')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50, 100])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo_vehiculo')
                    ->label('Tipo de Vehículo')
                    ->options([
                        'automovil' => 'Automóvil',
                        'camioneta' => 'Camioneta',
                        'motocicleta' => 'Motocicleta',
                        'camion' => 'Camión',
                        'bus' => 'Bus',
                        'otro' => 'Otro',
                    ])
                    ->multiple()
                    ->preload(),
                Tables\Filters\Filter::make('fecha_compra')
                    ->label('Rango de Fecha de Compra')
                    ->form([
                        Forms\Components\DatePicker::make('fecha_compra_desde')
                            ->label('Desde')
                            ->native(false),
                        Forms\Components\DatePicker::make('fecha_compra_hasta')
                            ->label('Hasta')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['fecha_compra_desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_compra', '>=', $date),
                            )
                            ->when(
                                $data['fecha_compra_hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_compra', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('kilometraje')
                    ->label('Rango de Kilometraje')
                    ->form([
                        Forms\Components\TextInput::make('kilometraje_min')
                            ->label('Mínimo')
                            ->numeric(),
                        Forms\Components\TextInput::make('kilometraje_max')
                            ->label('Máximo')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['kilometraje_min'],
                                fn (Builder $query, $km): Builder => $query->where('kilometraje_actual', '>=', $km),
                            )
                            ->when(
                                $data['kilometraje_max'],
                                fn (Builder $query, $km): Builder => $query->where('kilometraje_actual', '<=', $km),
                            );
                    }),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->tooltip('Editar vehículo')
                        ->icon('heroicon-o-pencil'),
                    Tables\Actions\Action::make('registrar_soat')
                        ->label('SOAT')
                        ->icon('heroicon-o-plus-circle')
                        ->url(fn (Vehicle $record) => VehicleResource::getUrl('edit', ['record' => $record]) . '#soatRecords')
                        ->color('success')
                        ->tooltip('Registrar nuevo SOAT'),
                    Tables\Actions\Action::make('registrar_revision')
                        ->label('Revisión')
                        ->icon('heroicon-o-plus-circle')
                        ->url(fn (Vehicle $record) => VehicleResource::getUrl('edit', ['record' => $record]) . '#revisionTecnicaRecords')
                        ->color('success')
                        ->tooltip('Registrar nueva revisión técnica'),
                    Tables\Actions\Action::make('ver_documentos')
                        ->label('Ver Documentos')
                        ->icon('heroicon-o-document-text')
                        ->url(fn (Vehicle $record) => VehicleResource::getUrl('edit', ['record' => $record]))
                        ->color('info')
                        ->tooltip('Ver todos los documentos del vehículo'),
                    Tables\Actions\DeleteAction::make()
                        ->tooltip('Eliminar vehículo')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation(),
                ])
                    ->tooltip('Acciones del vehículo')
                    ->icon('heroicon-o-ellipsis-horizontal')
                    ->color('primary')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('export')
                        ->label('Exportar Seleccionados')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn ($records) => $records->downloadExcel()),
                ])
                    ->label('Acciones masivas'),
            ])
            ->emptyStateHeading('No hay vehículos registrados')
            ->emptyStateDescription('Comienza creando un nuevo vehículo para gestionar su información y documentos.')
            ->emptyStateIcon('heroicon-o-truck');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SoatRecordsRelationManager::class,
            RelationManagers\RevisionTecnicaRecordsRelationManager::class,
            RelationManagers\MaintenancesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
