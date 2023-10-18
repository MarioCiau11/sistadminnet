<?php

namespace App\Models\catalogos;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_VEHICLES extends Model
{
    use HasFactory;

    protected $table = 'CAT_VEHICLES';
    protected $primaryKey = 'vehicles_key';

    protected $fillable = [
        'vehicles_name',
        'vehicles_plates',
        'vehicles_capacityVolume',
        'vehicles_capacityWeight',
        'vehicles_defaultAgent',
        'vehicles_branchOffice',
        'vehicles_status',
    ];

    public function scopewhereVehicleKey($query, $key){
        if(!is_null($key)){
           return $query->where('vehicles_key', 'like', $key.'%');
        }
        return $query;
    }
    
       public function scopewhereVehicleName($query, $name){
        if(!is_null($name)){
           return $query->where('vehicles_name', 'like', '%'.$name.'%');
        }
        return $query;
    }

    public function scopewhereVehicleStatus($query, $status){
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }

            return $query->where('vehicles_status', '=', $status);
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
