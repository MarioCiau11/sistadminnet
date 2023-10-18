<?php

namespace App\Models\catalogos;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_COMPANIES extends Model
{
    use HasFactory;

    protected $table = 'CAT_COMPANIES';
    protected $primaryKey = 'companies_key';

    public $incrementing = false;

    protected $fillable = [
        'companies_id',
        'companies_name',
        'companies_nameShort',
        'companies_descript',
        'companies_status',
        'companies_logo',
        'companies_addres',
        'companies_suburb',
        'companies_cp',
        'companies_city',
        'companies_state',
        'companies_country',
        'companies_phone1',
        'companies_phone2',
        'companies_mail',
        'companies_rfc',
        'companies_taxRegime',
        'companies_employerRegistration',
        'companies_representative',
        'companies_routeKey',
        'companies_routeCertificate',
        'companies_routeFiles',
        'companies_passwordKey',
        'companies_referenceProvider',
    ];

    public function scopewhereCompaniesKey($query, $key){
        if(!is_null($key)){
            return $query->where('companies_key', 'like', $key.'%');
        }
        return $query;
    }

    public function scopewhereCompaniesName($query, $name){
        if(!is_null($name)){
            return $query->where('companies_name', 'like', '%'.$name.'%');
        }
        return $query;
    }

    public function scopewhereCompaniesStatus($query, $status){
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }

            return $query->where('companies_status', '=', $status);
        }
        return $query;
    }

    public function getCompany(){
        // será con la sesión
        return $this->where('companies_key', '=', session('company')->companies_key)->first();
    }

    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }
}
