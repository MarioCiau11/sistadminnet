<?php

namespace App\Models\modulos;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_INVENTORIES extends Model
{
    use HasFactory;

    protected $table = 'PROC_INVENTORIES';
    protected $primaryKey = 'inventories_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'inventories_movement',
        'inventories_movementID',
        'inventories_issuedate',
        'inventories_concept',
        'inventories_money',
        'inventories_typeChange',
        'inventories_expiration',
        'inventories_reference',
        'inventories_company',
        'inventories_branchOffice',
        'inventories_depot',
        'inventories_depotType',
        'inventories_depotDestiny',
        'inventories_depotDestinyType',
        'inventories_user',
        'inventories_status',
        'inventories_total',
        'inventories_lines',
        'inventories_originType',
        'inventories_origin',
        'inventories_originID',

    ];

    public function scopewhereInventoriesMovementID($query, $key){
        if(!is_null($key)){
            return $query->where('PROC_INVENTORIES.inventories_movementID', '=', $key);
        }
        return $query;
    }

    

    public function scopewhereInventoriesMovement($query, $key){
        if(!is_null($key)){
            if($key === 'Todos'){
                return $query;
        }
        return $query->where('PROC_INVENTORIES.inventories_movement', '=', $key);

    } 
    return $query;
    
        
    }

    public function scopewhereInventoriesStatus($query, $status){
        if(!is_null($status)){
            if($status === 'Todos'){
                return $query;
            }
            return $query->where('PROC_INVENTORIES.inventories_status', '=', $status);
        }
        return $query;
    }

    public function scopewhereInventoriesDate($query, $key){
        if(!is_null($key)){
            switch ($key) {
                case 'Hoy':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                     return $query->whereDate('PROC_INVENTORIES.inventories_issueDate', '=', $fecha_actual);
                    break;
                case 'Ayer':
                    $fecha_ayer = new Carbon('yesterday');
                     return $query->whereDate('PROC_INVENTORIES.inventories_issueDate', '=', $fecha_ayer);
                    break;

                case 'Semana':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                    $fecha_semana = new Carbon('last week');
                    $fecha_formato = $fecha_semana->format('Y-m-d');
                     return $query->whereDate('PROC_INVENTORIES.inventories_issueDate', '<=', $fecha_actual)->whereDate('PROC_INVENTORIES.inventories_issueDate', '>=', $fecha_formato );
                    break;

                case 'Mes':
                    $now = Carbon::now();
                    $start    = (new DateTime($now->format('Y-m-d')))->modify('first day of this month');
                    $end      = (new DateTime($now->format('Y-m-d')))->modify('last day of this month');

                    $fecha_inicial = $start->format('Y-m-d');
                    $fecha_fin = $end->format('Y-m-d');
                   
                
                    return $query->whereDate('PROC_INVENTORIES.inventories_issueDate', '>=', $fecha_inicial)->whereDate('PROC_INVENTORIES.inventories_issueDate', '<=', $fecha_fin )->OrWhereNull('PROC_INVENTORIES.inventories_issueDate');
                    break;

                case 'Año Móvil':
                     $fecha_año_actual = Carbon::now()->format('Y');
                    $fecha_inicial= $fecha_año_actual.'-01-01';
                    $fecha_final = $fecha_año_actual.'-12-31';
    
                    return $query->whereDate('PROC_INVENTORIES.inventories_issueDate', '<=', $fecha_final)->whereDate('PROC_INVENTORIES.inventories_issueDate', '>=', $fecha_inicial );
                    break;

                case 'Año Pasado':
                    $fecha_año_inicioMes_pasado = new Carbon('last year');
                    $formato_fecha_inicioMes_pasado = $fecha_año_inicioMes_pasado->format('Y');
                    $inicoAñoPasado = $formato_fecha_inicioMes_pasado.'-01-01';
                    $finAñoPasado = $formato_fecha_inicioMes_pasado.'-12-31';

                    return $query->whereDate('PROC_INVENTORIES.inventories_issueDate', '>=', $inicoAñoPasado )->whereDate('PROC_INVENTORIES.inventories_issueDate', '<=', $finAñoPasado );

                    break;
                default:
                $fechasRangoArray = explode('+', $key);
                $fechaInicio = $fechasRangoArray[0];
                $fechaFinal = $fechasRangoArray[1];
                return $query->whereDate('inventories_issueDate', '>=', $fechaInicio)->whereDate('inventories_issueDate', '<=', $fechaFinal);
                    break;
            }
           
        }
        return $query;
    }

    

    public function scopewhereInventoriesUser($query, $key){
        if(!is_null($key) && $key != 'Todos'){
            return $query->where('PROC_INVENTORIES.inventories_user', '=', $key);
        }
        return $query;
    }

    public function scopewhereInventoriesbranchOffice($query, $key){
        if(!is_null($key) && $key != 'Todos'){
            return $query->where('PROC_INVENTORIES.inventories_branchOffice', '=', $key);
        }
        return $query;
    }

    public function scopewhereInventoriesDepot($query, $key){
        if(!is_null($key)){
            if($key === 'Todos'){
                return $query;
            }
            
            return $query->where('PROC_INVENTORIES.inventories_depot', '=', $key);
        }
        return $query;
    }

    public function getInventoriesIssuedateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

}
