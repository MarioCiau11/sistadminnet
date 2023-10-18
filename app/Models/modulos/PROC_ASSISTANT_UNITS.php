<?php

namespace App\Models\modulos;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_ASSISTANT_UNITS extends Model
{
    use HasFactory;

    protected $table = 'PROC_ASSISTANT_UNITS';
    protected $primaryKey = 'assistantUnit_id';

    protected $fillable = [
        'assistantUnit_companieKey',
        'assistantUnit_branchKey',
        'assistantUnit_branch',
        'assistantUnit_movement',
        'assistantUnit_movementID',
        'assistantUnit_module',
        'assistantUnit_moduleID',
        'assistantUnit_money',
        'assistantUnit_typeChange',
        'assistantUnit_group',
        'assistantUnit_account',
        'assistantUnit_year',
        'assistantUnit_period',
        'assistantUnit_charge',
        'assistantUnit_payment',
        'assistantUnit_chargeUnit',
        'assistantUnit_paymentUnit',
        'assistantUnit_apply',
        'assistantUnit_applyID',
        'assistantUnit_canceled',
        'asssistantUnit_costumer',
    ];

    public function scopewhereAssistantUnitAccount($query, $articulo, $articulo2, $nombreArticulo)
    {
        if (!is_null($articulo)) {
            if($articulo !== 'Todos'){
                $query->where('PROC_ASSISTANT_UNITS.assistantUnit_account', '>=', (int)$articulo);
            }
        }

        if (!is_null($articulo2)) {
            if($articulo2 !== 'Todos'){
                $query->where('PROC_ASSISTANT_UNITS.assistantUnit_account', '<=', (int)$articulo2);
            }
        }

        if (!is_null($nombreArticulo)) {
                if(is_numeric($nombreArticulo)){
                    $query->where('PROC_ASSISTANT_UNITS.assistantUnit_account', '=', (int)$nombreArticulo);
                }else{
                    $query->where('CAT_ARTICLES.articles_descript', 'LIKE', '%'.$nombreArticulo.'%');
                }
        }
        return $query;

    }

    public function scopewhereAssistantUnitDate($query, $key){
        if(!is_null($key)){
            switch ($key) {
                case 'Hoy':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                     return $query->whereDate('PROC_ASSISTANT_UNITS.created_at', '=', $fecha_actual);
                    break;
                case 'Ayer':
                    $fecha_ayer = new Carbon('yesterday');
                     return $query->whereDate('PROC_ASSISTANT_UNITS.created_at', '=', $fecha_ayer);
                    break;

                case 'Semana':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                    $fecha_semana = new Carbon('last week');
                    $fecha_formato = $fecha_semana->format('Y-m-d');
                     return $query->whereDate('PROC_ASSISTANT_UNITS.created_at', '<=', $fecha_actual)->whereDate('PROC_ASSISTANT_UNITS.created_at', '>=', $fecha_formato );
                    break;

                case 'Mes':
                    $now = Carbon::now();
                    $start    = (new DateTime($now->format('Y-m-d')))->modify('first day of this month');
                    $end      = (new DateTime($now->format('Y-m-d')))->modify('last day of this month');

                    $fecha_inicial = $start->format('Y-m-d');
                    $fecha_fin = $end->format('Y-m-d');
                
                    return $query->whereDate('PROC_ASSISTANT_UNITS.created_at', '<=', $fecha_fin)->whereDate('PROC_ASSISTANT_UNITS.created_at', '>=', $fecha_inicial)->orWhereNull('PROC_ASSISTANT_UNITS.created_at');

                    break;

                case 'Año Móvil':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                    $fecha_año_actual = Carbon::now()->format('Y');
                    $fecha_inicial= $fecha_año_actual.'-01-01';
    
                    return $query->whereDate('PROC_ASSISTANT_UNITS.created_at', '<=', $fecha_actual)->whereDate('PROC_ASSISTANT_UNITS.created_at', '>=', $fecha_inicial );
                    break;

                case 'Año Pasado':
                    $fecha_año_inicioMes_pasado = new Carbon('last year');
                    $formato_fecha_inicioMes_pasado = $fecha_año_inicioMes_pasado->format('Y');
                    $inicoAñoPasado = $formato_fecha_inicioMes_pasado.'-01-01';
                    $finAñoPasado = $formato_fecha_inicioMes_pasado.'-12-31';

                    return $query->whereDate('PROC_ASSISTANT_UNITS.created_at', '>=', $inicoAñoPasado )->whereDate('PROC_ASSISTANT_UNITS.created_at', '<=', $finAñoPasado );

                    break;
                default:
                $fechasRangoArray = explode('+', $key);
                $fechaInicio = $fechasRangoArray[0];
                $fechaFinal = $fechasRangoArray[1];
                $fechaInicio = Carbon::parse($fechaInicio)->format('Y-m-d');
                $fechaFinal = Carbon::parse($fechaFinal)->format('Y-m-d');
                return $query->whereDate('PROC_ASSISTANT_UNITS.created_at', '>=', $fechaInicio)->whereDate('PROC_ASSISTANT_UNITS.created_at', '<=', $fechaFinal);
                    break;
            }
           
        }
        return $query;
    }

    public function scopewherearticleCategory($query, $key){
        if(!is_null($key)){
            if($key === "Todos"){
                return $query;
            }
            return $query->where('CAT_ARTICLES.articles_category', '=', $key);
        }
        return $query;
    }

    //HACEMOS EL SCOPE PARA EL FILTRO DE EXISTENCIA. COMO NO TENEMOS UN CAMPO EN LA TABLA DE ARTICULOS QUE SE LLAME EXISTENCIA, LO QUE HAREMOS SERÁ UNA SUMA DE LAS EXISTENCIAS DE LOS ARTICULOS EN LA TABLA DE MOVIMIENTOS, SI LA SUMA ES MAYOR A 0, EL ARTICULO TIENE EXISTENCIA, SI ES IGUAL A 0, NO TIENE EXISTENCIA
    

    public function scopewherearticleFamily($query, $key){
        if(!is_null($key)){
           if($key === "Todos"){
                return $query;
            }
            return $query->where('CAT_ARTICLES.articles_family', '=', $key);
        }
        return $query;
    }

    public function scopewherearticleGroup($query, $key){
        if(!is_null($key)){
            if($key === "Todos"){
                return $query;
            }
            return $query->where('CAT_ARTICLES.articles_group', '=', $key);
        }
        return $query;
    }

    public function scopewhereAssistantUnitMovement($query, $key){
        if(!is_null($key)){
            if($key === "Todos"){
                return $query;
            }
            return $query->where('assistantUnit_movement', '=', $key);
        }
        return $query;
    }

    public function scopewhereAssistantUnitDepot($query, $key){
        if(!is_null($key)){
            if($key === "Todos"){
                return $query;
            }
            return $query->where('assistantUnit_group', '=', $key);
        }
        return $query;
    }

}
