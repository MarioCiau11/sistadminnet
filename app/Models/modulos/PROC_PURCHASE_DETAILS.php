<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_PURCHASE_DETAILS extends Model
{
    use HasFactory;

    protected $table = 'PROC_PURCHASE_DETAILS';
    protected $primaryKey = 'purchaseDetails_id';
    public $incrementing = false;

    protected $fillable = [
        'purchaseDetails_purchaseID',
        'purchaseDetails_article',
        'purchaseDetails_descript',
        'purchaseDetails_quantity',
        'purchaseDetails_unitCost',
        'purchaseDetails_unit',
        'purchaseDetails_factor',
        'purchaseDetails_inventoryAmount',
        'purchaseDetails_amount',
        'purchaseDetails_ivaPorcent',
        'purchaseDetails_total',
        'purchaseDetails_apply',
        'purchaseDetails_applyIncrement',
        'purchaseDetails_branchOffice',
        'purchaseDetails_depot',
        'purchaseDetails_outstandingAmount',
        'purchaseDetails_canceledAmount',
        'purchaseDetails_referenceArticles',
    ];
}
