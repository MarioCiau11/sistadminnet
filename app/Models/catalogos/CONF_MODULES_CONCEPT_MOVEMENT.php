<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CONF_MODULES_CONCEPT_MOVEMENT extends Model
{
    use HasFactory;

    protected $table = 'CONF_MODULES_CONCEPT_MOVEMENT';
    protected $primaryKey = 'moduleMovement_id';
    public $incrementing = false;

    protected $fillable = [
        'moduleMovement_conceptID',
        'moduleMovement_moduleName',
        'moduleMovement_movementName',
    ];
}
