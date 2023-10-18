<?php

namespace App\Exports\Reportes;

use App\Models\modulos\PROC_ASSISTANT_UNITS;
use App\Models\modulos\PROC_DEL_SERIES_MOV;
use App\Models\modulos\PROC_DEL_SERIES_MOV2;
use App\Models\modulos\PROC_INVENTORIES;
use App\Models\modulos\PROC_LOT_SERIES_MOV;
use App\Models\modulos\PROC_LOT_SERIES_MOV2;
use App\Models\modulos\PROC_PURCHASE;
use App\Models\modulos\PROC_SALES;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;


class ReportesInventarioDesglosadoExport implements FromView, ShouldAutoSize, WithStyles, WithDrawings, WithEvents
{
    public $nameDelArticulo;
    public $nameAlArticulo;
    public $nameArticulo;
    public $nameFecha;
    public $nameCategoria;
    public $nameFamilia;
    public $nameGrupo;
    public $nameAlmacen;
    public $nameMov;
    public $nameSerie;
    public $nameExistencia;

    public function __construct($nameDelArticulo, $nameAlArticulo, $nameArticulo, $nameFecha, $nameCategoria, $nameFamilia, $nameGrupo, $nameAlmacen, $nameMov, $nameSerie, $nameExistencia)
    {
        $this->nameDelArticulo = $nameDelArticulo;
        $this->nameAlArticulo = $nameAlArticulo;
        $this->nameArticulo = $nameArticulo;
        $this->nameFecha = $nameFecha;
        $this->nameCategoria = $nameCategoria;
        $this->nameFamilia = $nameFamilia;
        $this->nameGrupo = $nameGrupo;
        $this->nameAlmacen = $nameAlmacen;
        $this->nameMov = $nameMov;
        $this->nameSerie = $nameSerie;
        $this->nameExistencia = $nameExistencia;
    }

