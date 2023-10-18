<?php

namespace App\Models\modulos;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_ACCOUNTS_RECEIVABLE_P extends Model
{
    use HasFactory;

    protected $table = 'PROC_ACCOUNTS_RECEIVABLE_P';
    protected $primaryKey = 'accountsReceivableP_id';

    protected $fillable = [
        'accountsReceivableP_movement',
        'accountsReceivableP_movementID',
        'accountsReceivableP_issuedate',
        'accountsReceivableP_money',
        'accountsReceivableP_typeChange',
        'accountsReceivableP_moneyAccount',
        'accountsReceivableP_customer',
        'accountsReceivableP_condition',
        'accountsReceivableP_expiration',
        'accountsReceivableP_creditDays',
        'accountsReceivableP_moratoriumDays',
        'accountsReceivableP_formPayment',
        'accountsReceivableP_observations',
        'accountsReceivableP_amount',
        'accountsReceivableP_taxes',
        'accountsReceivableP_total',
        'accountsReceivableP_balance',
        'accountsReceivableP_balanceTotal',
        'accountsReceivableP_concept',
        'accountsReceivableP_reference',
        'accountsReceivableP_company',
        'accountsReceivableP_branchOffice',
        'accountsReceivableP_user',
        'accountsReceivableP_status',
        'accountsReceivableP_originType',
        'accountsReceivableP_origin',
        'accountsReceivableP_originID',
    ];

    public function scopewhereAccountsReceivableMovementID($query, $key)
    {
        if(!is_null($key)){
            return $query->where('accountsReceivableP_movementID', '=', $key);
        }
        return $query;
    }

    // public function scopewhereAccountsReceivableCustomer($query, $key){
    //     if(!is_null($key)){
    //         return $query->where('accountsReceivableP_customer', 'LIKE', '%'.$key.'%')->orWhere('CAT_CUSTOMERS.customers_businessName', 'LIKE', '%'.$key.'%');
    //     }
    //     return $query;
    // }

    public function scopeWhereAccountsReceivablePCustomer($query, $cliente, $cliente2, $clienteNumero){
        if(!is_null($cliente)){
            if($cliente !== 'Todos'){
                $query->where('accountsReceivableP_customer', '>=', (int)$cliente);
            }
        }

        if(!is_null($cliente2)){
            if($cliente2 !== 'Todos'){
                $query->where('accountsReceivableP_customer', '<=', (int)$cliente2);
            }
        }

        if(!is_null($clienteNumero)){
                $query->where('accountsReceivableP_customer', 'like', '%'.$clienteNumero.'%')->orWhere('CAT_CUSTOMERS.customers_businessName', 'like', '%'.$clienteNumero.'%');
        }

        return $query;


    }

    public function scopewhereCustomerCategory($query, $key){
        if(!is_null($key)){
            if($key == 'Todos'){
                return $query;
            }
            return $query->where('CAT_CUSTOMERS.customers_category', '=', $key);
        }
        return $query;
    }

    public function scopeWhereCustomerGroup($query, $key){
        if(!is_null($key)){
            if($key == 'Todos'){
                return $query;
            }
            return $query->where('CAT_CUSTOMERS.customers_group', '=', $key);
        }
        return $query;
    }

    public function scopewhereAccountsReceivableMovement($query, $key){
        if(!is_null($key)){
            if($key === 'Todos') {
                return $query;
            } else {
                return $query->where('accountsReceivableP_movement', '=', $key);
            }
        }
    }

    public function scopewhereAccountsReceivableStatus($query, $key){
        if(!is_null($key)){
            if($key === 'Todos') {
                return $query;
            } else {
                return $query->where('accountsReceivableP_status', '=', $key);
            }
        }
        return $query;
    }

