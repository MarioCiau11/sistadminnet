<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_DEL_SERIES_MOV2 extends Model
{
    use HasFactory;
    protected $table = 'PROC_DEL_SERIES_MOV2';
    protected $primaryKey = 'delSeriesMov2_id';

    protected $fillable = [
        'delSeriesMov2_companieKey',
        'delSeriesMov2_branchKey',
        'delSeriesMov2_module',
        'delSeriesMov2_saleID',
        'delSeriesMov2_article',
        'delSeriesMov2_lotSerie',
        'delSeriesMov2_quantity',
        'delSeriesMov2_articleID',
        'delSeriesMov2_cancelled',
    ];

    public function scopewhereSalesSeries($query, $key)
    {
        if (!is_null($key))
            if ($key === 'Todos')
            return $query;
            else
                return $query->where('PROC_DEL_SERIES_MOV2.delSeriesMov2_lotSerie', $key);
    }

    public function scopewhereSalesBranchOffice($query, $key)
    {
        if (!is_null($key))
            if ($key === 'Todos')
            return $query;
            else
                return $query->where('PROC_DEL_SERIES_MOV2.delSeriesMov2_branchKey', $key);
    }
    
}
