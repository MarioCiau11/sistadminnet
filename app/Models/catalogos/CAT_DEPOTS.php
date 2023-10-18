<?php

namespace App\Models\catalogos;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_DEPOTS extends Model
{
    use HasFactory;

    protected $table = 'CAT_DEPOTS';
    protected $primaryKey = 'depots_key';
    public $incrementing = false;

    protected $fillable = [
        'depots_key',
        'depots_branchIId',
        'depots_name',
        'depots_type',
        'depots_status',
    ];

    public function scopewhereDepotsKey($query, $key){
        if(!is_null($key)){
           return $query->where('depots_key', 'like', $key.'%');
        }
        return $query;
    }
    
       public function scopewhereDepotsName($query, $name){
        if(!is_null($name)){
           return $query->where('depots_name', 'like', '%'.$name.'%');
        }
        return $query;
    }

    public function scopewhereDepotsStatus($query, $status){
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }

            return $query->where('depots_status', '=', $status);
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
