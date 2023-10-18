<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_BALANCE extends Model
{
    use HasFactory;

    protected $table = 'PROC_BALANCE';
    protected $primaryKey = 'balance_id';

    protected $fillable = [
        'balance_companieKey',
        'balance_branchKey',
        'balance_branch',
        'balance_money',
        'balance_group',
        'balance_account',
        'balance_balance',
        'balance_reconcile',
    ];
}
