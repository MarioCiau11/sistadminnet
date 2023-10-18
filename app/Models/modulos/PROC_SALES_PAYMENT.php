<?php

namespace App\Models\modulos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PROC_SALES_PAYMENT extends Model
{
    use HasFactory;

    protected $table = 'PROC_SALES_PAYMENT';
    protected $primaryKey = 'salesPayment_id';
    public $incrementing = false;

    protected $fillable = [
        'salesPayment_saleID',
        'salesPayment_paymentMethod1',
        'salesPayment_paymentMethod2',
        'salesPayment_paymentMethod3',
        'salesPayment_amount1',
        'salesPayment_amount2',
        'salesPayment_amount3',
        'salesPayment_fullCharge',
        'salesPayment_Change',
        'salesPayment_moneyAccount',
        'salesPayment_moneyAccountType',
        'salesPayment_paymentMethodChange',
        'salesPayment_branchOffice',
    ];

    function getMetodo1() {
        return $this->hasOne('App\Models\catalogos\CONF_FORMS_OF_PAYMENT', 'formsPayment_key', 'salesPayment_paymentMethod1');
    }

    function getMetodo2() {
        return $this->hasOne('App\Models\catalogos\CONF_FORMS_OF_PAYMENT', 'formsPayment_key', 'salesPayment_paymentMethod2');
    }

    function getMetodo3() {
        return $this->hasOne('App\Models\catalogos\CONF_FORMS_OF_PAYMENT', 'formsPayment_key', 'salesPayment_paymentMethod3');
    }

    function getFormaCambio() {
        return $this->hasOne('App\Models\catalogos\CONF_FORMS_OF_PAYMENT', 'formsPayment_key', 'salesPayment_paymentMethodChange');
    }
}
