<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesListaPrecioExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_ARTICLES;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Termwind\Components\Dd;

class ReportesListaPreciosController extends Controller
{
    public function index()
    {
        $articulos = CAT_ARTICLES::where('articles_status', 'Alta')
            ->where('articles_status', 'Alta')
            ->orderBy('articles_category', 'ASC')
            ->get();
        return view('page.Reportes.Ventas.indexListaPrecios', compact('articulos'));
    }


    public function reportesListaPreciosAction(Request $request)
    {

        $listaPrecio = $request->listaPrecio;
        $nameFoto = $request->nameFoto;

        switch ($request->input('action')) {
            case 'Búsqueda':
                $reportes_collection_filtro = CAT_ARTICLES::whereArticlesPriceList($listaPrecio)
                    ->orderBy('articles_category', 'ASC')->get();

                $reportes_filtro_array = $reportes_collection_filtro->toArray();

                return redirect()->route('vista.reportes.listaPrecios')->with('reportes_filtro_array', $reportes_filtro_array)
                    ->with('listaPrecio', $listaPrecio)
                    ->with('nameFoto', $nameFoto);
                break;
            case 'Exportar excel':
                $precio = new ReportesListaPrecioExport($listaPrecio, $nameFoto);
                return Excel::download($precio, 'Lista de precios.xlsx');
                break;

            case 'Exportar PDF':
                $precio = CAT_ARTICLES::join('CAT_ARTICLES_IMG', 'CAT_ARTICLES.articles_key', '=', 'CAT_ARTICLES_IMG.articlesImg_article', 'left outer')
                    ->whereArticlesPriceList($listaPrecio)
                    ->where('articles_status', 'Alta')
                    ->orderBy('articles_id', 'ASC')
                    ->orderBy('articles_category', 'ASC')
                    ->get();

                if ($precio->isEmpty()) {
                    return redirect()->route('vista.reportes.listaPrecios')->with('message', 'no se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
                } else {


                    if ($nameFoto === 'Si') {
                        //ahora hacemos una consulta para obtener los datos de la categoría 
                        $articulos = CAT_ARTICLES::join('CAT_ARTICLES_IMG', 'CAT_ARTICLES.articles_key', '=', 'CAT_ARTICLES_IMG.articlesImg_article', 'left outer')
                            ->whereArticlesPriceList($listaPrecio)
                            ->orderBy('articles_id', 'asc')
                            ->orderBy('articles_category', 'ASC')
                            ->get();

                        $collectionPrecio = collect($articulos);
                        $categoriaArticulo = $collectionPrecio->unique('articles_category')->unique()->all();

                        // dd($articulos, $categoriaArticulo);

                        $categoriaPorArticulo = [];
                        foreach ($categoriaArticulo as $categoria) {
                            $precios = CAT_ARTICLES::join('CAT_ARTICLES_IMG', 'CAT_ARTICLES.articles_key', '=', 'CAT_ARTICLES_IMG.articlesImg_article', 'left outer')
                                ->whereArticlesCategory($categoria['articles_category'])
                                ->whereArticlesPriceList($listaPrecio)
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


                        $defaultImage = Storage::disk('empresas')->get('images.png');

                        foreach ($categoriaPorArticulo as $key => $value) {
                            foreach ($value['precios'] as $key2 => $value2) {
                                if ($value2['articlesImg_article'] != null) {
                                    $image = Storage::disk('empresas')->get($value2['articlesImg_path']);

                                    if ($image === null) {
                                        $image = $defaultImage;
                                    }
                                } else {
                                    $image = $defaultImage;
                                }
                            }
                        }



                        //buscamos si el articulo tiene imagen
                    } else {
                        $articulos = CAT_ARTICLES::whereArticlesPriceList($listaPrecio)
                            ->where('articles_status', 'Alta')
                            ->orderBy('articles_id', 'asc')
                            ->orderBy('articles_category', 'ASC')
                            ->get();

                        $collectionPrecio = collect($articulos);
                        $categoriaArticulo = $collectionPrecio->unique('articles_category')->unique()->all();

                        $categoriaPorArticulo = [];
                        foreach ($categoriaArticulo as $categoria) {
                            $precios = CAT_ARTICLES::whereArticlesCategory($categoria['articles_category'])
                                ->whereArticlesPriceList($listaPrecio)
                                ->where('articles_status', 'Alta')

                                ->orderBy('articles_id', 'asc')
                                ->orderBy('articles_descript', 'ASC')
                                ->get();

                            //si no tienen categoría, se les asigna la categoría "Sin categoría"
                            if ($categoria['articles_category'] == null) {
                                $categoria['articles_category'] = "SIN CATEGORÍA";
                            } else {
                                $categoria['articles_category'] = $categoria['articles_category'];
                            }

                            $arrayCategoria = $categoria->toArray();
                            $categoriaPorArticulo[] = array_merge($arrayCategoria, ['precios' => $precios->toArray()]);
                        }
                    }




                    if (session('company')->companies_logo === null || session('company')->companies_logo === '') {
                        $logoFile = null;
                    } else {
                        $logoFile = Storage::disk('empresas')->get(session('company')->companies_logo);
                    }

                    if ($logoFile == null) {
                        $logoFile = Storage::disk('empresas')->get('default.png');

                        if ($logoFile == null) {
                            $logoBase64 = '';
                        } else {
                            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoFile);
                        }
                    } else {
                        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoFile);
                    }
                    $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();


                    // dd($categoriaPorArticulo, $listaPrecio, $logoBase64, $parametro, $nameFoto, $image);
                    if ($nameFoto == 'Si') {
                        $pdf = PDF::loadView('page.Reportes.Ventas.listaPrecios', [
                            'categoriaArticulo' => $categoriaPorArticulo,
                            'listaPrecio' => $listaPrecio,
                            'logo' => $logoBase64,
                            'parametro' => $parametro,
                            'nameFoto' => $nameFoto,
                            'image' => $image,
                        ]);
                    } else {
                        $pdf = PDF::loadView('page.Reportes.Ventas.listaPrecios', [
                            'categoriaArticulo' => $categoriaPorArticulo,
                            'listaPrecio' => $listaPrecio,
                            'logo' => $logoBase64,
                            'parametro' => $parametro,
                            'nameFoto' => $nameFoto,


                        ]);
                    }



                    return $pdf->stream();
                }
                break;
        }
    }
}
