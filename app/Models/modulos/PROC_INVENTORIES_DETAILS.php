<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_INVENTORIES_DETAILS extends Model
{
    use HasFactory;

    protected $table = 'PROC_INVENTORIES_DETAILS';
    protected $primaryKey = 'inventoryDetails_id';


    protected $fillable = [
        'inventoryDetails_inventoryID',
        'inventoryDetails_article',
        'inventoryDetails_descript',
        'inventoryDetails_quantity',
        'inventoryDetails_unitCost',
        'inventoryDetails_unit',
        'inventoryDetails_inventoryAmount',
        'inventoryDetails_amount',
        'inventoryDetails_ivaPorcent',
        'inventoryDetails_total',
        'inventoryDetails_apply',
        'inventoryDetails_applyIncrement',
        'inventoryDetails_branchOffice',
        'inventoryDetails_depot',
        'inventoryDetails_outstandingAmount',
        'inventoryDetail_canceledAmount',
        'inventoryDetail_referenceArticles',
    ];
}
