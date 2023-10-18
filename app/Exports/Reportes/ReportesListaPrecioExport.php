<?php

namespace App\Exports\Reportes;

use App\Models\catalogos\CAT_ARTICLES;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportesListaPrecioExport implements FromView, ShouldAutoSize, WithStyles, WithDrawings, WithEvents, WithColumnWidths
{
    public $listaPrecio;
    public $nameFoto;
   
    public function __construct($listaPrecio, $nameFoto)
    {
        $this->listaPrecio = $listaPrecio;
        $this->nameFoto = $nameFoto;

    }

    public function collection (){
        $precio = CAT_ARTICLES::join('CAT_ARTICLES_IMG', 'CAT_ARTICLES.articles_key', '=', 'CAT_ARTICLES_IMG.articlesImg_article', 'left outer')
        ->whereArticlesPriceList($this->listaPrecio)
        ->where('articles_status', 'Alta')
        ->orderBy('articles_id', 'ASC')
        ->orderBy('articles_category', 'ASC')
        ->get();

        
        $collectionPrecio = collect($precio);
        $categoriaArticulo = $collectionPrecio->unique('articles_category')->unique()->all();

        $categoriaPorArticulo = [];
        foreach ($categoriaArticulo as $categoria) {
            $precios = CAT_ARTICLES::join('CAT_ARTICLES_IMG', 'CAT_ARTICLES.articles_key', '=', 'CAT_ARTICLES_IMG.articlesImg_article', 'left outer')
                ->whereArticlesCategory($categoria['articles_category'])
                ->whereArticlesPriceList($this->listaPrecio)
                ->where('articles_status', 'Alta')
                ->orderBy('articles_id', 'asc')
                ->orderBy('articles_descript', 'ASC')
                ->get()->unique('articles_key')->sortBy('articles_key');

            //si no tienen categoría, se les asigna la categoría "Sin categoría"
            if ($categoria['articles_category'] == null) {
                $categoria['articles_category'] = "SIN CATEGORÍA";
            }

            $arrayCategoria = $categoria->toArray();
            $categoriaPorArticulo[] = array_merge($arrayCategoria, ['precios' => $precios->toArray()]);
        }

        //tenemos que poner la ruta completa de la imagen, porque si no, no la encuentra

        $defaultImage = storage_path("app/empresas/images.png");


        foreach ($categoriaPorArticulo as $key => $value) {
            foreach ($value['precios'] as $key2 => $value2) {
                if ($value2['articlesImg_article'] != null) {
                    $image = storage_path("app/empresas/" . $value['articlesImg_path']);

                    if ($image === null) {
                        $image = $defaultImage;
                    }
                } else {
                    $image = $defaultImage;
                    
                }
                
            }
        }

        return [
            'categoriaArticulo' => $categoriaPorArticulo,
            'listaPrecio' => $this->listaPrecio,
            'nameFoto' => $this->nameFoto,
            'image' => $image,
        ];
    }

    public function view(): View
    {
        return view('exports.reporteListaPrecios', $this->collection());
    }

    public function drawings(){
        $drawsArray = [];
        //Asignamos la imagen de la empresa en el excel
         if (session('company')->companies_logo === null || session('company')->companies_logo === '') {
            $logoFile = null;
        } else {
            $logoFile = storage_path('app/empresas/' . session('company')->companies_logo);
        }

        if ($logoFile == null) {
            $logoFile = storage_path('app/empresas/default.png');

        }

        $drawing = new Drawing();
        $drawing->setPath($logoFile);
        $drawing->setWidth(50);
        $drawing->setHeight(50);
        $drawing->setCoordinates('A1');
        array_push($drawsArray, $drawing);


        
       $informacion = $this->collection();
       if($informacion['nameFoto'] == "Si"){
       $contador = 4;
       foreach ($informacion['categoriaArticulo'] as $categoriaList) {
            foreach ($categoriaList["precios"] as $articuloList) {
                if($categoriaList["articles_category"] == $articuloList["articles_category"] ||  $articuloList["articles_category"] == null){
                     $drawing = new Drawing();
                    if($articuloList['articlesImg_path'] != null){
                        $ruta = storage_path("app/empresas/" . $articuloList['articlesImg_path']);
                        //Verificar si existe la imagen
                        if(!file_exists($ruta)){
                            $ruta = $informacion['image'];
                        }
                        $drawing->setPath($ruta);
                        $drawing->setWidth(50);
                        $drawing->setHeight(50);
                        $drawing->setCoordinates('A'.($contador++));
                    }else{
                        $drawing->setPath($informacion['image']);
                        $drawing->setWidth(50);
                        $drawing->setHeight(50);
                        $drawing->setCoordinates('A'.($contador++));
                    }
                    array_push($drawsArray, $drawing);
                }
               
            }
            $contador = $contador + 2;
            }
        }
        return $drawsArray;
    }

     public function columnWidths(): array
    {
        return [
            'A' => 10,           
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
            $rows =  $event->sheet->getDelegate()->getHighestRow();
            for ($conta=1; $conta <= $rows -1 ; $conta++) { 
                        $event->sheet->getDelegate()->getRowDimension($conta)->setRowHeight(50);
                }
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
