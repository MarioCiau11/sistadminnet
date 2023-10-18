<?php

namespace App\Models\timbrado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_CANCELED_REASON extends Model
{
    use HasFactory;
    protected $table = 'PROC_CANCELED_REASON';
    protected $primaryKey = 'canceledReason_id';
    public $incrementing = false;

    protected $fillable = [
        "canceledReason_module",
        "canceledReason_moduleID",
        "canceledReason_reason",
        "canceledReason_sustitutionUuid",
    ];
}
