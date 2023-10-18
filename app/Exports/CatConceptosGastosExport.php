<?php

namespace App\Exports;

use App\Models\catalogos\CAT_COMPANIES;
use App\Models\catalogos\CAT_EXPENSE_CONCEPTS;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CatConceptosGastosExport implements FromView, ShouldAutoSize, WithStyles
{
    
    public $nameConcept;
    public $category;
    public $group;
    public $status;

    public function __construct($nameConcept, $category, $group, $status)
    {
        $this->nameConcept = $nameConcept;
        $this->category = $category;
        $this->group = $group;
        $this->status = $status;
    }

    public function view(): View
    {
       
        $concept_collection = CAT_EXPENSE_CONCEPTS::whereExpenseConceptsConcept($this->nameConcept)->whereExpenseConceptsGroup($this->group)->whereExpenseConceptsCategory($this->category)->whereExpenseConceptsStatus($this->status)->get();

        
        return view('exports.conceptosGastos', [
            'conceptos' => $concept_collection
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
