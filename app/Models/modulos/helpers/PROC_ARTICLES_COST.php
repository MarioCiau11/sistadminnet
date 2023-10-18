<?php

namespace App\Models\modulos\helpers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_ARTICLES_COST extends Model
{
    use HasFactory;

    protected $table = 'PROC_ARTICLES_COST';
    protected $primaryKey = 'articlesCost_id';
    public $timestamps = false;

    protected $fillable = [
        'articlesCost_companieKey',
        'articlesCost_branchKey',
        'articlesCost_depotKey',
        'articlesCost_article',
        'articlesCost_averageCost',
        'articlesCost_lastCost',
    ];
}
