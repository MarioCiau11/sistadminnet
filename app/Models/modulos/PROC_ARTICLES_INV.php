<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_ARTICLES_INV extends Model
{
    use HasFactory;

    protected $table = 'PROC_ARTICLES_INV';
    protected $primaryKey = 'articlesInv_id';
    
    protected $fillable = [
        'articlesInv_branchKey',
        'articlesInv_companieKey',
        'articlesInv_article',
        'articlesInv_depot',
        'articlesInv_inventory',
        ];

}
