<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CONF_MODULES_CONCEPT extends Model
{
    use HasFactory;

    protected $table = 'CONF_MODULES_CONCEPT';
    protected $primaryKey = 'moduleConcept_id';
    public $incrementing = true;

    protected $fillable = [
        'moduleConcept_name',
        'moduleConcept_module',
        'moduleConcept_prodServ',
        'moduleConcept_status'
    ];

    public function scopewhereConceptName($query, $ConceptName)
    {
        if(!is_null($ConceptName)){
            return $query ->where('moduleConcept_name', 'like', '%'.$ConceptName.'%');
        }
        return $query;
    }

    public function scopewhereConceptModule($query, $module)
    {
        if(!is_null($module)){
                
                if($module === 'Todos'){
                    return $query;
                }
                
                return $query ->where('moduleConcept_module', '=', $module);
                dd($query);
            }
            return $query;
    }

    public function scopewhereStatus($query, $status)
    {
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }
            
            return $query ->where('moduleConcept_status', '=', $status);
        }
        return $query;
    }

    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y');
    }

    //relacionamos CONF_MODULES_CONCEPT CON CONF_MODULES_CONCEPT_MOVEMENT
    public function movimientos()
    {
        return $this->belongsTo('App\Models\catalogos\CONF_MODULES_CONCEPT_MOVEMENT', 'moduleConcept_id', 'moduleMovement_conceptID');
        // dd($this->movimientos());
    }

}
