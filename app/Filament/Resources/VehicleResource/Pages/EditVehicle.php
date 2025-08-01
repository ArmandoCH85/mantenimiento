<?php

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Filament\Resources\VehicleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVehicle extends EditRecord
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('registrar_soat')
                ->label('Registrar SOAT')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->url(fn () => $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]) . '#soatRecords'),
            Actions\Action::make('registrar_revision')
                ->label('Registrar Revisión Técnica')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->url(fn () => $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]) . '#revisionTecnicaRecords'),
        ];
    }
}
