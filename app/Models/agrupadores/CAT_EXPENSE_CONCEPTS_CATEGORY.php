<?php

namespace App\Models\agrupadores;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_EXPENSE_CONCEPTS_CATEGORY extends Model
{
    use HasFactory;

    protected $table = 'CAT_EXPENSE_CONCEPTS_CATEGORY';

    protected $primaryKey = 'categoryExpenseConcept_id';

    protected $fillable = [
        'categoryExpenseConcept_name',
        'categoryExpenseConcept_status',
    ];
}
