<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_TREASURY_DETAILS extends Model
{
    use HasFactory;
    protected $table = 'PROC_TREASURY_DETAILS';
    protected $primaryKey = 'treasuriesDetails_id';
    public $incrementing = false;

    protected $fillable = [
        'treasuriesDetails_id',
        'treasuriesDetails_treasuryID',
        'treasuriesDetails_apply',
        'treasuriesDetails_applyIncrement',
        'treasuriesDetails_amount',
        'treasuriesDetails_paymentMethod',
        'treasuriesDetails_movReference',
    ];
}
