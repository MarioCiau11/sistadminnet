<?php

namespace App\Models\historicos;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HIST_STAMPED extends Model
{
    use HasFactory;

    protected $table = 'HIST_STAMPED';
    protected $primaryKey = 'histStamped_ID';
    public $timestamps = false;

    protected $fillable = [
        'histStamped_IDCompany',
        'histStamped_Date',
        'histStamped_Stamp',
    ];

    public function getTotalTimbres()
    {
        $companyKey = session('company')->companies_key;

        $totalConsumed = HIST_STAMPED::where('histStamped_IDCompany', $companyKey)->sum('histStamped_Stamp');

        return $totalConsumed;
    }

    public function getTotalAyer()
    {
        $companyKey = session('company')->companies_key;
        $fechaAyer = new Carbon('yesterday');

        $totalConsumed = HIST_STAMPED::where('histStamped_IDCompany', $companyKey)
            ->where('histStamped_Date', $fechaAyer)
            ->sum('histStamped_Stamp');

        return $totalConsumed;
    }

    public function getTotalHoy()
    {
        $companyKey = session('company')->companies_key;
        $fechaHoy = new Carbon('today');

        $totalConsumed = HIST_STAMPED::where('histStamped_IDCompany', $companyKey)
            ->where('histStamped_Date', $fechaHoy)
            ->sum('histStamped_Stamp');

        return $totalConsumed;
    }

}
