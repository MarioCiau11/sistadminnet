<?php

namespace App\Models\agrupadores;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_ARTICLES_GROUP extends Model
{
    use HasFactory;

    protected $table = 'CAT_ARTICLES_GROUP';
    protected $primaryKey = 'groupArticle_id';
    public $incrementing = false;

    protected $fillable = [
        'groupArticle_name',
        'groupArticle_status',
    ];
}
