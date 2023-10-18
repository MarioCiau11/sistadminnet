<?php

namespace App\Models\modulos;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_ACCOUNTS_RECEIVABLE extends Model
{
    use HasFactory;

    protected $table = 'PROC_ACCOUNTS_RECEIVABLE';
    protected $primaryKey = 'accountsReceivable_id';
    public $incrementing = false;
    public $timestamps = false;


    protected $fillable = [
        'accountsReceivable_movement',
        'accountsReceivable_movementID',
        'accountsReceivable_issuedate',
        'accountsReceivable_money',
        'accountsReceivable_typeChange',
        'accountsReceivable_moneyAccount',
        'accountsReceivable_customer',
        'accountsReceivable_condition',
        'accountsReceivable_expiration',
        'accountsReceivable_formPayment',
        'accountsReceivable_observations',
        'accountsReceivable_amount',
        'accountsReceivable_taxes',
        'accountsReceivable_total',
        'accountsReceivable_retention1',
        'accountsReceivable_retentionISR',
        'accountsReceivable_retention2',
        'accountsReceivable_retentionIVA',
        'accountsReceivable_moratoriumDays',
        'accountsReceivable_concept',
        'accountsReceivable_reference',
        'accountsReceivable_balance',
        'accountsReceivable_company',
        'accountsReceivable_branchOffice',
        'accountsReceivable_user',
        'accountsReceivable_status',
        'accountsReceivable_originType',
        'accountsReceivable_origin',
        'accountsReceivable_originID',
        'accountsReceivable_CFDI',
        'accountsReceivable_stamped',
    ];

    public function scopewhereAccountsReceivableMovementID($query, $key)
    {
        if(!is_null($key))
        {
            return $query->where('accountsReceivable_movementID', 'like', '%'.$key.'%');
        }
        return $query;
    }

    public function scopewhereAccountsReceivableCustomer($query, $key)
    {
        if(!is_null($key))
        {
            if(ctype_digit($key)){
                $query->where('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_customer', '=', (int) $key);
            }else{
                $query->where('customers_businessName', 'LIKE', '%'.$key.'%');
            }
        }
        return $query;
    }

    public function scopewhereAccountsReceivableMovement($query, $key)
    {
        if(!is_null($key))
        {
            if($key === 'Todos'){
                return $query;
            }
            return $query->where('accountsReceivable_movement', $key);
        }
        return $query;
    }

    public function scopewhereAccountsReceivableStatus($query, $key)
    {
        if(!is_null($key))
        {
            if($key === 'Todos'){
                return $query;
            }
            return $query->where('accountsReceivable_status', "=", $key);
        }
        return $query;
    }

    public function scopewhereAccountsReceivableDate($query, $key)
    {
        if(!is_null($key))
        {
            switch ($key) {
                case 'Hoy':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                     return $query->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_issuedate', '=', $fecha_actual);
                    break;
                case 'Ayer':
                    $fecha_ayer = new Carbon('yesterday');
                     return $query->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_issuedate', '=', $fecha_ayer);
                    break;

                case 'Semana':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                    $fecha_semana = new Carbon('last week');
                    $fecha_formato = $fecha_semana->format('Y-m-d');
                     return $query->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_issuedate', '<=', $fecha_actual)->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_issuedate', '>=', $fecha_formato );
                    break;

                    case 'Mes':
                        $now = Carbon::now();
                        $start = (new DateTime($now->format('Y-m-d')))->modify('first day of this month');
                        $end = (new DateTime($now->format('Y-m-d')))->modify('last day of this month');
    
                        $fecha_inicial = $start->format('Y-m-d');
                        $fecha_fin = $end->format('Y-m-d');
                         return $query->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_issuedate', '<=', $fecha_fin)->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_issuedate', '>=', $fecha_inicial )->orwhereNull('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_issuedate');
                        
                         break;

                        case 'Año Móvil':
                            $fecha_año_actual = Carbon::now()->format('Y');
                            $fecha_inicial= $fecha_año_actual.'-01-01';
                            $fecha_final = $fecha_año_actual.'-12-31';
             
                             return $query->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_issuedate', '<=', $fecha_final)->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_issuedate', '>=', $fecha_inicial );
                            break;

                        case 'Año Pasado':
                            $fecha_año_inicioMes_pasado = new Carbon('last year');
                            $formato_fecha_inicioMes_pasado = $fecha_año_inicioMes_pasado->format('Y');
                            $inicoAñoPasado = $formato_fecha_inicioMes_pasado.'-01-01';
                            $finAñoPasado = $formato_fecha_inicioMes_pasado.'-12-31';

