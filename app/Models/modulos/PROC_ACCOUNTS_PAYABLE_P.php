<?php

namespace App\Models\modulos;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_ACCOUNTS_PAYABLE_P extends Model
{
    use HasFactory;

    protected $table = 'PROC_ACCOUNTS_PAYABLE_P';
    protected $primaryKey = 'accountsPayableP_id';
    public $incrementing = false;

    protected $fillable = [
        'accountsPayableP_movement',
        'accountsPayableP_movementID',
        'accountsPayableP_issuedate',
        'accountsPayableP_money',
        'accountsPayableP_typeChange',
        'accountsPayableP_moneyAccount',
        'accountsPayableP_provider',
        'accountsPayableP_condition',
        'accountsPayableP_expiration',
        'accountsPayableP_creditDays',
        'accountsPayableP_moratoriumDays',
        'accountsPayableP_formPayment',
        'accountsPayableP_observations',
        'accountsPayableP_amount',
        'accountsPayableP_taxes',
        'accountsPayableP_total',
        'accountsPayableP_balance',
        'accountsPayableP_balanceTotal',
        'accountsPayableP_concept',
        'accountsPayableP_reference',
        'accountsPayableP_company',
        'accountsPayableP_branchOffice',
        'accountsPayableP_user',
        'accountsPayableP_status',
        'accountsPayableP_originType',
        'accountsPayableP_origin',
        'accountsPayableP_originID',
        ];

        public function scopewhereAccountsPayablePProvider($query, $proveedor, $proveedor2, $proveedorNumero){
        
            if(!is_null($proveedor) ){
                 if($proveedor !== 'Todos'){
                    $query->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_provider', '>=', (int) $proveedor);
                 }
            }

            if(!is_null($proveedor2) ){
                 if($proveedor2 !== 'Todos'){
                    $query->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_provider', '<=', (int) $proveedor2);
                 }
            }


            if(!is_null($proveedorNumero) ){
                $query->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_provider','like','%'.$proveedorNumero.'%');
            }

            return $query;
        }

        public function scopewhereProviderCategory($query, $key){
            if(!is_null($key)){
                if($key == 'Todos'){
                    return $query;
                }
                return $query->where('CAT_PROVIDERS.providers_category', '=', $key);
            }
            return $query;
        }

        public function scopeWhereProviderGroup($query, $key){
            if(!is_null($key)){
                if($key == 'Todos'){
                    return $query;
                }
                return $query->where('CAT_PROVIDERS.providers_group', '=', $key);
            }
            return $query;
        }


        public function scopewhereAccountsPayablePStatus($query, $key){
            if(!is_null($key)){
                if($key === 'Todos') {
                    return $query;
                } else {
                    return $query->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_status', '=', $key);
                }
            }
            return $query;
        }

       
        public function scopewhereAccountsPayablePMoratoriumDays($query, $key){
            if(!is_null($key)){
                if($key === 'Todos') {
                    return $query;
                } else {
                    switch ($key) {
                        case 'A partir del 1 al 15':
                            return $query->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_moratoriumDays', '>=', 1)->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_moratoriumDays', '<=', 15);
                            break;


                        case 'A partir del 16 al 30':
                            
                            return $query->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_moratoriumDays', '>=', 16)->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_moratoriumDays', '<=', 30);

                            break;

                        case 'A partir del 31 al 60':
                            return $query->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_moratoriumDays', '>=', 31)->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_moratoriumDays', '<=', 60);

                            break;

                        case 'A partir del 61 al 90':
                            return $query->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_moratoriumDays', '>=', 61)->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_moratoriumDays', '<=', 90);

                            break;

                            case 'Más de 90 días':
                                return $query->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_moratoriumDays', '>=', 90);

                                break;
                        }
                }
            }
        }
    
        public function scopewhereAccountsPayablePMoney($query, $key){
            if(!is_null($key)){

                if($key === 'Todos') {
                    return $query;
                } else {
                    return $query->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_money', '=', $key);
                }
            }
        }
}
