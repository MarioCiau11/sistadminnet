<?php

namespace App\Models\agrupadores;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_AGENTS_GROUP extends Model
{
    use HasFactory;

    protected $table = 'CAT_AGENTS_GROUP';
    protected $primaryKey = 'groupAgents_id';
    public $incrementing = false;

    protected $fillable = [
        'groupAgents_name',
        'groupAgents_status',
    ];
}
