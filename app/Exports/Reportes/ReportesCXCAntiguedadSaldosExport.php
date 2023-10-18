<?php

namespace App\Exports\Reportes;

use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE_P;
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

class ReportesCXCAntiguedadSaldosExport implements FromView, ShouldAutoSize, WithStyles, WithDrawings, WithColumnWidths, WithEvents
{
    public $nameClienteUno;
    public $nameClienteDos;
    public $nameCliente;
    public $nameCategoria;
    public $nameGrupo;
    public $namePlazo;
    public $nameMoneda;

    public function __construct($nameClienteUno, $nameClienteDos, $nameCliente, $nameCategoria, $nameGrupo, $namePlazo, $nameMoneda)
    {
        $this->nameClienteUno = $nameClienteUno;
        $this->nameClienteDos = $nameClienteDos;
        $this->nameCliente = $nameCliente;
        $this->nameCategoria = $nameCategoria;
        $this->nameGrupo = $nameGrupo;
        $this->namePlazo = $namePlazo;
        $this->nameMoneda = $nameMoneda;
    }

    public function view(): View
    {

        $cxc = PROC_ACCOUNTS_RECEIVABLE_P::join('CAT_CUSTOMERS', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_customer', '=', 'CAT_CUSTOMERS.customers_key')
            ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('CONF_MONEY', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_money', '=', 'CONF_MONEY.money_key')
            ->where('PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_movement', '=', 'Factura')
            ->where('PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_company', '=', session('company')->companies_key)
            // ->orWhere('PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_movement', '=', 'Entrada por Compra')
            ->whereAccountsReceivablePCustomer($this->nameClienteUno, $this->nameClienteDos, $this->nameCliente)
            ->whereCustomerCategory($this->nameCategoria)
            ->whereCustomerGroup($this->nameGrupo)
            ->whereAccountsReceivableMoratoriumDays($this->namePlazo)
            ->whereAccountsReceivableMoney($this->nameMoneda)
            ->orderBy('PROC_ACCOUNTS_RECEIVABLE_P.updated_at', 'DESC')
            ->get();
        $clientesPorCXC = [];

        if (!$cxc->isEmpty()) {
            $cxccollectionCXC = collect($cxc);

            $sucursal_almacen = $cxccollectionCXC->unique('accountsReceivableP_branchOffice')->unique()->first();

            $sucursal = $sucursal_almacen->branchOffices_key . '-' . $sucursal_almacen->branchOffices_name;


            foreach ($cxccollectionCXC as $cliente) {
                $cuentasxc = PROC_ACCOUNTS_RECEIVABLE_P::join('CAT_CUSTOMERS', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_customer', '=', 'CAT_CUSTOMERS.customers_key')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_company', '=', 'CAT_COMPANIES.companies_key')
                    ->join('CONF_MONEY', 'PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_money', '=', 'CONF_MONEY.money_key')
                    ->where('PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_movement', '=', 'Factura')
                    ->where('PROC_ACCOUNTS_RECEIVABLE_P.accountsReceivableP_company', '=', session('company')->companies_key)
                    ->whereAccountsReceivablePCustomer($cliente->customers_key, $cliente->customers_key, $cliente->customers_key)
                    ->whereAccountsReceivableMoney($cliente->accountsReceivableP_money)
                    ->whereCustomerCategory($this->nameCategoria)
                    ->whereCustomerGroup($this->nameGrupo)
                    ->whereAccountsReceivableMoratoriumDays($this->namePlazo)
                    ->orderBy('PROC_ACCOUNTS_RECEIVABLE_P.updated_at', 'DESC')
                    ->get();



                if (!array_key_exists($cliente->customers_businessName . '-' . $cliente->accountsReceivableP_money, $clientesPorCXC)) {
                    $clientesPorCXC[$cliente->customers_businessName . '-' . $cliente->accountsReceivableP_money] = $cliente->toArray();
                }
                $clientesPorCXC[$cliente->customers_businessName . '-' . $cliente->accountsReceivableP_money] = array_merge($clientesPorCXC[$cliente->customers_businessName . '-' . $cliente->accountsReceivableP_money], ['cuentasxc' => $cuentasxc->toArray()]);
            }
        }



        return view('exports.reporteCXCAntiguedadSaldos', [
            'cuentasxcobrar' => $clientesPorCXC,
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
