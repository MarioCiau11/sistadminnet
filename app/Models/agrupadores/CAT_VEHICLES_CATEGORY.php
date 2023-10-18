<?php

namespace App\Models\agrupadores;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_VEHICLES_CATEGORY extends Model
{
    use HasFactory;

    protected $table = 'CAT_VEHICLES_CATEGORY';
    protected $primaryKey = 'categoryVehicle_id';
    public $incrementing = false;

    protected $fillable = [
        'categoryVehicle_name',
        'categoryVehicle_status',
    ];
}
