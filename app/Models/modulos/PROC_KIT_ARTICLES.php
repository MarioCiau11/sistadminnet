<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_KIT_ARTICLES extends Model
{
    use HasFactory;

    protected $table = 'PROC_KIT_ARTICLES';
    protected $primaryKey = 'procKit_id';
    public $incrementing = false;

    protected $fillable = [
        'procKit_article',
        'procKit_articleID',
        'procKit_saleID',
        'procKit_articleDesp',
        'procKit_tipo',
        'procKit_cantidad',
        'procKit_articleIDReference',
        ];


}
