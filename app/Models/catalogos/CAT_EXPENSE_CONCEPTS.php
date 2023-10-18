<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_EXPENSE_CONCEPTS extends Model
{
    use HasFactory;

    protected $table = 'CAT_EXPENSE_CONCEPTS';
    protected $primaryKey = 'expenseConcepts_id';

    protected $fillable = [
        'expenseConcepts_concept',
        'expenseConcepts_tax',
        'expenseConcepts_retention',
        'expenseConcepts_retention2',
        'expenseConcepts_retention3',
        'expenseConcepts_exemptIVA',
        'expenseConcepts_group',
        'expenseConcepts_category',
        'expenseConcepts_status',
    ];

    public function scopewhereExpenseConceptsConcept($query, $concept){
        if(!is_null($concept)){
            return $query->where('expenseConcepts_concept', 'like', '%'.$concept.'%');
        }
        return $query;
    }

    public function scopewhereExpenseConceptsGroup($query, $group){
        if(!is_null($group)){
            return $query->where('expenseConcepts_group', '=', $group);
        }
        return $query;
    }

    public function scopewhereExpenseConceptsCategory($query, $category){
        if(!is_null($category)){
            return $query->where('expenseConcepts_category', '=', $category);
        }
        return $query;
    }

    public function scopewhereExpenseConceptsStatus($query, $status){
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }

            return $query->where('expenseConcepts_status', '=', $status);
        }
        return $query;
    }


}
