<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;


class  CAT_MONEY_ACCOUNTS extends Model
{
    use HasFactory;

    protected $table = 'CAT_MONEY_ACCOUNTS';
    protected $primaryKey = 'moneyAccounts_key';
    public $incrementing = false;

    protected $fillable = [
        'moneyAccounts_key',
        'moneyAccounts_bank',
        'moneyAccounts_city',
        'moneyAccounts_state',
        'moneyAccounts_country',
        'moneyAccounts_status',
        'moneyAccounts_referenceBank',
    ];

    public function scopewhereMoneyAccountsKey($query, $key){
        if(!is_null($key)){
           return $query->where('CAT_MONEY_ACCOUNTS.moneyAccounts_key', 'like', $key.'%');
        }
        return $query;
    }
    
       public function scopewhereInstFinancialName($query, $bank){
        if(!is_null($bank)){
           return $query->where('CAT_FINANCIAL_INSTITUTIONS.instFinancial_name', 'like', '%'.$bank.'%');
        }
        return $query;
    }

    public function scopewhereMoneyAccountsStatus($query, $status){
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }

            return $query->where('CAT_MONEY_ACCOUNTS.moneyAccounts_status', '=', $status);
        }
        return $query;
    }

     public function scopewhereMoneyAccountsNumberAccount($query, $numeroCuenta){
        if(!is_null($numeroCuenta)){
           return $query->where('CAT_MONEY_ACCOUNTS.moneyAccounts_numberAccount', 'like', $numeroCuenta.'%');
        }
        return $query;
    }

       public function scopewhereMoneyAccountsAccountType($query, $tipoCuenta){
        if(!is_null($tipoCuenta)){
           return $query->where('CAT_MONEY_ACCOUNTS.moneyAccounts_accountType', 'like', $tipoCuenta.'%');
        }
        return $query;
    }

     public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y');
    }
}
