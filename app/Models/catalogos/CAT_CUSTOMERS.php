<?php

namespace App\Models\catalogos;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_CUSTOMERS extends Model
{
    use HasFactory;

    protected $table = 'CAT_CUSTOMERS';
    protected $primaryKey = 'customers_key';

    public $incrementing = false;

    protected $fillable = [
        'customers_name',
        'customers_type',
        'customers_businessName',
        'customers_RFC',
        'customers_CURP',
        'customers_name',
        'customers_lastName',
        'customers_lastName2',
        'customers_cellphone',
        'customers_mail',
        'customers_addres',
        'customers_roads',
        'customers_outdoorNumber',
        'customers_interiorNumber',
        'customers_colonyFractionation',
        'customers_townMunicipality',
        'customers_state',
        'customers_country',
        'customers_cp',
        'customers_phone1',
        'customers_phone2',
        'customers_contact1',
        'customers_mail1',
        'customers_contact2',
        'customers_mail2',
        'customers_observations',
        'customers_group',
        'customers_category',
        'customers_status',
        'customers_priceList',
        'customers_creditCondition',
        'customers_creditLimit',
        'customers_identificationCFDI',
        'customers_taxRegime',
        'customers_nameFile',
        'customers_route',
    ];

    public function scopewhereCustomersKey($query, $key){
        if(!is_null($key)){
            return $query->where('customers_key', 'like', $key.'%');
        }
        return $query;
    }

    public function scopewhereCustomersName($query, $name){
        if(!is_null($name)){
            return $query->where('customers_name', 'like', '%'.$name.'%');
        }
        return $query;
    }

    public function scopewhereCustomersBusinessName($query, $businessName){
        if(!is_null($businessName)){
            return $query->where('customers_businessName', 'like', '%'.$businessName.'%');
        }
        return $query;
    }



    public function scopewhereCustomersStatus($query, $status){
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }

            return $query->where('customers_status', '=', $status);
        }
        return $query;
    }

    public function scopewhereCustomersGroup($query, $group){
        if(!is_null($group)){
            return $query->where('customers_group', '=', $group);
        }
        return $query;
    }

    public function scopewhereCustomersCategory($query, $category){
        if(!is_null($category)){
            return $query->where('customers_category', '=', $category);
        }
        return $query;
    }


    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function regimenFiscal()
    {
        return $this->belongsTo('App\Models\catalogosSAT\CAT_SAT_REGIMENFISCAL', 'customers_taxRegime', 'c_RegimenFiscal');
    }

}
