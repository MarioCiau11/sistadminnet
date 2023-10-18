<?php

namespace App\Models\historicos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FAVORITE_REPORTS extends Model
{
    use HasFactory;

    protected $table = 'FAVORITE_REPORTS';
    protected $primaryKey = 'reports_id';
    public $incrementing = false;
    public $timestamps = false;


    protected $fillable = [
        'user_id',
        'report_key',
        'report_name',
        'report_identifier',
    ];
}
