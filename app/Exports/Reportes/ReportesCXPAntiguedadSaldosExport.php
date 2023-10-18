<?php

namespace App\Exports\Reportes;

use App\Models\modulos\PROC_ACCOUNTS_PAYABLE;
use App\Models\modulos\PROC_ACCOUNTS_PAYABLE_P;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ReportesCXPAntiguedadSaldosExport implements FromView, ShouldAutoSize, WithStyles, WithDrawings, WithColumnWidths, WithEvents
{
    public $nameProveedorUno;
    public $nameProveedorDos;
    public $nameProveedor;
    public $nameCategoria;
    public $nameGrupo;
    public $namePlazo;
    public $nameMoneda;


    public function __construct($nameProveedorUno, $nameProveedorDos, $nameProveedor, $nameCategoria, $nameGrupo, $namePlazo, $nameMoneda)
    {
        $this->nameProveedorUno = $nameProveedorUno;
        $this->nameProveedorDos = $nameProveedorDos;
        $this->nameProveedor = $nameProveedor;
        $this->nameCategoria = $nameCategoria;
        $this->nameGrupo = $nameGrupo;
        $this->namePlazo = $namePlazo;
        $this->nameMoneda = $nameMoneda;
    }

    public function view(): View
    {
        $cxp = PROC_ACCOUNTS_PAYABLE_P::join('CAT_PROVIDERS', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_provider', '=', 'CAT_PROVIDERS.providers_key')
            ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CONF_MONEY', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_money', '=', 'CONF_MONEY.money_key')
            ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_company', '=', 'CAT_COMPANIES.companies_key')
            ->whereIn('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_movement', ['Factura de Gasto', 'Entrada por Compra'])
            ->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_company', '=', session('company')->companies_key)
            ->whereAccountsPayablePProvider($this->nameProveedorUno, $this->nameProveedorDos, $this->nameProveedor)
            ->whereAccountsPayablePMoratoriumDays($this->namePlazo)
            ->whereAccountsPayablePMoney($this->nameMoneda)
            ->whereProviderCategory($this->nameCategoria)
            ->whereProviderGroup($this->nameGrupo)
            ->orderBy('PROC_ACCOUNTS_PAYABLE_P.updated_at', 'DESC')
            ->get();
        $proveedoresPorCXP = [];

        if (!$cxp->isEmpty()) {
            $collectionCXP = collect($cxp);

            // dd($collectionCXP, $proveedoresCXP);
            $sucursal_almacen = $collectionCXP->unique('accountsPayableP_branchOffice')->unique()->first();

            $sucursal = $sucursal_almacen->branchOffices_key . '-' . $sucursal_almacen->branchOffices_name;


            foreach ($collectionCXP as $proveedor) {
                $cuentasxp = PROC_ACCOUNTS_PAYABLE_P::join('CAT_PROVIDERS', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_provider', '=', 'CAT_PROVIDERS.providers_key')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CONF_MONEY', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_money', '=', 'CONF_MONEY.money_key')
                    ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_company', '=', 'CAT_COMPANIES.companies_key')
                    ->whereIn('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_movement', ['Factura de Gasto', 'Entrada por Compra'])
                    ->where('PROC_ACCOUNTS_PAYABLE_P.accountsPayableP_company', '=', session('company')->companies_key)
                    ->whereAccountsPayablePProvider($proveedor->providers_key, $proveedor->providers_key, $proveedor->providers_key)
                    ->whereAccountsPayablePMoney($proveedor->accountsPayableP_money)
                    ->whereAccountsPayablePMoratoriumDays($this->namePlazo)
                    ->whereProviderCategory($this->nameCategoria)
                    ->whereProviderGroup($this->nameGrupo)
                    ->orderBy('PROC_ACCOUNTS_PAYABLE_P.updated_at', 'DESC')
                    ->get();
                // dd($cuentasxp);

                if (!array_key_exists($proveedor->providers_name . '-' . $proveedor->accountsPayableP_money, $proveedoresPorCXP)) {
                    $proveedoresPorCXP[$proveedor->providers_name . '-' . $proveedor->accountsPayableP_money] = $proveedor->toArray();
                }
                $proveedoresPorCXP[$proveedor->providers_name . '-' . $proveedor->accountsPayableP_money] =  array_merge($proveedoresPorCXP[$proveedor->providers_name . '-' . $proveedor->accountsPayableP_money], ['cuentasxp' => $cuentasxp->toArray()]);
            }

            // DD($proveedoresPorCXP);

        }
        return view('exports.reporteCXPAntiguedadSaldos', [
            'cuentasxpagar' => $proveedoresPorCXP,
        ]);
    }

    public function drawings()
    {

        if (session('company')->companies_logo === null || session('company')->companies_logo === '') {
            $logoFile = null;
        } else {
            $logoFile = storage_path('app/empresas/' . session('company')->companies_logo);
            if (!file_exists($logoFile)) {
                $logoFile = null;
            }
        }

        if ($logoFile == null) {
            $logoFile = storage_path('app/empresas/default.png');
        }

        $drawing = new Drawing();
        $drawing->setPath($logoFile);
        $drawing->setWidth(50);
        $drawing->setHeight(50);
        $drawing->setCoordinates('A1');

        return $drawing;
    }

    public function columnWidths(): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        //poner altura a la fila A1
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(50);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
