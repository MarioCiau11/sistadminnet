<?php

namespace App\Models\agrupadores;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_AGENTS_CATEGORY extends Model
{
    use HasFactory;

    protected $table = 'CAT_AGENTS_CATEGORY';
    protected $primaryKey = 'categoryAgents_id';
    public $incrementing = false;

    protected $fillable = [
        'categoryAgents_name',
        'categoryAgents_status',
    ];
}
