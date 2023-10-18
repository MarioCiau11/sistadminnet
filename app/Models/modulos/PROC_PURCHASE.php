<?php

namespace App\Models\modulos;

use DateTime;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_PURCHASE extends Model
{
    use HasFactory;

    protected $table = 'PROC_PURCHASE';
    protected $primaryKey = 'purchase_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'purchase_movement',
        'purchase_movementID',
        'purchase_issueDate',
        'purchase_lastChange',
        'purchase_concept',
        'purchase_money',
        'purchase_typeChange',
        'purchase_provider',
        'purchase_condition',
        'purchase_expiration',
        'purchase_reference',
        'purchase_company',
        'purchase_branchOffice',
        'purchase_depot',
        'purchase_depotType',
        'purchase_user',
        'purchase_status',
        'purchase_listPriceProvider',
        'purchase_amount',
        'purchase_taxes',
        'purchase_total',
        'purchase_lines',
        'purchase_originType',
        'purchase_origin',
        'purchase_originID',
        'purchase_ticket',
        'purchase_operator',
        'purchase_plates',
        'purchase_material',
        'purchase_inputWeight',
        'purchase_inputDateTime',
        'purchase_outputWeight',
        'purchase_outputDateTime',
        ];
    

        public function scopewherePurchaseMovementID($query, $key){
            if(!is_null($key)){
                return $query->where('PROC_PURCHASE.purchase_movementID', '=', $key);
            }
            return $query;
        }

        

        public function scopewherePurchaseProvider($query, $key){

            if(!is_null($key)){
                
                if($key !== 'Todos'){
                    if(ctype_digit($key)){
                        $query->where('PROC_PURCHASE.purchase_provider', '=', (int) $key);
                        // dd($query);
                    }else{
                        $query->Where('CAT_PROVIDERS.providers_name', 'LIKE', '%'.$key.'%');
                    }
                }
            }
            return $query;
        }

        public function scopewherePurchaseNameProvider($query, $proveedor, $proveedor2){
        
            if(!is_null($proveedor) ){
                 if($proveedor !== 'Todos'){
                    $query->where('purchase_provider', '>=', (int) $proveedor);
                 }
            }

            if(!is_null($proveedor2) ){
                 if($proveedor2 !== 'Todos'){
                    $query->where('purchase_provider', '<=', (int) $proveedor2);
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
        

        public function scopewherePurchaseMovement($query, $key){
            if(!is_null($key)){
                if($key === 'Todos'){
                    return $query;
            }
            return $query->where('PROC_PURCHASE.purchase_movement', '=', $key);

        } 
        return $query;
        
            
        }

        public function scopewherePurchaseStatus($query, $status){
            if(!is_null($status)){
                if($status === 'Todos'){
                    return $query;
                }
                return $query->where('PROC_PURCHASE.purchase_status', '=', $status);
            }
            return $query;
        }

        public function scopewherePurchaseDate($query, $key){
            if(!is_null($key)){
                switch ($key) {
                    case 'Hoy':
                        $fecha_actual = Carbon::now()->format('Y-m-d');
                         return $query->whereDate('PROC_PURCHASE.purchase_issueDate', '=', $fecha_actual);
                        break;
                    case 'Ayer':
                        $fecha_ayer = new Carbon('yesterday');
                         return $query->whereDate('PROC_PURCHASE.purchase_issueDate', '=', $fecha_ayer);
                        break;

                    case 'Semana':
                        $fecha_actual = Carbon::now()->format('Y-m-d');
                        $fecha_semana = new Carbon('last week');
                        $fecha_formato = $fecha_semana->format('Y-m-d');
                         return $query->whereDate('PROC_PURCHASE.purchase_issueDate', '<=', $fecha_actual)->whereDate('PROC_PURCHASE.purchase_issueDate', '>=', $fecha_formato );
                        break;

                    case 'Mes':
                        $now = Carbon::now();
                        $start    = (new DateTime($now->format('Y-m-d')))->modify('first day of this month');
                        $end      = (new DateTime($now->format('Y-m-d')))->modify('last day of this month');
    
                        $fecha_inicial = $start->format('Y-m-d');
                        $fecha_fin = $end->format('Y-m-d');
                    
                        
                        return $query->whereDate('PROC_PURCHASE.purchase_issueDate', '>=', $fecha_inicial)->whereDate('PROC_PURCHASE.purchase_issueDate', '<=', $fecha_fin );
                        break;

                    case 'Año Móvil':
                         $fecha_año_actual = Carbon::now()->format('Y');
                        $fecha_inicial= $fecha_año_actual.'-01-01';
                        $fecha_final = $fecha_año_actual.'-12-31';
        
                        return $query->whereDate('PROC_PURCHASE.purchase_issueDate', '<=', $fecha_final)->whereDate('PROC_PURCHASE.purchase_issueDate', '>=', $fecha_inicial );
                        break;

                    case 'Año Pasado':
                        $fecha_año_inicioMes_pasado = new Carbon('last year');
                        $formato_fecha_inicioMes_pasado = $fecha_año_inicioMes_pasado->format('Y');
                        $inicoAñoPasado = $formato_fecha_inicioMes_pasado.'-01-01';
                        $finAñoPasado = $formato_fecha_inicioMes_pasado.'-12-31';

                        return $query->whereDate('PROC_PURCHASE.purchase_issueDate', '>=', $inicoAñoPasado )->whereDate('PROC_PURCHASE.purchase_issueDate', '<=', $finAñoPasado );

                        break;
                    default:
                    $fechasRangoArray = explode('+', $key);
                    $fechaInicio = $fechasRangoArray[0];
                    $fechaFinal = $fechasRangoArray[1];
                    $fechaInicio = Carbon::parse($fechaInicio)->format('Y-m-d');
                    $fechaFinal = Carbon::parse($fechaFinal)->format('Y-m-d');
                    return $query->whereDate('purchase_issueDate', '>=', $fechaInicio)->whereDate('purchase_issueDate', '<=', $fechaFinal);
                        break;
                }
               
            }
            return $query;
        }

        

        public function scopewherePurchaseUser($query, $key){
            if(!is_null($key) && $key != 'Todos'){
                return $query->where('PROC_PURCHASE.purchase_user', '=', $key);
            }
            return $query;
        }

        public function scopewherePurchasebranchOffice($query, $key){
            if(!is_null($key) && $key != 'Todos'){
                return $query->where('PROC_PURCHASE.purchase_branchOffice', '=', $key);
            }
            return $query;
        }

        public function scopewherePurchaseMoney($query, $key){
            if(!is_null($key)){
                if($key === 'Todos'){
                    return $query;
                }
                return $query->where('PROC_PURCHASE.purchase_money', '=', $key);
            }
            return $query;
        }

        public function scopewherePurchaseDepot($query, $key){
            if(!is_null($key)){
                if($key === 'Todos'){
                    return $query;
                }
                
                return $query->where('PROC_PURCHASE.purchase_depot', '=', $key);
            }
            return $query;
        }

        public function scopewherePurchaseUnit($query, $key){
            if(!is_null($key)){
                if($key === 'Todos'){
                    return $query;
                }
                return $query->where('PROC_PURCHASE_DETAILS.purchaseDetails_unit', '=', $key);
            }
            return $query;
        }

        public function scopewherePurchaseArticle($query, $key)
        {
            if(!is_null($key)){
                if($key === 'Todos'){
                    return $query;
                }
                return $query->where('PROC_PURCHASE_DETAILS.purchaseDetails_article', '=', $key);
            }
            return $query;
        }

        public function scopewherePurchaseDetailsArticle($query, $nameArticuloUno, $nameArticuloDos)
        {
            if(!is_null($nameArticuloUno) ){
                if($nameArticuloUno !== 'Todos'){
                   $query->where('PROC_PURCHASE_DETAILS.purchaseDetails_article', '>=', (int) $nameArticuloUno);
                }
           }

              if(!is_null($nameArticuloDos) ){
                if($nameArticuloDos !== 'Todos'){
                   $query->where('PROC_PURCHASE_DETAILS.purchaseDetails_article', '<=', (int) $nameArticuloDos);
                }
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

        public function scopewhereAssistantUnitYear($query, $year)
        {
            if(!is_null($year) ){
                
                   $query->where('PROC_ASSISTANT_UNITS.assistantUnit_year', '=', $year);
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
                return $query->where('CAT_ARTICLES.articles_category', '=', $category)->orwhere('CAT_ARTICLES.articles_category', '=', null);
            }
            return $query;
        }
    
        public function scopewhereArticleFamily($query, $family){
            if(!is_null($family)){
                return $query->where('CAT_ARTICLES.articles_family', '=', $family);
            }
            return $query;
        }

        public function getPurchaseIssuedateAttribute($value)
        {
            return Carbon::parse($value)->format('d-m-Y');
        }

        public function scopewherePurchaseSeries($query, $key)
        {
            if(!is_null($key))
                if($key === 'Todos')
                    return $query;
                else
                    return $query->where('PROC_LOT_SERIES_MOV.lotSeriesMov_lotSerie', $key);
        }

        

    
    

}
