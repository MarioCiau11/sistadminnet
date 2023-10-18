<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_CANCELED_CFDI extends Model
{
    use HasFactory;

     protected $table = 'PROC_CANCELED_CFDI';
    protected $primaryKey = 'canceledCfdi_id';

    protected $fillable = [
        'canceledCfdi_module',
        'canceledCfdi_moduleID',
        'canceledCfdi_movementID',
        'canceledCfdi_total',
        'canceledCfdi_receptor',
        'canceledCfdi_company',
        'canceledCfdi_branchOffice',
        'canceledCfdi_status',
        'canceledCfdi_Uuid'
    ];
}
