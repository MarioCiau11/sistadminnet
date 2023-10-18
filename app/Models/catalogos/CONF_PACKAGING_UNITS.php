<?php

namespace App\Models\catalogos;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CONF_PACKAGING_UNITS extends Model
{
    use HasFactory;

    protected $table = 'CONF_PACKAGING_UNITS';
    protected $primaryKey = 'packaging_units_id';

    protected $fillable = [
        'packaging_units_packaging',
        'packaging_units_weight',
        'packaging_units_unit',
        'packaging_units_status',
    ];


    public function scopewhereUnitPackaging($query, $UnitPackaging){
        
        if(!is_null($UnitPackaging)){
            return $query->where('packaging_units_packaging', 'like', '%'.$UnitPackaging.'%');
        }
        return $query;
    }

    public function scopewhereStatus($query, $status){
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }
            
            return $query->where('packaging_units_status', '=', $status);
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
