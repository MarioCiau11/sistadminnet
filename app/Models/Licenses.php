<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Licenses extends Model
{
    use HasFactory;

    protected $primaryKey = 'license_ID';
    protected $table = 'LICENSES';
    protected $fillable = [
        'license_UserID',
        'license_Licenses',
        'license_Active',
    ];

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'user_licenses', 'license_id', 'user_id');
    }
}
