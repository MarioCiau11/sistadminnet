<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_TREASURY_FILES extends Model
{
    use HasFactory;
    public $table = "PROC_TREASURY_FILES";
    public $primaryKey = "treasuriesFiles_id";
    public $incrementing = false;

    protected $fillable = [
        'treasuriesFiles_keyTreasury',
        'treasuriesFiles_path',
        'treasuriesFiles_file',
    ];
}
