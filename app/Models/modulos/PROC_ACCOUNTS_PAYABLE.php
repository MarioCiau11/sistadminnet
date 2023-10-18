<?php

namespace App\Models\modulos;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_ACCOUNTS_PAYABLE extends Model
{
    use HasFactory;

    protected $table = 'PROC_ACCOUNTS_PAYABLE';
    protected $primaryKey = 'accountsPayable_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'accountsPayable_movement',
        'accountsPayable_movementID',
        'accountsPayable_issuedate',
        'accountsPayable_money',
        'accountsPayable_typeChange',
        'accountsPayable_moneyAccount',
        'accountsPayable_provider',
        'accountsPayable_condition',
        'accountsPayable_expiration',
        'accountsPayable_formPayment',
        'accountsPayable_observations',
        'accountsPayable_amount',
        'accountsPayable_taxes',
        'accountsPayable_total',
        'accountsPayable_retention',
        'accountsPayable_retention2',
        'accountsPayable_retention3',
        'accountsPayable_moratoriumDays',
        'accountsPayable_concept',
        'accountsPayable_reference',
        'accountsPayable_balance',
        'accountsPayable_company',
        'accountsPayable_branchOffice',
        'accountsPayable_user',
        'accountsPayable_status',
        'accountsPayable_originType',
        'accountsPayable_origin',
        'accountsPayable_originID',
        ];

         public function scopewhereAccountsPayableMovementID($query, $key)
        {
            if(!is_null($key)){
                return $query->where('accountsPayable_movementID', '=', $key);
            }
            return $query;
        }
    
        public function scopewhereAccountsPayableProvider($query, $key){
            if(!is_null($key)){
                if($key !== 'Todos'){
                    if(ctype_digit($key)){
                        $query->where('PROC_ACCOUNTS_PAYABLE.accountsPayable_provider', '=', (int) $key);
                    }else{
                        $query->where('providers_name', 'LIKE','%'.$key.'%');
                    }
                }
            }
            return $query;
        }

        public function scopewhereAccountsPayableMovement($query, $key){
            if(!is_null($key)){
                if($key === 'Todos') {
                    return $query;
                } else {
                    return $query->where('accountsPayable_movement', '=', $key);
                }
            }
        }

        public function scopewhereAccountsPayableStatus($query, $key){
            if(!is_null($key)){
                if($key === 'Todos') {
                    return $query;
                } else {
                    return $query->where('accountsPayable_status', '=', $key);
                }
            }
            return $query;
        }

        public function scopewhereAccountsPayableDate($query, $date){
            if(!is_null($date)){
                switch ($date) {
                    case 'Hoy':
                        $fecha_actual = date('Y-m-d');
                        return $query->whereDate('accountsPayable_issuedate', '=', $fecha_actual);
                        break;
                    case 'Ayer':
                        $fecha_ayer = new Carbon('yesterday');
                        $fecha_ayer = $fecha_ayer->format('Y-m-d');
                        return $query->whereDate('accountsPayable_issuedate', '=', $fecha_ayer);
                        break;
                    case 'Semana':
                        $fecha_actual = Carbon::now()->format('Y-m-d');
                        $fecha_semana = new Carbon('last week');
                        $fecha_formato = $fecha_semana->format('Y-m-d');
                        return $query->whereDate('accountsPayable_issuedate', '<=', $fecha_actual)->whereDate('accountsPayable_issuedate', '>=', $fecha_formato);
                        break;
                    case 'Mes':
                        $now = Carbon::now();
                        $start    = (new DateTime($now->format('Y-m-d')))->modify('first day of this month');
                        $end      = (new DateTime($now->format('Y-m-d')))->modify('last day of this month');
    
                        $fecha_inicial = $start->format('Y-m-d');
                        $fecha_fin = $end->format('Y-m-d');

                        return $query->whereDate('accountsPayable_issuedate', '<=', $fecha_fin)->whereDate('accountsPayable_issuedate', '>=', $fecha_inicial)->orWhereNull('accountsPayable_issuedate');
                        break;

                    case 'Año Móvil':
                        $fecha_año_actual = Carbon::now()->format('Y');
                        $fecha_inicial= $fecha_año_actual.'-01-01';
                        $fecha_final = $fecha_año_actual.'-12-31';

                        return $query->whereDate('accountsPayable_issuedate', '<=', $fecha_final)->whereDate('accountsPayable_issuedate', '>=', $fecha_inicial);
                        break;
                    case 'Año Pasado':
                        $fecha_año_inicioMes_pasado = new Carbon('last year');
                        $formato_fecha_año_inicioMes_pasado = $fecha_año_inicioMes_pasado->format('Y');
                        $inicioAñoPasado = $formato_fecha_año_inicioMes_pasado.'-01-01';
                        $finAñoPasado = $formato_fecha_año_inicioMes_pasado.'-12-31';
                        return $query->whereDate('accountsPayable_issuedate', '>=', $finAñoPasado)->whereDate('accountsPayable_issuedate', '<=', $inicioAñoPasado);
                        break;

                    default:
                    $fechasRangoArray = explode('+', $date);
                    $fecha_inicio = $fechasRangoArray[0];
                    $fecha_fin = $fechasRangoArray[1];
                    return $query->whereDate('accountsPayable_issuedate', '>=', $fecha_inicio)->whereDate('accountsPayable_issuedate', '<=', $fecha_fin);
                        break;

                }
            }
            return $query;
        }

        public function scopewhereAccountsPayableMoney($query, $key){
            if(!is_null($key)){
                if($key === 'Todos') {
                    return $query;
                } else {
                    return $query->where('accountsPayable_money', '=', $key);
                }

            }
            return $query;
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

        public function scopewhereAccountsPayableUser($query, $key){
            if(!is_null($key)){
                if($key === 'Todos') {
                    return $query;
                } else {
                    return $query->where('accountsPayable_user', '=', $key);
                }
            }
            return $query;
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

        public function scopewhereAccountsPayableBranchOffice($query, $key){
            if(!is_null($key)){
                if($key === 'Todos') {
                    return $query;
                } else {
                    return $query->where('accountsPayable_branchOffice', '=', $key);
                }
            }
            return $query;
        }

        public function scopewhereAssistantMoney($query, $key){
            if(!is_null($key)){
                if($key === 'Todos') {
                    return $query;
                } else {
                    return $query->where('PROC_ASSISTANT.assistant_money', '=', $key);
                }

            }
        }
    
}
