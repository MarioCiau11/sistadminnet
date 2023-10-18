<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_CANCELED_REFERENCE extends Model
{
    use HasFactory;

    protected $table = 'PROC_CANCELED_REFERENCE';
    protected $primaryKey = 'canceledReference_id';

    protected $fillable = [
        'canceledReference_module',
        'canceledReference_moduleID',
        'canceledReference_moduleCanceledID',
    ];
}
