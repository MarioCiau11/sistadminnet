<?php

namespace App\Models\historicos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HIST_ARTICLES_PRICES extends Model
{
    use HasFactory;

    protected $table = 'HIST_ARTICLES_PRICES';
    protected $primaryKey = 'histArticlesPrices_key';
    public $incrementing = false;
    public $timestamps = false;


    protected $fillable = [
        'histArticlesPrices_article',
        'histArticlesPrices_listPrice',
        'histArticlesPrices_dateChange',
        'histArticlesPrices_previousPrice',
        'histArticlesPrices_newPrice',
    ];
    
}
