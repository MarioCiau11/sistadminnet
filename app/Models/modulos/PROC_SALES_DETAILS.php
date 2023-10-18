<?php

namespace App\Models\modulos;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_SALES_DETAILS extends Model
{
    use HasFactory;

    protected $table = 'PROC_SALES_DETAILS';
    protected $primaryKey = 'salesDetails_id';
    public $incrementing = false;

    protected $fillable = [
        'salesDetails_saleID',
        'salesDetails_article',
        'salesDetails_type',
        'salesDetails_descript',
        'salesDetails_quantity',
        'salesDetails_unitCost',
        'salesDetails_unit',
        'salesDetails_factor',
        'salesDetails_inventoryAmount',
        'salesDetails_amount',
        'salesDetails_ivaPorcent',
        'salesDetails_total',
        'salesDetails_packingUnit',
        'salesDetails_netQuantity',
        'salesDetails_apply',
        'salesDetails_applyIncrement',
        'salesDetails_branchOffice',
        'salesDetails_depot',
        'salesDetails_outstandingAmount',
        'salesDetails_canceledAmount',
        'salesDetails_referenceArticles',
    ];

    public function scopewhereSalesMovementID($query, $key)
    {
        if(!is_null($key))
            return $query->where('PROC_SALES.sales_movementID', 'like', '%'.$key.'%');
    }

    public function scopewhereSalesCustomer($query, $key)
    {
        if(!is_null($key))
            return $query->where('PROC_SALES.sales_customer', 'like', '%'.$key.'%')->orWhere('CAT_CUSTOMERS.customers_businessName', 'like', '%'.$key.'%');
    }

    public function scopewhereSalesMovement($query, $key)
    {
        if(!is_null($key))
            if($key === 'Todos')
                return $query;
            else
                return $query->where('PROC_SALES.sales_movement', $key);
    }

    public function scopewhereSalesStatus($query, $key)
    {
        if(!is_null($key))
            if($key === 'Todos')
                return $query;
            else
                return $query->where('PROC_SALES.sales_status', $key);
    }

    public function scopewhereSalesDate($query, $key){
        if(!is_null($key)){
            switch ($key) {
                case 'Hoy':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                     return $query->whereDate('PROC_SALES.sales_issuedate', '=', $fecha_actual);
                    break;
                case 'Ayer':
                    $fecha_ayer = new Carbon('yesterday');
                     return $query->whereDate('PROC_SALES.sales_issuedate', '=', $fecha_ayer);
                    break;

                case 'Semana':
                    $fecha_actual = Carbon::now()->format('Y-m-d');
                    $fecha_semana = new Carbon('last week');
                    $fecha_formato = $fecha_semana->format('Y-m-d');
                     return $query->whereDate('PROC_SALES.sales_issuedate', '<=', $fecha_actual)->whereDate('PROC_SALES.sales_issuedate', '>=', $fecha_formato );
                    break;

                    case 'Mes':
                        $fecha_actual = Carbon::now()->format('Y-m-d');
                        $fecha_mes = new Carbon('last month');
                        $fecha_formato = $fecha_mes->format('Y-m-d');
                       
                         return $query->whereDate('PROC_SALES.sales_issuedate', '<=', $fecha_actual)->whereDate('PROC_SALES.sales_issuedate', '>=', $fecha_formato );
                        break;

                        case 'Año Móvil':
                            $fecha_actual = Carbon::now()->format('Y-m-d');
                            $fecha_año_actual = Carbon::now()->format('Y');
                            $fecha_inicial= $fecha_año_actual.'-01-01';
             
                             return $query->whereDate('PROC_SALES.sales_issuedate', '<=', $fecha_actual)->whereDate('PROC_SALES.sales_issuedate', '>=', $fecha_inicial );
                            break;

                        case 'Año Pasado':
                            $fecha_año_inicioMes_pasado = new Carbon('last year');
                            $formato_fecha_inicioMes_pasado = $fecha_año_inicioMes_pasado->format('Y');
                            $inicoAñoPasado = $formato_fecha_inicioMes_pasado.'-01-01';
                            $finAñoPasado = $formato_fecha_inicioMes_pasado.'-12-31';

                            return $query->whereDate('PROC_SALES.sales_issuedate', '>=', $inicoAñoPasado )->whereDate('PROC_SALES.sales_issuedate', '<=', $finAñoPasado );
                            break;
                
                default:
                    $fechasRangoArray = explode('+', $key);
                    $fechaInicio = $fechasRangoArray[0];
                    $fechaFinal = $fechasRangoArray[1];
                    $fechaInicio = Carbon::parse($fechaInicio)->format('Y-m-d');
                    $fechaFinal = Carbon::parse($fechaFinal)->format('Y-m-d');
                    return $query->whereDate('PROC_SALES.sales_issuedate', '>=', $fechaInicio)->whereDate('PROC_SALES.sales_issuedate', '<=', $fechaFinal);
                    break;
            }
           
        }
        return $query;
    }


    public function scopewhereSalesUser($query, $key)
    {
        if(!is_null($key))
            if($key === 'Todos')
                return $query;
            else
                return $query->where('PROC_SALES.sales_user', $key);
    }

    public function scopewhereSalesBranchOffice($query, $key)
    {
        if(!is_null($key))
            if($key === 'Todos')
                return $query;
            else
                return $query->where('PROC_SALES.sales_branchOffice', $key);
    }

    public function scopewhereSalesMoney($query, $key)
    {
        if(!is_null($key))
            if($key === 'Todos')
                return $query;
            else
                return $query->where('PROC_SALES.sales_money', $key);
    }

    public function scopewhereSalesDepot($query, $key)
    {
        if(!is_null($key))
            if($key === 'Todos')
                return $query;
            else
                return $query->where('PROC_SALES.sales_depot', $key);
    }

    public function scopewhereSalesUnit($query, $key)
    {
        if(!is_null($key))
            if($key === 'Todos')
                return $query;
            else
                return $query->where('PROC_SALES_DETAILS.salesDetails_unit', $key);
    }

    public function scopewhereSalesArticle($query, $key)
    {
        if(!is_null($key) && $key != 'Todos')
            return $query->where('PROC_SALES_DETAILS.salesDetails_descript', $key);
    }
}
