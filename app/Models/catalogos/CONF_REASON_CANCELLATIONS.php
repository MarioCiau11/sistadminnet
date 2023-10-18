<?php

namespace App\Models\catalogos;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CONF_REASON_CANCELLATIONS extends Model
{
    use HasFactory;

    protected $table = 'CONF_REASON_CANCELLATIONS';
    protected $primaryKey = 'reasonCancellations_id';
    public $incrementing = false;

    protected $fillable = [
        'reasonCancellations_name',
        'reasonCancellations_module',
        'reasonCancellations_status',
    ];

    public function scopewhereReasonCancellationsName($query, $reasonName)
    {
        if(!is_null($reasonName)){
            return $query ->where('reasonCancellations_name', 'like', '%'.$reasonName.'%');
        }
        return $query;
    }

    public function scopewhereStatus($query, $status)
    {
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }
            
            return $query ->where('reasonCancellations_status', '=', $status);
        }
        return $query;
    }

    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y');
    }
}
