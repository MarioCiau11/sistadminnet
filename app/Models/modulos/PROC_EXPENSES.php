<?php

namespace App\Models\modulos;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_EXPENSES extends Model
{
    use HasFactory;

    protected $table = 'PROC_EXPENSES';
    protected $primaryKey = 'expenses_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'expenses_movement',
        'expenses_movementID',
        'expenses_issueDate',
        'expenses_lastChange',
        'expenses_money',
        'expenses_typeChange',
        'expenses_provider',
        'expenses_observations',
        'expenses_moneyAccount',
        'expenses_typeAccount',
        'expenses_paymentMethod',
        'expenses_condition',
        'expenses_expiration',
        'expenses_amount',
        'expenses_taxes',
        'expenses_total',
        'expenses_antecedents',
        'expenses_antecedentsName',
        'expenses_fixedAssets',
        'expenses_fixedAssetsName',
        'expenses_fixedAssetsSerie',
        'expenses_company',
        'expenses_branchOffice',
        'expenses_user',
        'expenses_status',
        ];


    
    
        public function scopewhereExpensesMovementID($query, $key){
            if(!is_null($key)){
                return $query->where('PROC_EXPENSES.expenses_movementID', '=', $key);
            }
            return $query;
        }
    
        public function scopewhereExpensesProvider($query, $key){
            if(!is_null($key)){
                if($key !== 'Todos'){
                    if(ctype_digit($key)){
                        $query->where('PROC_EXPENSES.expenses_provider', '=', (int)$key);
                    }else{
                    $query->Where('CAT_PROVIDERS.providers_name', 'LIKE', '%' . $key . '%');
                    }
                }
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
    
        public function scopewhereExpensesMovement($query, $key){
            if(!is_null($key)){
                if($key === 'Todos') {
                    return $query;
                }
                return $query->where('expenses_movement', '=', $key);
            }
            return $query;
        }

        public function scopewhereExpensesStatus($query, $status){
            if(!is_null($status)){
                if($status === 'Todos'){
                    return $query;
            }
            return $query->where('expenses_status', '=', $status);
            }
            return $query;
        }

        public function scopewhereExpensesDate($query, $date){
            if(!is_null($date)){
                switch ($date) {
                    case 'Hoy':
                        $fecha_actual = Carbon::now()->format('Y-m-d');
                        return $query->whereDate('expenses_issueDate', '=', $fecha_actual);
                        break;
                    case 'Ayer':
                        $fecha_ayer = new Carbon('yesterday');
                        $fecha_ayer = $fecha_ayer->format('Y-m-d');
                        return $query->whereDate('expenses_issueDate', '=', $fecha_ayer);
                        break;
                    case 'Semana':
                        $fecha_actual = Carbon::now()->format('Y-m-d');
                        $fecha_semana = new Carbon('last week');
                        $fecha_formato = $fecha_semana->format('Y-m-d');
                        return $query->whereDate('expenses_issueDate', '<=', $fecha_actual)->whereDate('expenses_issueDate', '>=', $fecha_formato);
                        break;
                    case 'Mes':
                        $now = Carbon::now();
                        $start    = (new DateTime($now->format('Y-m-d')))->modify('first day of this month');
                        $end      = (new DateTime($now->format('Y-m-d')))->modify('last day of this month');
    
                        $fecha_inicial = $start->format('Y-m-d');
                        $fecha_fin = $end->format('Y-m-d');
                        return $query->whereDate('expenses_issueDate', '>=', $fecha_inicial)->whereDate('expenses_issueDate', '<=', $fecha_fin)->OrWhereNull('expenses_issueDate');
                        break;
                    case 'Año Móvil':
                         $fecha_año_actual = Carbon::now()->format('Y');
                        $fecha_inicial= $fecha_año_actual.'-01-01';
                        $fecha_final = $fecha_año_actual.'-12-31';

                        return $query->whereDate('expenses_issueDate', '<=', $fecha_final)->whereDate('expenses_issueDate', '>=', $fecha_inicial);
                        break;

                    case 'Año Pasado':
                        $fecha_año_inicioMes_pasado = new Carbon('last year');
                        $formato_fecha_año_inicioMes_pasado = $fecha_año_inicioMes_pasado->format('Y');
                        $inicioAñoPasado = $formato_fecha_año_inicioMes_pasado.'-01-01';
                        $finAñoPasado = $formato_fecha_año_inicioMes_pasado.'-12-31';

                        return $query->whereDate('expenses_issueDate', '>=', $inicioAñoPasado)->whereDate('expenses_issueDate', '<=', $finAñoPasado);

                        break;
                    default:
                    $fechasRangoArray = explode('+', $date);
                    $fechaInicio = $fechasRangoArray[0];
                    $fechaFinal = $fechasRangoArray[1];
                    $fechaInicio = Carbon::parse($fechaInicio)->format('Y-m-d');
                    $fechaFinal = Carbon::parse($fechaFinal)->format('Y-m-d');
                    return $query->whereDate('expenses_issueDate', '>=', $fechaInicio)->whereDate('expenses_issueDate', '<=', $fechaFinal);
                        break;
                }
            }
            return $query;
        }

        public function scopewhereExpensesUser($query, $key){
            if(!is_null($key)){
                if($key === 'Todos'){
                    return $query;
                } else {
                    return $query->where('expenses_user', '=', $key);
                }

            }
            return $query;
        }

        public function scopewhereExpensesbranchOffice($query, $key){
            if(!is_null($key)){
                if($key === 'Todos'){
                    return $query;
                } else {
                    return $query->where('expenses_branchOffice', '=', $key);
                }

            }
            return $query;
        }

        public function scopewhereExpensesMoney($query, $key){
            if(!is_null($key)){
                if($key === 'Todos'){
                    return $query;
                } else {
                    return $query->where('expenses_money', '=', $key);
                }
            }
            return $query;
        }

        public function scopewhereExpensesDetailsConcept($query, $key){
            if(!is_null($key)){
                if($key === 'Todos'){
                    return $query;
                } else {
                    return $query->where('PROC_EXPENSES_DETAILS.expensesDetails_concept', '=', $key);
                }
            }
            return $query;
        }

        public function scopewhereExpensesAntecedentsName($query, $key){
            if(!is_null($key)){

                    return $query->where('expenses_antecedentsName', 'LIKE', '%'.$key.'%');
            }
            return $query;
        }

        public function scopewhereExpensesAntecedents($query, $key){
            if(!is_null($key)){
                if($key === 0){
                    return $query;
                } else {
                    return $query->where('expenses_antecedents', '=', $key);
                }
            }
        }

        public function scopewhereExpensesFixedAssetsName($query, $key){
            if(!is_null($key)){
                if($key === 'Todos'){
                    return $query;
                } else {
                    return $query->where('expenses_fixedAssetsName', '=', $key);
                }
            }
            return $query;
        }

        public function scopewhereExpensesFixedAssetsSeries($query, $key){
            if(!is_null($key)){
                if($key === 'Todos'){
                    return $query;
                } else {
                    return $query->where('expenses_fixedAssetsSerie', '=', $key);
                }
            }
            return $query;
        }
        public function scopewhereExpensesFixedAssets($query, $key){
            if(!is_null($key)){
                if($key === 0){
                    return $query;
                } else {
                    return $query->where('expenses_fixedAssets', '=', $key);
                }
            }
        }

        public function getExpensesIssuedateAttribute($value)
        {
            return Carbon::parse($value)->format('d-m-Y');
        }

        

    
    
}
