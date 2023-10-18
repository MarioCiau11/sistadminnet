<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_PACKINGLIST extends Model
{
    use HasFactory;

    protected $table = 'PROC_PACKINGLIST';
    protected $primaryKey = 'packingList_id';
    public $incrementing = false;

    protected $fillable = [
        'packingList_companieKey',
        'packingList_branchKey',
        'packingList_module',
        'packingList_saleID',
        'packingList_article',
        'packingList_unidPack',
        'packingList_quantity',
        'packingList_weight',
        'packingList_weightUnid',
        'packingList_weightNet',
        'packingList_advance',
        'packingList_articleID',
        ];
}
