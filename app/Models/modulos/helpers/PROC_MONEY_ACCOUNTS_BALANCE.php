<?php

namespace App\Models\modulos\helpers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_MONEY_ACCOUNTS_BALANCE extends Model
{
    use HasFactory;

    protected $table = 'PROC_MONEY_ACCOUNTS_BALANCE';
    protected $primaryKey = 'moneyAccountsBalance_id';

    protected $fillable = [
        'moneyAccountsBalance_moneyAccount',
        'moneyAccountsBalance_accountType',
        'moneyAccountsBalance_money',
        'moneyAccountsBalance_balance',
        'moneyAccountsBalance_initialBalance',
        'moneyAccountsBalance_status',
        'moneyAccountsBalance_company',
    ];

    public function scopewhereMoneyAccountsBalanceMoneyAccount($query, $key){
        if(!is_null($key)){
            if($key === 'Todos') {
                return $query;
            }
                return $query->where('moneyAccountsBalance_moneyAccount', '=', $key);
            
        }
    }

    public function scopewhereMoneyAccountsBalanceMoney($query, $key){
        if(!is_null($key)){
             if($key === 'Todos') {
                return $query;
            }
            return $query->where('moneyAccountsBalance_money', '=', $key);
        }
    }

    public function scopewhereCreatedAt($query, $date)
    {
        if (!is_null($date)) {
            switch ($date) {
                case 'Hoy':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                    return $query->whereDate('PROC_ASSISTANT.created_at', '=', $fecha_actual);
                    break;
                case 'Ayer':
                    $fecha_ayer = new Carbon('yesterday');
                    $fecha_ayer = $fecha_ayer->format('Y-m-d');
                    return $query->whereDate('PROC_ASSISTANT.created_at', '=', $fecha_ayer);
                    break;
                case 'Semana':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                    $fecha_semana = new Carbon('last week');
                    $fecha_formato = $fecha_semana->format('Y-m-d');

                    return $query->whereDate('PROC_ASSISTANT.created_at', '<=', $fecha_actual)->whereDate('PROC_ASSISTANT.created_at', '>=', $fecha_formato);
                    break;
                case 'Mes':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                    $fecha_mes = new Carbon('last month');
                    $fecha_formato = $fecha_mes->format('Y-m-d');

                    return $query->whereDate('PROC_ASSISTANT.created_at', '>=', $fecha_formato)->whereDate('PROC_ASSISTANT.created_at', '<=', $fecha_actual);
                    break;
                case 'Año Móvil':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                    $fecha_año_actual = Carbon::now()->format('Y');
                    $fecha_inicial = $fecha_año_actual . '-01-01';

                    return $query->whereDate('PROC_ASSISTANT.created_at', '<=', $fecha_actual)->whereDate('PROC_ASSISTANT.created_at', '>=', $fecha_inicial);
                    break;

                case 'Año Pasado':
                    $fecha_año_inicioMes_pasado = new Carbon('last year');
                    $formato_fecha_año_inicioMes_pasado = $fecha_año_inicioMes_pasado->format('Y');
                    $inicioAñoPasado = $formato_fecha_año_inicioMes_pasado . '-01-01';
                    $finAñoPasado = $formato_fecha_año_inicioMes_pasado . '-12-31';

                    return $query->whereDate('PROC_ASSISTANT.created_at', '>=', $inicioAñoPasado)->whereDate('PROC_ASSISTANT.created_at', '<=', $finAñoPasado);

                    break;
                default:
                    $fechasRangoArray = explode('+', $date);
                    $fechaInicio = $fechasRangoArray[0];
                    $fechaFinal = $fechasRangoArray[1];
                    $fechaInicio = Carbon::parse($fechaInicio)->format('Y-m-d');
                    $fechaFinal = Carbon::parse($fechaFinal)->format('Y-m-d');
                    // dd($fechaInicio, $fechaFinal);
                    return $query->whereDate('PROC_ASSISTANT.created_at', '>=', $fechaInicio)->whereDate('PROC_ASSISTANT.created_at', '<=', $fechaFinal);
                    break;
            }
        }
        return $query;
    }


}
