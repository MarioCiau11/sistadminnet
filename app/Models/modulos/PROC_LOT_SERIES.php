<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_LOT_SERIES extends Model
{
    use HasFactory;

    protected $table = 'PROC_LOT_SERIES';
    protected $primaryKey = 'lotSeries_id';

    protected $fillable = [
        'lotSeries_companieKey',
        'lotSeries_branchKey',
        'lotSeries_article',
        'lotSeries_lotSerie',
        'lotSeries_depot',
        'lotSeriesMov_existenceAlt',
        'lotSeriesMov_existenceFixedAsset',
        'lotSeries_delete',
    ];
}
