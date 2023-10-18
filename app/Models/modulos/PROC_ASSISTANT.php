<?php

namespace App\Models\modulos;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PROC_ASSISTANT extends Model
{
    use HasFactory;

    protected $table = 'PROC_ASSISTANT';
    protected $primaryKey = 'assistant_id';
 
    protected $fillable = [
        'assistant_companieKey',
        'assistant_branchKey',
        'assistant_branch',
        'assistant_movement',
        'assistant_movementID',
        'assistant_module',
        'assistant_moduleID',
        'assistant_money',
        'assistant_typeChange',
        'assistant_group',
        'assistant_account',
        'assistant_year',
        'assistant_period',
        'assistant_charge',
        'assistant_payment',
        'assistant_apply',
        'assistant_applyID',
        'assistant_canceled',
        
    ];

    public function scopewhereAssistantCreated($query, $date){
        if(!is_null($date)){
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
                    $now = Carbon::now();
                    $start = (new DateTime($now->format('Y-m-d')))->modify('first day of this month');
                    $end = (new DateTime($now->format('Y-m-d')))->modify('last day of this month');

                    $fecha_inicial = $start->format('Y-m-d');
                    $fecha_fin = $end->format('Y-m-d');
                    return $query->whereDate('PROC_ASSISTANT.created_at', '<=', $fecha_fin)->whereDate('PROC_ASSISTANT.created_at', '>=', $fecha_inicial)->orWhereNull('PROC_ASSISTANT.created_at');
                    break;
                case 'Año Móvil':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                    $fecha_año_actual = Carbon::now()->format('Y');
                    $fecha_inicial = $fecha_año_actual.'-01-01';

                    return $query->whereDate('PROC_ASSISTANT.created_at', '<=', $fecha_actual)->whereDate('PROC_ASSISTANT.created_at', '>=', $fecha_inicial);
                    break;

                case 'Año Pasado':
                    $fecha_año_inicioMes_pasado = new Carbon('last year');
                    $formato_fecha_año_inicioMes_pasado = $fecha_año_inicioMes_pasado->format('Y');
                    $inicioAñoPasado = $formato_fecha_año_inicioMes_pasado.'-01-01';
                    $finAñoPasado = $formato_fecha_año_inicioMes_pasado.'-12-31';

                    return $query->whereDate('PROC_ASSISTANT.created_at', '>=', $inicioAñoPasado)->whereDate('PROC_ASSISTANT.created_at', '<=', $finAñoPasado);

                    break;
                default:
                $fechasRangoArray = explode('+', $date);
                $fechaInicio = $fechasRangoArray[0];
                $fechaFinal = $fechasRangoArray[1];
                $fechaInicio = Carbon::parse($fechaInicio)->format('Y-m-d');
                $fechaFinal = Carbon::parse($fechaFinal)->format('Y-m-d');
                // dd($fechaInicio, $fechaFinal);
                // return $query->whereDate('PROC_ASSISTANT.created_at', '>=', $fechaInicio)->whereDate('PROC_ASSISTANT.created_at', '<=', $fechaFinal);
                return $query->whereDate('PROC_ASSISTANT.created_at', '>=', $fechaInicio)->whereDate('PROC_ASSISTANT.created_at', '<=', $fechaFinal);
                    break;
            }
        }
        return $query;
    }
    public function scopewhereAssistantAccount($query, $key){
            if(!is_null($key)){
                if($key === 'Todos') {
                    return $query;
                }
                if(ctype_digit($key)){
                    return $query->where('PROC_ASSISTANT.assistant_account', '=', (int) $key);
                }else{
                    return $query->where('PROC_ASSISTANT.assistant_account', '=', $key);
                }
            
            }
            return $query;
        
        }


     public function scopewhereAssistantMoney($query, $key){
            if(!is_null($key)){
                if ($key === 'Todos') {
                    return $query;
                }
                    return $query->where('PROC_ASSISTANT.assistant_money', '=', $key);
            }
            return $query;
        }

    public function scopewhereCreatedAt($query, $date){
            if(!is_null($date)){
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
                    $fecha_mes = Carbon::now()->startOfMonth();
                    $fecha_formato = $fecha_mes->format('Y-m-d');

                    return $query->whereDate('PROC_ASSISTANT.created_at', '>=', $fecha_formato)->whereDate('PROC_ASSISTANT.created_at', '<=', $fecha_actual);
                    break;
                    case 'Año Móvil':
                        $fecha_actual = Carbon::now()->format('Y-m-d');
                        $fecha_año_actual = Carbon::now()->format('Y');
                        $fecha_inicial = $fecha_año_actual.'-01-01';

                        return $query->whereDate('PROC_ASSISTANT.created_at', '<=', $fecha_actual)->whereDate('PROC_ASSISTANT.created_at', '>=', $fecha_inicial);
                        break;

                    case 'Año Pasado':
                        $fecha_año_inicioMes_pasado = new Carbon('last year');
                        $formato_fecha_año_inicioMes_pasado = $fecha_año_inicioMes_pasado->format('Y');
                        $inicioAñoPasado = $formato_fecha_año_inicioMes_pasado.'-01-01';
                        $finAñoPasado = $formato_fecha_año_inicioMes_pasado.'-12-31';

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

    public function scopewhereAssistantMovement($query, $key){
        if(!is_null($key)){
            if($key === 'Todos') {
                return $query;
            } else {
                return $query->where('PROC_ASSISTANT.assistant_movement', '=', $key);
            }
        }
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

       
}
