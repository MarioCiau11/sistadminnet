<?php

namespace App\Models\catalogos;

use App\Models\agrupadores\CAT_PROVIDER_LIST;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAT_PROVIDERS extends Model
{
    use HasFactory;

    protected $table = 'CAT_PROVIDERS';
    protected $primaryKey = 'providers_key';

    public $incrementing = false;

    protected $fillable = [
        'providers_name',
        'providers_nameShort',
        'providers_RFC',
        'providers_CURP',
        'providers_status',
        'providers_address',
        'providers_roads',
        'providers_outdoorNumber',
        'providers_interiorNumber',
        'providers_colonyFractionation',
        'providers_townMunicipality',
        'providers_country',
        'providers_cp',
        'providers_observations',
        'providers_phone1',
        'providers_phone2',
        'providers_cellphone',
        'providers_contact1',
        'providers_mail1',
        'providers_contact2',
        'providers_mail2',
        'providers_group',
        'providers_category',
        'providers_creditCondition',
        'providers_formPayment',
        'providers_money',
        'providers_taxRegime',
        'providers_bankAccount',
        'providers_nameFile',
        'providers_route',
    ];

    public function scopewhereProvidersKey($query, $key){
        if(!is_null($key)){
            return $query->where('providers_key', 'like', $key.'%');
        }
        return $query;
    }

    public function scopewhereProvidersName($query, $name){
        if(!is_null($name)){
            return $query->where('providers_name', 'like', '%'.$name.'%');
        }
        return $query;
    }

    public function scopewhereProvidersStatus($query, $status){
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }

            return $query->where('providers_status', '=', $status);
        }
        return $query;
    }

    public function scopewhereProvidersGroup($query, $group){
        if(!is_null($group)){
            return $query->where('providers_group', '=', $group);
        }
        return $query;
    }

    public function scopewhereProvidersCategory($query, $category){
        if(!is_null($category)){
            return $query->where('providers_category', '=', $category)->orderBy('providers_key', 'desc');
        }
        return $query;
    }

    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y');
    }

    //relacionamos proveedores con la lista de precios de proveedores. Para ello hacemos un belongsTo. Un proveedor puede tener solo una lista de precios. relacionaremos listProvider_id con providers_priceList
    // public function listaProveedores(){
    //     return $this->belongsTo(CAT_PROVIDER_LIST::class, 'listProvider_id', 'providers_priceList');
    // }

    public function listaProveedores()
    {
        return $this->belongsTo('App\Models\agrupadores\CAT_PROVIDER_LIST', 'providers_priceList', 'listProvider_id');
    }

    public function formaPago()
    {
        return $this->belongsTo('App\Models\catalogos\CONF_FORMS_OF_PAYMENT', 'providers_formPayment', 'formsPayment_key');
    }

    public function regimenFiscal()
    {
        return $this->belongsTo('App\Models\catalogosSAT\CAT_SAT_REGIMENFISCAL', 'providers_taxRegime', 'c_RegimenFiscal');
    }


}
