<?php

namespace App\Models\catalogos;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CONF_UNITS extends Model
{
    use HasFactory;

    protected $table = 'CONF_UNITS';
    protected $primaryKey = 'units_id';
    

    protected $fillable = [
        'units_unit',
        'units_decimalVal',
        'units_keySat',
        'units_status',
    ];

    public function ScopewhereUnit($query, $Unit){
        
        if(!is_null($Unit)){
            return $query->where('units_unit', 'like', '%'.$Unit.'%');
        }
        return $query;
    }

    public function ScopewhereStatus($query, $status){
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }
            
            return $query->where('units_status', '=', $status);
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
