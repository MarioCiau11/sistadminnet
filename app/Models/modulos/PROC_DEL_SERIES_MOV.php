<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_DEL_SERIES_MOV extends Model
{
    use HasFactory;

    protected $table = 'PROC_DEL_SERIES_MOV';
    protected $primaryKey = 'delSeriesMov_id';

    protected $fillable = [
        'delSeriesMov_companieKey',
        'delSeriesMov_branchKey',
        'delSeriesMov_module',
        'delSeriesMov_inventoryID',
        'delSeriesMov_article',
        'delSeriesMov_lotSerie',
        'delSeriesMov_quantity',
        'delSeriesMov_articleID',
        'delSeriesMov_cancelled',
    ];
}
