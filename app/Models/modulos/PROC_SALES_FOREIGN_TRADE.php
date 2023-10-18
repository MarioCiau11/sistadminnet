<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_SALES_FOREIGN_TRADE extends Model
{
    use HasFactory;

    protected $table = 'PROC_SALES_FOREIGN_TRADE';
    protected $primaryKey = 'salesForeingTrade_id';
    public $incrementing = false;

    protected $fillable = [
        'salesForeingTrade_saleID',
        'salesForeingTrade_transferReason',
        'salesForeingTrade_operationType',
        'salesForeingTrade_petitionKey',
        'salesForeingTrade_incoterm',
        'salesForeingTrade_subdivision',
        'salesForeingTrade_certificateOforigin',
        'salesForeingTrade_numberCertificateOrigin',
        'salesForeingTrade_trustedExportedNumber',
    ];
}
