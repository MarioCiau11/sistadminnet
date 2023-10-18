<?php

namespace App\Models\catalogos;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CONF_USERS_BRANCH_ALLOWED extends Model
{
    use HasFactory;

    protected $table = 'CONF_USERS_BRANCH_ALLOWED';

    protected $primaryKey = 'usersBranch_id';
    public $incrementing = false;

    protected $fillable = [
        'usersBranch_userID',
        'usersBranch_userBranchAllowed'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }


}
