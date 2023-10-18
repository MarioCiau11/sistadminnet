<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_name',
        'username',
        'user_email',
        'password',
        'user_rol',
        'user_status',
        'user_block_sale_prices',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'user_remember_token',
        
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_email_verified_at' => 'datetime',
    ];

    public function scopewhereUserName($query, $name)
    {
        if(!is_null($name)){
            return $query->where('user_name', 'like', '%'.$name.'%');
        }
        return $query;
    }

    public function scopewhereUserNames($query, $user)
    {
        if(!is_null($user)){
            return $query->where('username', 'like', $user.'%');
        }
        return $query;
    }

    public function scopewhereUserRoles($query, $rol)
    {
        if(!is_null($rol)){
            return $query->where('user_rol', '=', $rol);
        }
        return $query;
    }

    public function scopewhereUserStatus($query, $status){
        if(!is_null($status)){

            if($status === 'Todos'){
                return $query;
            }

            return $query->where('user_status', '=', $status);
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
