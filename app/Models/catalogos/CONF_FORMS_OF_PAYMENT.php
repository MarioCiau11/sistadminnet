<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;


class CONF_FORMS_OF_PAYMENT extends Model
{
    use HasFactory;

    protected $table = 'CONF_FORMS_OF_PAYMENT';
    protected $primaryKey = 'formsPayment_key';
    public $incrementing = false;

    protected $fillable = [
        'formsPayment_key',
        'formsPayment_name',
        'formsPayment_descript',
        'formsPayment_money',
        'formsPayment_sat',
        'formsPayment_status',
    ];
    //validamos la request para hacer el añadir el where en la consulta
    public function scopewhereFormsPaymentKey($query, $key)
    {
        if(!is_null($key)){
            return $query->where('formsPayment_key', 'like', $key.'%');
        }
        return $query;
    }

    public function scopewhereFormsPaymentName($query, $name)
    {
        if(!is_null($name)){
            return $query->where('formsPayment_name', 'like', '%'.$name.'%');
        }
        return $query;
    }

    public function scopewhereFormsPaymentStatus($query, $status)
    {
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }
            
            return $query->where('formsPayment_status', '=', $status);
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
