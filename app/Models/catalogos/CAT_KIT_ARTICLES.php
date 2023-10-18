<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_KIT_ARTICLES extends Model
{
    use HasFactory;

    protected $table = 'CAT_ARTICLES_KIT';
    protected $primaryKey = 'kitArticles_id';

    protected $fillable = [
        'kitArticles_article',
        'kitArticles_articleID',
        'kitArticles_articleDesp',
        'kitArticles_tipo',
        'kitArticles_costo',
        'kitArticles_cantidad',
    ];
}
