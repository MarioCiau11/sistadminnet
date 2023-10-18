<?php

namespace App\Models\agrupadores;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_PROVIDER_CATEGORY extends Model
{
    use HasFactory;

    protected $table = 'CAT_PROVIDER_CATEGORY';
    protected $primaryKey = 'categoryProvider_id';

    protected $fillable = [
        'categoryProvider_name',
        'categoryProvider_status',
    ];
}
