<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_PURCHASE_FILES extends Model
{
    use HasFactory;

    protected $table = "PROC_PURCHASE_FILES";
    protected $primaryKey = "purchaseFiles_id";
    public $incrementing = false;

    protected $fillable = [
        'purchaseFiles_keyPurchase',
        'purchaseFiles_path',
        'purchaseFiles_file',
    ];
}
