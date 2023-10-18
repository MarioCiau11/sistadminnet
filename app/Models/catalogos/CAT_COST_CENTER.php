<?php

namespace App\Models\catalogos;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_COST_CENTER extends Model
{
    use HasFactory;

    protected $table = 'CAT_COST_CENTER';
    protected $primaryKey = 'costCenter_id';

    protected $fillable = [
        'costCenter_key',
        'costCenter_name',
        'costCenter_status',
    ];

    public function scopeWhereCostCenterKey($query, $key){
        if(!is_null($key)){
            return $query->where('costCenter_key', 'like', $key.'%');
        }
        return $query;
    }

    public function scopeWhereCostCenterName($query, $name){
        if(!is_null($name)){
            return $query->where('costCenter_name', 'like', '%'.$name.'%');
        }
        return $query;
    }

    public function scopeWhereCostCenterStatus($query, $status){
        if(!is_null($status)){

            if($status == 'Todos'){
                return $query;
            }
            return $query->where('costCenter_status', $status);
        }
        return $query;
    }


    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

}
