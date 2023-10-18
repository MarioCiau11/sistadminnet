<?php

namespace App\Models\agrupadores;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_PROVIDER_GROUP extends Model
{
    use HasFactory;

    protected $table = 'CAT_PROVIDER_GROUP';
    protected $primaryKey = 'groupProvider_id';
    public $incrementing = false;

    protected $fillable = [
        'groupProvider_name',
        'groupProvider_status',
    ];
}
