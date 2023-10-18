<?php

namespace App\Models\timbrado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_CFDI_CXC_REFERENCE extends Model
{
    use HasFactory;

     protected $table = 'PROC_CFDI_CXC_REFERENCE';
    protected $primaryKey = 'cfdiReferenceCxC_id';
    public $incrementing = false;


    protected $fillable = [
        "cfdiReferenceCxC_id",
        "cfdiReferenceCxC_cxcID",
        "cfdiReferenceCxC_module",
        "cfdiReferenceCxC_moduleOrigin",
        "cfdiReferenceCxC_idOrigin",
        "cfdiReferenceCxC_UUID",
        "cfdiReferenceCxC_company",
        "cfdiReferenceCxC_branchOffice",
        
    ];
}