    public function view(): View
    {
        $inventario =  PROC_ASSISTANT_UNITS::join('CAT_ARTICLES', 'PROC_ASSISTANT_UNITS.assistantUnit_account', '=', 'CAT_ARTICLES.articles_key')
            ->join('CAT_DEPOTS', 'PROC_ASSISTANT_UNITS.assistantUnit_group', '=', 'CAT_DEPOTS.depots_key')
            ->join('CAT_BRANCH_OFFICES', 'PROC_ASSISTANT_UNITS.assistantUnit_branchKey', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CAT_COMPANIES', 'PROC_ASSISTANT_UNITS.assistantUnit_companieKey', '=', 'CAT_COMPANIES.companies_key')
            ->whereAssistantUnitAccount($this->nameDelArticulo, $this->nameAlArticulo, $this->nameArticulo)
            ->whereAssistantUnitDate($this->nameFecha)
            ->whereArticleCategory($this->nameCategoria)
            ->whereArticleFamily($this->nameFamilia)
            ->whereArticleGroup($this->nameGrupo)
            ->whereAssistantUnitDepot($this->nameAlmacen)
            ->whereAssistantUnitMovement($this->nameMov)
            ->where('PROC_ASSISTANT_UNITS.assistantUnit_companieKey', '=', session('company')->companies_key)
            ->where('PROC_ASSISTANT_UNITS.assistantUnit_branchKey', '=',  session('sucursal')->branchOffices_key)
            ->where('PROC_ASSISTANT_UNITS.assistantUnit_canceled', '=', 0)
            ->get()
            ->unique('assistantUnit_id');

        $informacionDesglose = [];

        foreach ($inventario as $inventario) {
            $modulo = strtoupper($inventario->assistantUnit_module);
            $tipoArticulo = $inventario->articles_type;
            $identificadorModulo = $inventario->assistantUnit_moduleID;
            $articuloReferencia = $inventario->assistantUnit_account;
            $empresa = $inventario->assistantUnit_companieKey;
            $sucursal = $inventario->assistantUnit_branchKey;
            $isConcluidoMov = false;

            //Validamos que los movimientos esten en statusConcluido
            switch ($inventario->assistantUnit_module) {
                case 'Compras':
                    $movCompra = PROC_PURCHASE::WHERE('purchase_id', '=', $identificadorModulo)->WHERE('purchase_status', '=', 'FINALIZADO')->first();

                    if ($movCompra != NULL) {
                        $isConcluidoMov = true;
                    }
                    break;

                case 'Ventas':
                    $movVentas = PROC_SALES::WHERE('sales_id', '=', $identificadorModulo)->WHERE('sales_status', '=', 'FINALIZADO')->first();

                    if ($movVentas != NULL) {
                        $isConcluidoMov = true;
                    }
                    break;

                case 'Inv':
                    $movInventario = PROC_INVENTORIES::WHERE('inventories_id', '=', $identificadorModulo)->WHERE('inventories_status', '=', 'FINALIZADO')->first();

                    if ($movInventario != NULL) {
                        $isConcluidoMov = true;
                    }
                    break;

                default:
                    # code...
                    break;
            }

            if ($isConcluidoMov) {
                if ($tipoArticulo == "Serie" && $this->nameSerie == "Si") {
                    switch ($inventario->assistantUnit_module) {
                        case 'Compras':
                            $seriesCompras = PROC_LOT_SERIES_MOV::WHERE('lotSeriesMov_module', '=', $modulo)->WHERE('lotSeriesMov_purchaseID', '=', $identificadorModulo)->WHERE('lotSeriesMov_article', '=', $articuloReferencia)->where('lotSeriesMov_companieKey', '=', $empresa)
                                ->where('lotSeriesMov_branchKey', '=',  $sucursal)->GET();
                            if (count($seriesCompras) >= 1) {
                                $inventarioConSerie = array_merge($inventario->toArray(), ['series' => $seriesCompras->toArray()]);
                                array_push($informacionDesglose, $inventarioConSerie);
                            } else {
                                array_push($informacionDesglose, $inventario->toArray());
                            }
                            break;

                        case 'Ventas':
                            $seriesVentas = PROC_DEL_SERIES_MOV2::WHERE('delSeriesMov2_module', '=', $inventario->assistantUnit_module)->WHERE('delSeriesMov2_saleID', '=', $identificadorModulo)->WHERE('delSeriesMov2_article', '=', $articuloReferencia)->where('delSeriesMov2_companieKey', '=', $empresa)->where('delSeriesMov2_branchKey', '=',  $sucursal)->where('delSeriesMov2_affected', '=', 1)->GET()->unique('delSeriesMov2_id');

                            if (count($seriesVentas) >= 1) {
                                $inventarioConSerie = array_merge($inventario->toArray(), ['series' => $seriesVentas->toArray()]);
                                array_push($informacionDesglose, $inventarioConSerie);
                            } else {
                                array_push($informacionDesglose, $inventario->toArray());
                            }
                            break;

                        case 'Inv':
                            if ($inventario->assistantUnit_movement == 'Ajuste de Inventario') {
                                $seriesInv = PROC_LOT_SERIES_MOV2::WHERE('lotSeriesMov2_module', '=', $modulo)->WHERE('lotSeriesMov2_inventoryID', '=', $identificadorModulo)->WHERE('lotSeriesMov2_article', '=', $articuloReferencia)->where('lotSeriesMov2_companieKey', '=', $empresa)
                                    ->where('lotSeriesMov2_branchKey', '=',  $sucursal)->GET();
                            } else {
                                $seriesInv = PROC_DEL_SERIES_MOV::WHERE('delSeriesMov_module', '=', $modulo)->WHERE('delSeriesMov_inventoryID', '=', $identificadorModulo)->WHERE('delSeriesMov_article', '=', $articuloReferencia)->where('delSeriesMov_companieKey', '=', $empresa)
                                    ->where('delSeriesMov_branchKey', '=',  $sucursal)->GET();
                            }


                            if (count($seriesInv) >= 1) {
                                $inventarioConSerie = array_merge($inventario->toArray(), ['series' => $seriesInv->toArray()]);
                                array_push($informacionDesglose, $inventarioConSerie);
                            } else {
                                array_push($informacionDesglose, $inventario->toArray());
                            }

                            break;

                        default:
                            # code...
                            break;
                    }
                }
                if ($this->nameSerie == "No") {
                    array_push($informacionDesglose, $inventario->toArray());
                }
            }
        }

        return view('exports.reporteInventariosDesglosado', [
            'inventarios' => $informacionDesglose,
            'nameSerie' => $this->nameSerie,
            'nameExistencia' => $this->nameExistencia,
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
