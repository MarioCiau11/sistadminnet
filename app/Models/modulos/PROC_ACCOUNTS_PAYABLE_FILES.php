<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_ACCOUNTS_PAYABLE_FILES extends Model
{
    use HasFactory;
    protected $table = "PROC_ACCOUNTS_PAYABLE_FILES";
    protected $primaryKey = "accountsPayableFiles_id";
    public $incrementing = false;

    protected $fillable = [
        'accountsPayableFiles_keyAccountPayable',
        'accountsPayableFiles_path',
        'accountsPayableFiles_file'
    ];
}
