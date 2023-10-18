<?php

namespace App\Models\catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;


class CAT_ARTICLES extends Model
{
    use HasFactory;

    protected $table = 'CAT_ARTICLES';
    protected $primaryKey = 'articles_key';
    public $incrementing = false;

    protected $fillable = [
        'articles_key',
        'articles_type',
        'articles_status',
        'articles_descript',
        'articles_descript2',
        'articles_unitSale',
        'articles_transfer',
        'articles_unitBuy',
        'articles_group',
        'articles_category',
        'articles_family',
        'articles_porcentIva',
        'articles_retention1',
        'articles_retention2',
        'articles_listPrice1',
        'articles_listPrice2',
        'articles_listPrice3',
        'articles_listPrice4',
        'articles_listPrice5',
        'articles_productService',
        'articles_objectTax',
        'articles_costoTotal',
        'articles_cantidadTotal'
    ];

    public function scopewhereArticlesKey($query, $key){
        if(!is_null($key)){
            return $query->where('articles_key', '=', $key);
        }
        return $query;
    }

    public function scopewhereArticlesNombre($query, $name){
        if(!is_null($name)){
           return $query->where('articles_descript', 'like', '%'.$name.'%');
        }
        return $query;
    }

     public function scopewhereArticlesGroup($query, $group){
        if(!is_null($group)){
            return $query->where('articles_group', '=', $group);
        }
        return $query;
    }

    public function scopewhereArticlesCategory($query, $category){
        if(!is_null($category)){
            return $query->where('articles_category', '=', $category);
        }
        return $query;
    }

    public function scopewhereArticlesFamily($query, $family){
        if(!is_null($family)){
            return $query->where('articles_family', '=', $family);
        }
        return $query;
    }


    public function scopewhereArticlesStatus($query, $status){
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }

            return $query->where('articles_status', '=', $status);
        }
        return $query;
    }

    public function scopewhereArticlesPriceList($query, $priceList){
        if(!is_null($priceList)){
            switch ($priceList) {
                case 'Precio 1':
                    return $query->where('articles_listPrice1', '>', 0);
                    break;
                case 'Precio 2':
                    return $query->where('articles_listPrice2', '>', 0);
                    break;
                case 'Precio 3':
                    return $query->where('articles_listPrice3', '>', 0);
                    break;
                case 'Precio 4':
                    return $query->where('articles_listPrice4', '>', 0);
                    break;
                case 'Precio 5':
                    return $query->where('articles_listPrice5', '>', 0);
                    break;
            }
        }
    }

    public function scopewhereArticlesInvBranch($query, $invBranch){
        if(!is_null($invBranch)){

            if($invBranch === 'Todos'){
                return $query;
            }

            return $query->where('articlesInv_branchKey', '=', $invBranch)->orwhere('articlesInv_branchKey', '=', null);
        }
        return $query;
    }

    public function scopewhereArticlesInvDepot($query, $invDepot){
        if(!is_null($invDepot)){

            if($invDepot === 'Todos'){
                return $query;
            }

            return $query->where('articlesInv_depot', '=', $invDepot)->orwhere('articlesInv_depot', '=', null);
        }
        return $query;
    }

    public function scopewhereArticlesCostDepot($query, $costDepot){
        if(!is_null($costDepot)){

            if($costDepot === 'Todos'){
                return $query;
            }

            return $query->where('articlesCost_depotKey', '=', $costDepot)->orwhere('articlesCost_depotKey', '=', null);
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
