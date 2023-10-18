<?php

namespace App\Models\agrupadores;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_PROVIDER_LIST extends Model
{
    use HasFactory;

    protected $table = 'CAT_PROVIDER_LIST';
    protected $primaryKey = 'listProvider_id';

    protected $fillable = [
        'listProvider_name',
        'listProvider_status',
    ];

    public function scopeWhereListproviderId($query, $id)
    {
        if (!is_null($id)) {
            return $query->where('listProvider_id', 'like', $id . '%');
        }
        return $query;
    }

    public function scopeWhereListproviderName($query, $name)
    {
        if (!is_null($name)) {
            return $query->where('listProvider_name', 'like', '%' . $name . '%');
        }
        return $query;
    }

    public function scopeWhereListproviderStatus($query, $status)
    {
        if (!is_null($status)) {

            if ($status === 'Todos') {
                return $query;
            }

            return $query->where('listProvider_status', '=', $status);
        }
        return $query;
    }
}
