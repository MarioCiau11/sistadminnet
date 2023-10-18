<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_PROVIDERS_FILES extends Model
{
    use HasFactory;

    protected $table = 'CAT_PROVIDERS_FILES';
    protected $primaryKey = 'providersFiles_id';

    public $incrementing = false;

    protected $fillable = [
        'providersFiles_keyProvider',
        'providersFiles_path',
        'providersFiles_file',
    ];
}
