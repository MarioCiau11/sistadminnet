<?php

namespace App\Models\timbrado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_CFDI_REFERENCE extends Model
{
    use HasFactory;
    protected $table = 'PROC_CFDI_REFERENCE';
    protected $primaryKey = 'cfdiReference_id';
    public $incrementing = false;


    protected $fillable = [
        "cfdiReference_module",
        "cfdiReference_cfdiID",
        "cfdiReference_moduleOrigin",
        "cfdiReference_idOrigin",
        "cfdiReference_movementOrigin",
        "cfdiReference_relationTypeKey",
        "cfdiReference_percentage",
        "cfdiReference_company",
        "cfdiReference_branchOffice",
    ];
}
