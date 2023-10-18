<?php

namespace App\Models\timbrado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_CFDI extends Model
{
    use HasFactory;

    protected $table = 'PROC_CFDI';
    protected $primaryKey = 'cfdi_id';
    public $incrementing = false;

    protected $fillable = [
        'cfdi_module',
        'cfdi_moduleID',
        'cfdi_movementID',
        'cfdi_serie',
        'cfdi_RFC',
        'cfdi_amount',
        'cfdi_taxes',
        'cfdi_total',
        'cfdi_outstandingBalance',
        'cfdi_certificateNumber',
        'cfdi_stamp',
        'cfdi_originalString',
        'cfdi_stamped',
        'cfdi_document',
        'cfdi_Pdf',
        'cfdi_UUID',
        'cfdi_Path',
        'cfdi_stampSat',
        'cfdi_certificateNumberSat',
        'cfdi_cancelled',
        'cfdi_acknowledgmentCanceled',
        'cfdi_stampCanceledSat',
        'cfdi_year',
        'cfdi_period',
        'cfdi_money',
        'cfdi_typeChange',
        'cfdi_company',
        'cfdi_branchOffice',
    ];
}