                            return $query->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_issuedate', '>=', $inicoAñoPasado )->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_issuedate', '<=', $finAñoPasado );
                            break;
                
                default:
                $fechasRangoArray = explode('+', $key);
                   $fechaInicio = $fechasRangoArray[0];
                   $fechaFinal = $fechasRangoArray[1];
                    $fechaInicio = Carbon::parse($fechaInicio)->format('Y-m-d');
                    $fechaFinal = Carbon::parse($fechaFinal)->format('Y-m-d');
                    return $query->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_issuedate', '>=', $fechaInicio)->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_issuedate', '<=', $fechaFinal);
                    break;
                }
        }
        return $query;
    }

    public function scopewhereAccountsReceivableExpiration($query, $key)
    {
        if(!is_null($key))
        {
            switch ($key) {
                case 'Hoy':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                     if($key === null){
                        return $query;
                    }
                    return $query->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration', '=', $fecha_actual)->orWhereNull('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration');
                    break;
                case 'Ayer':
                    $fecha_ayer = new Carbon('yesterday');
                     return $query->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration', '=', $fecha_ayer)->orWhereNull('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration');
                    break;

                case 'Semana':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                    $fecha_semana = new Carbon('last week');
                    $fecha_formato = $fecha_semana->format('Y-m-d');
                     return $query->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration', '<=', $fecha_actual)->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration', '>=', $fecha_formato)->orWhereNull('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration');
                    break;

                    case 'Mes':
                        
                        // $fecha_mes = new Carbon('last month');
                        // $fecha_formato = $fecha_mes->format('Y-m-d');
                        
                        //buscar el mes actual
                        $now = Carbon::now();
                        $start    = (new DateTime($now->format('Y-m-d')))->modify('first day of this month');
                        $end      = (new DateTime($now->format('Y-m-d')))->modify('last day of this month');

                        $fecha_inicial = $start->format('Y-m-d');
                        $fecha_fin = $end->format('Y-m-d');
                        // dd($start, $end, $fecha_inicial, $fecha_fin);
                       
                         return $query->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration', '<=', $fecha_fin)->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration', '>=', $fecha_inicial)->orWhereNull('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration');

                        //  return $query->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration', '<=', $fecha_actual)->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration', '>=', $fecha_formato )->orWhereNull('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration');
                         break;

                        case 'Año Móvil':
                            $fecha_año_actual = Carbon::now()->format('Y');
                            $fecha_inicial= $fecha_año_actual.'-01-01';
                            $fecha_final = $fecha_año_actual.'-12-31';
             
                             return $query->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration', '<=', $fecha_final)->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration', '>=', $fecha_inicial )->orWhereNull('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration');
                            break;

                        case 'Año Pasado':
                            $fecha_año_inicioMes_pasado = new Carbon('last year');
                            $formato_fecha_inicioMes_pasado = $fecha_año_inicioMes_pasado->format('Y');
                            $inicoAñoPasado = $formato_fecha_inicioMes_pasado.'-01-01';
                            $finAñoPasado = $formato_fecha_inicioMes_pasado.'-12-31';

                            return $query->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration', '>=', $inicoAñoPasado )->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration', '<=', $finAñoPasado )->orWhereNull('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration');
                            break;
                
                default:
                $fechasRangoArray = explode('+', $key);
                   $fechaInicioVen = $fechasRangoArray[0];
                   $fechaFinalVen = $fechasRangoArray[1];
                    return $query->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration', '>=', $fechaInicioVen)->whereDate('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration', '<=', $fechaFinalVen)->orWhereNull('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_expiration');
                    break;
                }

        }
        
    }

    public function scopewhereAccountsReceivableUser($query, $key)
    {
        if(!is_null($key))
        {
               if($key === 'Todos') {
                    return $query;
                } else {
                    return $query->where('accountsReceivable_user', '=', $key);
                }
        }

        return $query;
    }

    public function scopewhereAccountsReceivablebranchOffice($query, $key)
    {
        if(!is_null($key))
        {
            if($key === 'Todos')
            {
                return $query;
                
            }
            return $query->where('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_branchOffice', '=', $key);

        }
    }

    public function scopewhereAccountsReceivableMoney($query, $key)
    {
        if(!is_null($key))
        {
            if($key === 'Todos')
            {
                return $query;
                
            }
            return $query->where('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_money', '=', $key);

        }
    }
    
    public function scopewhereBalanceMoney($query, $key){
        if(!is_null($key)){
            if($key === 'Todos') {
                return $query;
            } else {
                return $query->where('PROC_BALANCE.balance_money', '=', $key);
            }

        }
    }

    public function scopewhereBalancebranchKey($query, $key){
        if(!is_null($key)){
            if($key === 'Todos') {
                return $query;
            } else {
                return $query->where('PROC_BALANCE.balance_branchKey', '=', $key);
            }

        }

    }

     public function scopewhereAccountsReceivableStamped($query, $key){

        if(!is_null($key)){
            if($key !== 'Todos'){
                if($key === "Si"){
                    $query->where('accountsReceivable_stamped', "=",1);
                }

                if($key === "No"){
                    $query->where('accountsReceivable_stamped', "=",Null);
                }
            }
        }
        return $query;
    }
}
