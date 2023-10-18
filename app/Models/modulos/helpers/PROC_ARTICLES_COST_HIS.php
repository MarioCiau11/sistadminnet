<?php

namespace App\Models\modulos\helpers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_ARTICLES_COST_HIS extends Model
{
    use HasFactory;

    protected $table = 'PROC_ARTICLES_COST_HIS';
    protected $primaryKey = 'articlesCostHis_id';
    public $timestamps = false;

    protected $fillable = [
        'articlesCostHis_companieKey',
        'articlesCostHis_branchKey',
        'articlesCostHis_depotKey',
        'articlesCostHis_article',
        'articlesCostHis_lastCost',
        'articlesCostHis_currentCost',
        'articlesCostHis_averageCost',
        'created_at',
    ];
}
