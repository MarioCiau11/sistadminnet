<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_EXPENSES_FILES extends Model
{
    use HasFactory;

    protected $table = 'PROC_EXPENSES_FILES';
    protected $primaryKey = 'expensesFiles_id';
    public $incrementing = false;

    protected $fillable = [
        'expensesFiles_keyExpense',
        'expensesFiles_path',
        'expensesFiles_file',
    ];
}
