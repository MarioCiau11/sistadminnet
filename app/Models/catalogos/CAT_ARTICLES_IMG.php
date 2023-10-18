<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_ARTICLES_IMG extends Model
{
    use HasFactory;

    protected $table = 'CAT_ARTICLES_IMG';
    protected $primaryKey = 'articlesImg_id';
    public $incrementing = false;

    protected $fillable = [
        'articlesImg_article',
        'articles_type',
        'articlesImg_path',
        'articlesImg_file',
    ];
}