    public function scopewhereAccountsReceivableDate($query, $date){
        if(!is_null($date)){
            switch ($date) {
                case 'Hoy':
                    $fecha_actual = date('Y-m-d');
                    return $query->whereDate('accountsReceivableP_issuedate', '=', $fecha_actual);
                    break;
                case 'Ayer':
                    $fecha_ayer = new Carbon('yesterday');
                    $fecha_ayer = $fecha_ayer->format('Y-m-d');
                    return $query->whereDate('accountsReceivableP_issuedate', '=', $fecha_ayer);
                    break;
                case 'Semana':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                    $fecha_semana = new Carbon('last week');
                    $fecha_formato = $fecha_semana->format('Y-m-d');
                    return $query->whereDate('accountsReceivableP_issuedate', '<=', $fecha_actual)->whereDate('accountsReceivableP_issuedate', '>=', $fecha_formato);
                    break;
                case 'Mes':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                    $fecha_mes = new Carbon('last month');
                    $fecha_formato = $fecha_mes->format('Y-m-d');
                    return $query->whereDate('accountsReceivableP_issuedate', '<=', $fecha_actual)->whereDate('accountsReceivableP_issuedate', '>=', $fecha_formato);
                    break;

                case 'Año Móvil':
                    $fecha_actual = Carbon  ::now()->format('Y-m-d');
                    $fecha_año_actual = Carbon::now()->format('Y');
                    $fecha_inicial = $fecha_año_actual.'-01-01';
                    return $query->whereDate('accountsReceivableP_issuedate', '<=', $fecha_actual)->whereDate('accountsReceivableP_issuedate', '>=', $fecha_inicial);
                    break;
                case 'Año Pasado':
                    $fecha_año_inicioMes_pasado = new Carbon('last year');
                    $formato_fecha_año_inicioMes_pasado = $fecha_año_inicioMes_pasado->format('Y');
                    $inicioAñoPasado = $formato_fecha_año_inicioMes_pasado.'-01-01';
                    $finAñoPasado = $formato_fecha_año_inicioMes_pasado.'-12-31';
                    return $query->whereDate('accountsReceivableP_issuedate', '>=', $finAñoPasado)->whereDate('accountsReceivableP_issuedate', '<=', $inicioAñoPasado);
                    break;

                default:
                $fechasRangoArray = explode('+', $date);
                // dd($fechasRangoArray);
                $fecha_inicio = $fechasRangoArray[0];
                $fecha_fin = $fechasRangoArray[1];
                return $query->whereDate('accountsReceivableP_issuedate', '>=', $fecha_inicio)->whereDate('accountsReceivableP_issuedate', '<=', $fecha_fin);
                    break;

            }
        }
        return $query;
    }

    public function scopewhereAccountsReceivableMoratoriumDays($query, $key){
        if(!is_null($key)){
            if($key === 'Todos') {
                return $query;
            } else {
                switch ($key) {
                    case 'A partir del 1 al 15':
                        return $query->where('accountsReceivableP_moratoriumDays', '>=', 1)->where('accountsReceivableP_moratoriumDays', '<=', 15);
                        break;


                    case 'A partir del 16 al 30':
                        
                        return $query->where('accountsReceivableP_moratoriumDays', '>=', 16)->where('accountsReceivableP_moratoriumDays', '<=', 30);

                        break;

                    case 'A partir del 31 al 60':
                        return $query->where('accountsReceivableP_moratoriumDays', '>=', 31)->where('accountsReceivableP_moratoriumDays', '<=', 60);

                        break;

                    case 'A partir del 61 al 90':
                        return $query->where('accountsReceivableP_moratoriumDays', '>=', 61)->where('accountsReceivableP_moratoriumDays', '<=', 90);

                        break;

                        case 'Más de 90 días':
                            return $query->where('accountsReceivableP_moratoriumDays', '>=', 90);

                            break;
                    }
            }
        }
    }
    
    public function scopewhereAccountsReceivableUser($query, $key){
        if(!is_null($key)){
            if($key === 'Todos') {
                return $query;
            } else {
                return $query->where('accountsReceivableP_user', '=', $key);
            }
        }
        return $query;
    }

    public function scopewhereAccountsReceivableBranchOffice($query, $key){
        if(!is_null($key)){
            if($key === 'Todos') {
                return $query;
            } else {
                return $query->where('accountsReceivableP_branchOffice', '=', $key);
            }
        }
        return $query;
    }

    public function scopewhereAccountsReceivableMoney($query, $key){
        if(!is_null($key)){
            return $query->where('accountsReceivableP_money', '=', $key);

        }
        return $query;
    }

    

}
