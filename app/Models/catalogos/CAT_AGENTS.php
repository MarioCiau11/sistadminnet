<?php

namespace App\Models\catalogos;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_AGENTS extends Model
{
    use HasFactory;

    protected $table = 'CAT_AGENTS';
    protected $primaryKey = 'agents_key';

    protected $fillable = [
        'agents_name',
        'agents_type',
        'agents_category',
        'agents_group',
        'agents_branchOffice',
        'agents_status',
    ];
    public function getActiveSellers()
    {
        return $this->where('agents_type', '=', 'Vendedor')
        ->where('agents_status', '=', 'Alta')
        ->where('agents_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->get();
    }
    
    public function scopewhereAgentKey($query, $key){
        if(!is_null($key)){
           return $query->where('agents_key', 'like', $key.'%');
        }
        return $query;
    }
    
       public function scopewhereAgentName($query, $name){
        if(!is_null($name)){
           return $query->where('agents_name', 'like', '%'.$name.'%');
        }
        return $query;
    }

    public function scopewhereAgentStatus($query, $status){
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }

            return $query->where('agents_status', '=', $status);
        }
        return $query;
    }

    public function scopewhereAgentGroup($query, $group){
        if(!is_null($group)){
            return $query->where('agents_group', '=', $group);
        }
        return $query;
    }

    public function scopewhereAgentCategory($query, $category){
        if(!is_null($category)){
            return $query->where('agents_category', '=', $category);
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
