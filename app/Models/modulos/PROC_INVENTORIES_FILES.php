<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_INVENTORIES_FILES extends Model
{
    use HasFactory;

    protected $table = "PROC_INVENTORIES_FILES";
    protected $primaryKey = "inventoriesFiles_id";
    public $incrementing = false;

    protected $fillable = [
        'inventoriesFiles_keyInventory',
        'inventoriesFiles_path',
        'inventoriesFiles_file',
    ];
}
