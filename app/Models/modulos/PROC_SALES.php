<?php

namespace App\Models\modulos;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_SALES extends Model
{
    use HasFactory;

    protected $table = 'PROC_SALES';
    protected $primaryKey = 'sales_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'sales_movement',
        'sales_movementID',
        'sales_issueDate',
        'sales_lastChange',
        'sales_concept',
        'sales_money',
        'sales_typeChange',
        'sales_customer',
        'sales_typeCondition',
        'sales_condition',
        'sales_expiration',
        'sales_reference',
        'sales_company',
        'sales_branchOffice',
        'sales_depot',
        'sales_user',
        'sales_status',
        'sales_amount',
        'sales_taxes',
        'sales_total',
        'sales_retention1',
        'sales_retentionISR',
        'sales_retention2',
        'sales_retentionIVA',
        'sales_lines',
        'sales_originType',
        'sales_origin',
        'sales_originID',
        'sales_listPrice',
        'sales_driver',
        'sales_vehicle',
        'sales_identificationCFDI',
        'sales_plates',
        'sales_placeDelivery',
        'sales_bookingNumber',
        'sales_stamp',
        'sales_departureDate',
        'sales_shipName',
        'sales_finalDestiny',
        'sales_contractNumber',
        'sales_containerType',
        'sales_ticket',
        'sales_material',
        'sales_outputWeight',
        'sales_dateTime',
        'sales_advanced',
        'sales_stamped',
    ];

    public function scopewhereSalesMovementID($query, $key)
    {
        if(!is_null($key))
            return $query->where('sales_movementID', 'like', '%'.$key.'%');
    }

    public function scopeWhereSalesCustomer($query, $key)
    {
        if (!is_null($key)) {
            if ($key === 'Todos') {
                return $query;
            } else {
                return $query->where(function ($query) use ($key) {
                    $query->where('sales_customer', '=', $key)
                        ->orWhereRaw("CONCAT(customers_name, ' ', customers_lastName, ' ', customers_lastName2) LIKE ?", ["%$key%"])->orwhere('customers_businessName', 'like', '%'.$key.'%');
                });
            }
        }
    }


    public function scopewhereSaleNameCustomer($query, $cliente, $cliente2){
        
        if(!is_null($cliente) ){
            if($cliente !== 'Todos'){
               $query->where('sales_customer', '>=', (int) $cliente);
            }
       }

         if(!is_null($cliente2) ){
                if($cliente2 !== 'Todos'){
                $query->where('sales_customer', '<=', (int) $cliente2);
                }
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

    public function scopewhereSalesMovement($query, $key)
    {
        if(!is_null($key))
            if($key === 'Todos')
                return $query;
            else
                return $query->where('sales_movement', $key);
    }

    public function scopewhereSalesStatus($query, $key)
    {
        if(!is_null($key))
            if($key === 'Todos')
                return $query;
            else
                return $query->where('sales_status', $key);
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
                        $now = Carbon::now();
                        $start    = (new DateTime($now->format('Y-m-d')))->modify('first day of this month');
                        $end      = (new DateTime($now->format('Y-m-d')))->modify('last day of this month');
    
                        $fecha_inicial = $start->format('Y-m-d');
                        $fecha_fin = $end->format('Y-m-d');
                         return $query->whereDate('PROC_SALES.sales_issuedate', '>=', $fecha_inicial)->whereDate('PROC_SALES.sales_issuedate', '<=', $fecha_fin )->OrWhereNull('PROC_SALES.sales_issuedate');
                        break;

                        case 'Año Móvil':
                            $fecha_año_actual = Carbon::now()->format('Y');
                            $fecha_inicial= $fecha_año_actual.'-01-01';
                            $fecha_final = $fecha_año_actual.'-12-31';
             
                             return $query->whereDate('PROC_SALES.sales_issuedate', '<=', $fecha_final)->whereDate('PROC_SALES.sales_issuedate', '>=', $fecha_inicial );
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

    public function scopewhereAssistantUnitYear($query, $year)
    {
        if(!is_null($year) ){
            
               $query->where('PROC_ASSISTANT_UNITS.assistantUnit_year', '=', $year);
       }

        return $query;
    }
    
    public function scopewhereSalesListPrice($query, $key)
    {
        if(!is_null($key))
            if($key === 'Todos')
                return $query;
            else
                return $query->where('sales_listPrice', $key);
    }


    public function scopewhereSalesUser($query, $key)
    {
        if(!is_null($key))
            if($key === 'Todos')
                return $query;
            else
                return $query->where('sales_user', $key);
    }

    public function scopewhereSalesBranchOffice($query, $key)
    {
        if(!is_null($key))
            if($key === 'Todos')
                return $query;
            else
                return $query->where('sales_branchOffice', $key);
    }

    public function scopewhereSaleDetailsArticle($query, $nameArticuloUno, $nameArticuloDos)
    {
        if(!is_null($nameArticuloUno) ){
            if($nameArticuloUno !== 'Todos'){
               $query->where('PROC_SALES_DETAILS.salesDetails_article', '>=', (int) $nameArticuloUno);
            }
       }

          if(!is_null($nameArticuloDos) ){
            if($nameArticuloDos !== 'Todos'){
               $query->where('PROC_SALES_DETAILS.salesDetails_article', '<=', (int) $nameArticuloDos);
            }
          }


        return $query;
    }

    public function scopewhereArticleGroup($query, $group){
        if(!is_null($group)){
            return $query->where('CAT_ARTICLES.articles_group', '=', $group);
        }
        return $query;
    }

    public function scopewhereArticleCategory($query, $category){
        if(!is_null($category)){
            return $query->where('CAT_ARTICLES.articles_category', '=', $category);
        }
        return $query;
    }

    public function scopewhereArticleFamily($query, $family){
        if(!is_null($family)){
            return $query->where('CAT_ARTICLES.articles_family', '=', $family);
        }
        return $query;
    }

    public function scopewhereAssistantUnitAccount($query, $nameArticuloUno, $nameArticuloDos)
    {
        if(!is_null($nameArticuloUno) ){
            if($nameArticuloUno !== 'Todos'){
               $query->where('PROC_ASSISTANT_UNITS.assistantUnit_account', '>=', (int) $nameArticuloUno);
            }
       }

          if(!is_null($nameArticuloDos) ){
            if($nameArticuloDos !== 'Todos'){
               $query->where('PROC_ASSISTANT_UNITS.assistantUnit_account', '<=', (int) $nameArticuloDos);
            }
          }


        return $query;
    }

    public function scopewhereSalesMoney($query, $key)
    {
        if(!is_null($key))
            if($key === 'Todos')
                return $query;
            else
                return $query->where('sales_money', $key);
    }

    public function scopewhereSalesDepot($query, $key)
    {
        if(!is_null($key))
            if($key === 'Todos')
                return $query;
            else
                return $query->where('sales_depot', $key);
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

    public function scopewhereSalesStamped($query, $key){

        if(!is_null($key)){
            if($key !== 'Todos'){
                if($key === "Si"){
                    $query->where('sales_stamped', "=",1);
                }

                if($key === "No"){
                    $query->where('sales_stamped', "=",Null);
                }
            }
        }
        return $query;
    }


    public function scopewhereSalesSeries($query, $key)
    {
        if(!is_null($key))
            if($key === 'Todos')
                return $query;
            else
                return $query->where('PROC_DEL_SERIES_MOV2.delSeriesMov2_lotSerie', $key);
    }

    public function scopewhereSalesReference($query, $key){
        if(!is_null($key))
        return $query->Where('sales_reference', 'like', '%'.$key.'%');
    }

    public function Company()
    {
        return $this->belongsTo('App\Models\catalogos\CAT_COMPANIES', 'sales_company', 'companies_key');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\catalogos\CAT_CUSTOMERS', 'sales_customer', 'customers_key');
    }
}
