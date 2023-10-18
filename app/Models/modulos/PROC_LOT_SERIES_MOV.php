<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_LOT_SERIES_MOV extends Model
{
    use HasFactory;

    protected $table = 'PROC_LOT_SERIES_MOV';
    protected $primaryKey = 'lotSeriesMov_id';

    protected $fillable = [
        'lotSeriesMov_companieKey',
        'lotSeriesMov_branchKey',
        'lotSeriesMov_module',
        'lotSeriesMov_purchaseID',
        'lotSeriesMov_article',
        'lotSeriesMov_lotSerie',
        'lotSeriesMov_quantity',
    ];
}
