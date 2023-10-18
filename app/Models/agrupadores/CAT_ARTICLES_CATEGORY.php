<?php

namespace App\Models\agrupadores;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_ARTICLES_CATEGORY extends Model
{
    use HasFactory;

    protected $table = 'CAT_ARTICLES_CATEGORY';

    protected $primaryKey = 'categoryArticle_id';

    protected $fillable = [
        'categoryArticle_name',
        'categoryArticle_status',
    ];
}
