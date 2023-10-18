<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_LOT_SERIES_MOV2 extends Model
{
    use HasFactory;

    protected $table = 'PROC_LOT_SERIES_MOV2';
    protected $primaryKey = 'lotSeriesMov2_id';

    protected $fillable = [
        'lotSeriesMov2_companieKey',
        'lotSeriesMov2_branchKey',
        'lotSeriesMov2_module',
        'lotSeriesMov2_purchaseID',
        'lotSeriesMov2_article',
        'lotSeriesMov2_lotSerie',
        'lotSeriesMov2_quantity',
    ];
}
