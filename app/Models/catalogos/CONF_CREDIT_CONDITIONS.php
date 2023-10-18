<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CONF_CREDIT_CONDITIONS extends Model
{
    use HasFactory;
    protected $table = 'CONF_CREDIT_CONDITIONS';
    protected $primaryKey = 'creditConditions_id';
    protected $autoIncrement = true;

      protected $fillable = [
        'creditConditions_name',
        'creditConditions_type',
        'creditConditions_days',
        'creditConditions_typeDays',
        'creditConditions_workDays',
        'creditConditions_paymentMethod',
        'creditConditions_status',
    ];

    //validamos la request para hacer añadir el where en la consulta 
       public function scopewhereConditionName($query, $ConditionName){
        if(!is_null($ConditionName)){
           return $query->where('creditConditions_name', 'like', '%'.$ConditionName.'%');
        }
        return $query;
    }

    public function scopewhereStatus($query, $status){
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }
            
            return $query->where('creditConditions_status', '=', $status);
        }
        return $query;
    }

     public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y');
    }
}
