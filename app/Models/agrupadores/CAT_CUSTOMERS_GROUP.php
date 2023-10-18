<?php

namespace App\Models\agrupadores;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_CUSTOMERS_GROUP extends Model
{
    use HasFactory;

    protected $table = 'CAT_COSTUMERS_GROUP';
    protected $primaryKey = 'groupCustomer_id';
    public $incrementing = false;

    protected $fillable = [
        'groupCustomer_name',
        'groupCostumer_status',
    ];
}
