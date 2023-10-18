<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_ACCOUNTS_PAYABLE_DETAILS extends Model
{
    use HasFactory;

    protected $table = 'PROC_ACCOUNTS_PAYABLE_DETAILS';
    protected $primaryKey = 'accountsPayableDetails_id';
    public $incrementing = false;

    protected $fillable = [
        'accountsPayableDetails_accountPayableID',
        'accountsPayableDetails_apply',
        'accountsPayableDetails_applyIncrement',
        'accountsPayableDetails_amount',
        'accountsPayableDetails_company',
        'accountsPayableDetails_branchOffice',
        'accountsPayableDetails_user',
        'accountsPayableDetails_movReference',
        ];

}
