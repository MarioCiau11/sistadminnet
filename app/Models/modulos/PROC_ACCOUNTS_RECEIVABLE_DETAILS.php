<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_ACCOUNTS_RECEIVABLE_DETAILS extends Model
{
    use HasFactory;
    protected $table = 'PROC_ACCOUNTS_RECEIVABLE_DETAILS';
    protected $primaryKey = 'accountsReceivableDetails_id';

    protected $fillable=[
        'accountsReceivableDetails_accountPayableID',
        'accountsReceivableDetails_apply',
        'accountsReceivableDetails_applyIncrement',
        'accountsReceivableDetails_retention1',
        'accountsReceivableDetails_retentionISR',
        'accountsReceivableDetails_retention2',
        'accountsReceivableDetails_retentionIVA',
        'accountsReceivableDetails_amount',
        'accountsReceivableDetails_company',
        'accountsReceivableDetails_branchOffice',
        'accountsReceivableDetails_user',
        'accountsReceivableDetails_movReference',
    ];
    

}
