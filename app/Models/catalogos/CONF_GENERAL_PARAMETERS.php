<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CONF_GENERAL_PARAMETERS extends Model
{
    use HasFactory;

    protected $table = 'CONF_GENERAL_PARAMETERS';
    protected $primaryKey = 'generalParameters_id';
    public $incrementing = false;

    protected $fillable = [
        'generalParameters_company',
        'generalParameters_businessDays',
        'generalParameters_exerciseStarts',
        'generalParameters_exerciseEnds',
        'generalParameters_filesCustomers',
        'generalParameters_filesProviders',
        'generalParameters_filesMovements',
        'generalParameters_billsNot',
        'generalParameters_defaultMoney',
        'generalParameters_exchangeRate',

    ];

    //REFERENCIAMOS EL MODELO DE LA TABLA CONF_GENERAL_PARAMETERS_CONSECUTIVES
    public function consecutivos()
    {
    //    return $this->belongsTo('App\Models\catalogos\CONF_GENERAL_PARAMETERS_CONSECUTIVES', 'generalParameters_id', 'generalConsecutives_generalParametersID');

        return $this->belongsTo('App\Models\catalogos\CONF_GENERAL_PARAMETERS_CONSECUTIVES', 'generalParameters_id', 'generalConsecutives_generalParametersID')
        ->where('generalConsecutives_company', session('company')->companies_key) // Cambia 'company' por el nombre de tu columna de empresa
            ->where('generalConsecutives_branchOffice', session('sucursal')->branchOffices_key); // Cambia 'branchOffice' por el nombre de tu columna de sucursal
    }
    
    

    
}
