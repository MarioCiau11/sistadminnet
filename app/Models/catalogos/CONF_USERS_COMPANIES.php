<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CONF_USERS_COMPANIES extends Model
{
    use HasFactory;

    protected $table = 'CONF_USERS_COMPANIES';
    
    protected $primaryKey = 'usersCompanies_id';
    public $incrementing = false;

    protected $fillable = [
        'usersCompanies_userID',
        'usersCompanies_userCompany'];

    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y');
    }

}
