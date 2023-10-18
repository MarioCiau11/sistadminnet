<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_EXPENSES_DETAILS extends Model
{
    use HasFactory;

    protected $table = 'PROC_EXPENSES_DETAILS';
    protected $primaryKey = 'expensesDetails_id';
    public $incrementing = false;

    protected $fillable = [
        'expensesDetails_expensesID',
        'expensesDetails_establishment',
        'expensesDetails_concept',
        'expensesDetails_reference',
        'expensesDetails_quantity',
        'expensesDetails_price',
        'expensesDetails_amount',
        'expensesDetails_vat',
        'expensesDetails_vatAmount',
        'expensesDetails_retention1',
        'expensesDetails_retentionISR',
        'expensesDetails_retention2',
        'expensesDetails_retentionIVA',
        'expensesDetails_total',
        'expensesDetails_branchOffice',
        'expensesDetails_depot',
    ];
}
