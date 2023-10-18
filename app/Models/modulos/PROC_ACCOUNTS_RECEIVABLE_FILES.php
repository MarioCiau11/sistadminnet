<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_ACCOUNTS_RECEIVABLE_FILES extends Model
{
    use HasFactory;

    protected $table = "PROC_ACCOUNTS_RECEIVABLE_FILES";
    protected $primaryKey = "accountsReceivableFiles_id";
    public $incrementing = false;

    protected $fillable = [
        'accountsReceivableFiles_keyaccountsReceivable',
        'accountsReceivableFiles_path',
        'accountsReceivableFiles_file',
    ];
}
