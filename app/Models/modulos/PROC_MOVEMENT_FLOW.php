<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_MOVEMENT_FLOW extends Model
{
    use HasFactory;

    protected $table = 'PROC_MOVEMENT_FLOW';
    protected $primaryKey = 'movementFlow_id';

    protected $fillable = [
        'movementFlow_branch',
        'movementFlow_company',
        'movementFlow_moduleOrigin',
        'movementFlow_originID',
        'movementFlow_movementOrigin',
        'movementFlow_movementOriginID',
        'movementFlow_moduleDestiny',
        'movementFlow_destinityID',
        'movementFlow_movementDestinity',
        'movementFlow_movementDestinityID',
        'movementFlow_cancelled',
        ];
}
