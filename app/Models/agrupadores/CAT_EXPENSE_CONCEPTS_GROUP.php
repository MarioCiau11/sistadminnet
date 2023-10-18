<?php

namespace App\Models\agrupadores;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_EXPENSE_CONCEPTS_GROUP extends Model
{
    use HasFactory;

    protected $table = 'CAT_EXPENSE_CONCEPTS_GROUP';

    protected $primaryKey = 'groupExpenseConcept_id';

    public $incrementing = false;

    protected $fillable = [
        'groupExpenseConcept_name',
        'groupExpenseConcept_status',
    ];


}
