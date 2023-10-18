<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;


class CAT_FINANCIAL_INSTITUTIONS extends Model
{
    use HasFactory;

    protected $table = 'CAT_FINANCIAL_INSTITUTIONS';
    protected $primaryKey = 'instFinancial_key';
    public $incrementing = false;

    protected $fillable = [
        'instFinancial_key',
        'instFinancial_name',
        'instFinancial_city',
        'instFinancial_state',
        'instFinancial_country',
        'instFinancial_status',
    ];

    public function scopewhereInstFinancialKey($query, $key){
        if(!is_null($key)){
           return $query->where('instFinancial_key', 'like', $key.'%');
        }
        return $query;
    }
    
       public function scopewhereInsFinancialName($query, $name){
        if(!is_null($name)){
           return $query->where('instFinancial_name', 'like', '%'.$name.'%');
        }
        return $query;
    }

    public function scopewhereInsFinancialStatus($query, $status){
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }

            return $query->where('instFinancial_status', '=', $status);
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
