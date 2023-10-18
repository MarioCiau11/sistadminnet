<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CONF_GENERAL_PARAMETERS_CONSECUTIVES extends Model
{
    use HasFactory;

    protected $table = 'CONF_GENERAL_PARAMETERS_CONSECUTIVES';
    protected $primaryKey = 'generalConsecutives_id';


    protected $fillable = [
        'generalConsecutives_generalParametersID',
        'generalConsecutives_company',
        'generalConsecutives_consOrderPurchase',
        'generalConsecutives_consEntryPurchase',
        'generalConsecutives_consAdjustment',
        'generalConsecutives_consTransfer',
        'generalConsecutives_consQuotation',
        'generalConsecutives_consDemand',
        'generalConsecutives_consBill',
        'generalConsecutives_consAdvance',
        'generalConsecutives_consApplication',
        'generalConsecutives_consPayment',
        'generalConsecutives_consAdvanceCXC',
        'generalConsecutives_consApplicationCXC',
        'generalConsecutives_consReturnAdvance',
        'generalConsecutives_consCollection',
        'generalConsecutives_consExpense',
        'generalConsecutives_consPettyCash',
        'generalConsecutives_consTransferT',
        'generalConsecutives_consEgress',
    ];
    
}
