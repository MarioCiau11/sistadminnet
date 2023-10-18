<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CAT_BRANCH_OFFICES extends Model
{
    use HasFactory;

    protected $table = 'CAT_BRANCH_OFFICES';
    protected $primaryKey = 'branchOffices_key';
    public $incrementing = false;
 
    protected $fillable = [
        'branchOffices_key',
        'branchOffices_companyId',
        'branchOffices_name',
        'branchOffices_status',
        'branchOffices_addres',
        'branchOffices_suburb',
        'branchOffices_cp',
        'branchOffices_city',
        'branchOffices_country',
        'branchOffices_state',
    ];

       //validamos la request para hacer añadir el where en la consulta
     public function scopewherebranchOfficesKey($query, $key){
        if(!is_null($key)){
           return $query->where('branchOffices_key', 'like', $key.'%');
        }
        return $query;
    }
    
       public function scopewherebranchOfficesName($query, $name){
        if(!is_null($name)){
           return $query->where('branchOffices_name', 'like', '%'.$name.'%');
        }
        return $query;
    }

    public function scopewherebranchOfficesStatus($query, $status){
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }
            
            return $query->where('branchOffices_status', '=', $status);
        }
        return $query;
    }

     public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function setDateAttribute( $value ) {
      $this->attributes['dateTime'] = (new Carbon($value))->format('d-m-y');
    }
  

   
}
