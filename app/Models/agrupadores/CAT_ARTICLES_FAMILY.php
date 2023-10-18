<?php

namespace App\Models\agrupadores;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_ARTICLES_FAMILY extends Model
{
    use HasFactory;

    protected $table = 'CAT_ARTICLES_FAMILY';

    protected $primaryKey = 'familyArticle_id';

    protected $fillable = [
        'familyArticle_name',
        'familyArticle_status',
    ];
}
