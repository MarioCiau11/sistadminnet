<?php

namespace App\Models\agrupadores;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_ARTICLES_LIST extends Model
{
    use HasFactory;

    protected $table = 'CAT_ARTICLES_LIST';
    protected $primaryKey = 'articlesList_id';

    protected $fillable = [
        'articlesList_article',
        'articlesList_listID',
        'articlesList_nameArticle',
        'articlesList_provider',
        'articlesList_lastCost',
        'articlesList_lastPurchase',
        'articlesList_averageCost',
    ];
}
