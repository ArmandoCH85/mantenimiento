<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('vehicles:check-expired', function () {
    $this->call('vehicles:update-states');
})->purpose('Verificar y actualizar estados de documentos vencidos');
