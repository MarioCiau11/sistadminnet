<?php

namespace App\Models\modulos;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_TREASURY extends Model
{
    use HasFactory;

    protected $table = 'PROC_TREASURY';
    protected $primaryKey = 'treasuries_id';
    public $incremeting = false;
    public $timestamps = false;
    


    protected $fillable = [
        'treasuries_movement',
        'treasuries_movementID',
        'treasuries_issuedate',
        'treasuries_concept',
        'treasuries_money',
        'treasuries_typeChange',
        'treasuries_moneyAccount',
        'treasuries_moneyAccountOrigin',
        'treasuries_moneyAccountDestiny',
        'treasuries_paymentMethod',
        'treasuries_beneficiary',
        'treasuries_reference',
        'treasuries_amount',
        'treasuries_taxes',
        'treasuries_total',
        'treasuries_accountBalance',
        'treasuries_company',
        'treasuries_branchOffice',
        'treasuries_user',
        'treasuries_status',
        'treasuries_originType',
        'treasuries_origin',
        'treasuries_originID',
    ];

     public function scopewhereTreasuriesMovementID($query, $key)
    {
        if(!is_null($key)){
            return $query->where('PROC_TREASURY.treasuries_movementID', 'LIKE', '%'.$key.'%');
        }
        return $query;
    }

    public function scopewhereTreasuriesMovement($query, $key){
        if(!is_null($key)){
            if($key === 'Todos') {
                return $query;
            } else {
                return $query->where('treasuries_movement', '=', $key);
            }
        }
    }

  

    public function scopewhereTreasuriesStatus($query, $key){
        if(!is_null($key)){
            if($key === 'Todos') {
                return $query;
            } else {
                return $query->where('treasuries_status', '=', $key);
            }
        }
        return $query;
    }

    public function scopewhereTreasuriesBeneficiary($query, $key){
        if(!is_null($key)){
            return $query->where('treasuries_beneficiary', '=', $key);
        }
        return $query;
    }

    public function scopewhereTreasuriesDate($query, $date){
        if(!is_null($date)){
            switch ($date) {
                case 'Hoy':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                    return $query->whereDate('treasuries_issuedate', '=', $fecha_actual);
                    break;
                case 'Ayer':
                    $fecha_ayer = new Carbon('yesterday');
                    $fecha_ayer = $fecha_ayer->format('Y-m-d');
                    return $query->whereDate('treasuries_issuedate', '=', $fecha_ayer);
                    break;
                case 'Semana':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                    $fecha_semana = new Carbon('last week');
                    $fecha_formato = $fecha_semana->format('Y-m-d');
                    return $query->whereDate('treasuries_issuedate', '>=', $fecha_actual)->whereDate('treasuries_issuedate', '<=', $fecha_formato);
                    break;
                case 'Mes':
                    $now = Carbon::now();
                    $start    = (new DateTime($now->format('Y-m-d')))->modify('first day of this month');
                    $end      = (new DateTime($now->format('Y-m-d')))->modify('last day of this month');

                    $fecha_inicial = $start->format('Y-m-d');
                    $fecha_fin = $end->format('Y-m-d');
                    return $query->whereDate('treasuries_issuedate', '>=', $fecha_inicial)->whereDate('treasuries_issuedate', '<=', $fecha_fin);
                    break;
                case 'Año Móvil':
                     $fecha_año_actual = Carbon::now()->format('Y');
                    $fecha_inicial= $fecha_año_actual.'-01-01';
                    $fecha_final = $fecha_año_actual.'-12-31';
                    return $query->whereDate('treasuries_issuedate', '<=', $fecha_final)->whereDate('treasuries_issuedate', '>=', $fecha_inicial);
                    break;

                case 'Año Pasado':
                    $fecha_año_inicioMes_pasado = new Carbon('last year');
                    $formato_fecha_año_inicioMes_pasado = $fecha_año_inicioMes_pasado->format('Y');
                    $inicioAñoPasado = $formato_fecha_año_inicioMes_pasado.'-01-01';
                    $finAñoPasado = $formato_fecha_año_inicioMes_pasado.'-12-31';

                    return $query->whereDate('treasuries_issuedate', '>=', $inicioAñoPasado)->whereDate('treasuries_issuedate', '<=', $finAñoPasado);

                    break;
                default:
                $fechasRangoArray = explode('+', $date);
                $fechaInicio = $fechasRangoArray[0];
                $fechaFinal = $fechasRangoArray[1];
                return $query->whereDate('treasuries_issuedate', '>=', $fechaInicio)->whereDate('treasuries_issuedate', '<=', $fechaFinal);
                    break;
            }
        }
        return $query;
    }

    

        
    public function scopewhereTreasuriesUser($query, $key){
        if(!is_null($key)){
            if($key === 'Todos') {
                return $query;
            } else {
                return $query->where('treasuries_user', '=', $key);
            }
        }
        return $query;
    }

    public function scopewhereTreasuriesBranchOffice($query, $key){
        if(!is_null($key)){
            if($key === 'Todos') {
                return $query;
            } else {
                return $query->where('treasuries_branchOffice', '=', $key);
            }
        }
        return $query;
    }

    public function scopewhereTreasuriesMoney($query, $key){
        if(!is_null($key)){
            if($key === 'Todos') {
                return $query;
            } else {
                return $query->where('treasuries_money', '=', $key);
            }
        }
        return $query;
    }


   

    public function scopewhereTreasuriesMoneyAccount($query, $key){
        if(!is_null($key)){
            if($key === 'Todos') {
                return $query;
            }
                return $query->where('treasuries_moneyAccount', '=', $key);
            
        }
        return $query;
        
    }

      

    public function getExpensesIssuedateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }
}
