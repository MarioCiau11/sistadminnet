<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_SALES_FILES extends Model
{
    use HasFactory;

    protected $table = "PROC_SALES_FILES";
    protected $primaryKey = "salesFiles_id";
    public $incrementing = false;

    protected $fillable = [
        'salesFiles_keySale',
        'salesFiles_path',
        'salesFiles_file',
    ];
}
