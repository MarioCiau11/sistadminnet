<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;


class CONF_MONEY extends Model
{
    use HasFactory;

    protected $table = 'CONF_MONEY';
    protected $primaryKey = 'money_key';
    public $incrementing = false;

     protected $fillable = [
        'money_key',
        'money_key',
        'money_change',
        'money_descript',
        'money_keySat',
        'money_status',
    ];

       //validamos la request para hacer añadir el where en la consulta
     public function scopewhereMonedasKey($query, $key){
        if(!is_null($key)){
           return $query->where('money_key', 'LIKE', $key.'%');
        }
        return $query;
    }
    
       public function scopewhereMonedasName($query, $name){
        if(!is_null($name)){
           return $query->where('money_name', 'LIKE', '%'.$name.'%');
        }
        return $query;
    }

    public function scopewhereMonedasStatus($query, $status){
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }
            
            return $query->where('money_status', '=', $status);
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
