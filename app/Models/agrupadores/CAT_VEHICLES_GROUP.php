<?php

namespace App\Models\agrupadores;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_VEHICLES_GROUP extends Model
{
    use HasFactory;

    protected $table = 'CAT_VEHICLES_GROUP';
    protected $primaryKey = 'groupVehicle_id';
    public $incrementing = false;

    protected $fillable = [
        'groupVehicle_name',
        'groupVehicle_status',
    ];
}
