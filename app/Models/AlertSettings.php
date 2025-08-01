<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertSettings extends Model
{
    use HasFactory;

    protected $table = 'alert_settings';

    protected $fillable = [
        'dias_anticipacion_soat',
        'dias_anticipacion_revision_tecnica',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}