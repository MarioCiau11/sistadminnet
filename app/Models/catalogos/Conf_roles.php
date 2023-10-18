<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CONF_ROLES extends Model
{
    use HasFactory;
    protected $table = 'roles';
    public $autoincrement = true;

    protected $fillable = [
        'name',
        'descript',
        'status',
        'identifier',
    ];

      public function scopewhereRolName($query, $name){
        if(!is_null($name)){
           return $query->where('name', 'like', '%'.$name.'%');
        }
        return $query;
    }

    public function scopewhereRolStatus($query, $status){
        if(!is_null($status)){
            if($status === 'Todos'){
                return $query;
            }
            return $query->where('status', '=', $status);
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
