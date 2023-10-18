<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_ARTICLES_UNITS extends Model
{
    use HasFactory;

    protected $table = 'CAT_ARTICLES_UNITS';
    protected $primaryKey = 'articlesUnits_id';
    public $incrementing = false;


     protected $fillable = [
        'articlesUnits_article',
        'articlesUnits_unit',
        'articlesUnits_factor',
    ];
}
