<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_CUSTOMERS_FILES extends Model
{
    use HasFactory;

    protected $table = 'CAT_CUSTOMERS_FILES';
    protected $primaryKey = 'customersFiles_id';

    public $incrementing = false;

    protected $fillable = [
        'customersFiles_keyCustomer',
        'customersFiles_path',
        'customersFiles_file',
    ];
}
