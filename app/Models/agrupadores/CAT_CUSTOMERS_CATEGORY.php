<?php

namespace App\Models\agrupadores;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_CUSTOMERS_CATEGORY extends Model
{
    use HasFactory;

    protected $table = 'CAT_COSTUMERS_CATEGORY';
    protected $primaryKey = 'categoryCostumer_id';

    protected $fillable = [
        'categoryCostumer_name',
        'categoryCostumer_status',
    ];
}
