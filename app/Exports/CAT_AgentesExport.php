<?php

namespace App\Exports;
use App\Models\catalogos\CAT_AGENTS;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Contracts\View\View;

class CAT_AgentesExport implements FromView, ShouldAutoSize, WithStyles
{
    public $keyAgente;
    public $nameAgente;
    public $category;
    public $group;
    public $status;
    

    public function __construct($keyAgente, $nameAgente, $category, $group, $status)
    {
        $this->keyAgente = $keyAgente;
        $this->nameAgente = $nameAgente;
        $this->category = $category;
        $this->group = $group;
        $this->status = $status;
    }

    public function view(): View
    {
        $agentes_collection_filtro = CAT_AGENTS::join('CAT_BRANCH_OFFICES', 'CAT_AGENTS.agents_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
        ->select('CAT_AGENTS.*', 'CAT_BRANCH_OFFICES.branchOffices_name')
        ->whereAgentKey($this->keyAgente)->whereAgentName($this->nameAgente)->whereAgentCategory($this->category)->whereAgentGroup($this->group)->whereAgentStatus($this->status)->get();
        
        return view('exports.Agentes', [
            'agentes' => $agentes_collection_filtro
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
