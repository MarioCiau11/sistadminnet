<?php

namespace App\Http\Controllers\erpNet\catalogos;

use App\Exports\CAT_ArticulosExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_ARTICLES_CATEGORY;
use App\Models\agrupadores\CAT_ARTICLES_FAMILY;
use App\Models\agrupadores\CAT_ARTICLES_GROUP;
use App\Models\catalogos\CAT_ARTICLES;
use App\Models\catalogos\CAT_ARTICLES_IMG;
use App\Models\catalogos\CAT_ARTICLES_UNITS;
use App\Models\catalogos\CAT_KIT_ARTICLES;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_UNITS;
use App\Models\CatalogosSAT\CAT_SAT_OBJETOIMP;
use App\Models\historicos\HIST_ARTICLES_PRICES;
use App\Models\modulos\helpers\PROC_ARTICLES_COST;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Image;
use stdClass;

class ArticulosController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:Artículos']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $select_categoria = $this->selectCategoria();
        $select_grupo = $this->selectGrupo();
        $select_familia = $this->selectFamilia();
        $articulos = CAT_ARTICLES::where('articles_status', '=', 'Alta')->orderBy('articles_id', 'asc')->get();
        // dd($articulos);
        $unidad = $this->getConfUnidades();

        return view('page.catalogos.Articulos.index', compact('select_categoria', 'select_grupo', 'select_familia', 'articulos', 'unidad'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $create_objImp_array = $this->selectObjetoImp();
        $select_categoria = $this->selectCategoria();
        $select_grupo = $this->selectGrupo();
        $select_familia = $this->selectFamilia();
        $select_ConfUnidades = $this->getConfUnidades();
        $select_multiUnidad = $this->getConfUnidadesByName();
        $articulos = CAT_ARTICLES::WHERE('articles_status', '=', 'Alta')->WHEREIN('articles_type', ['Normal', 'Serie', 'Servicio'])->get();

        return view('page.catalogos.Articulos.create', compact('create_objImp_array', 'select_categoria', 'select_grupo', 'select_familia', 'select_ConfUnidades', 'select_multiUnidad', 'articulos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $cat_articulos_request = $request->except('_token');
        $isKeyActiculos = CAT_ARTICLES::where('articles_key', $cat_articulos_request['keyClave'])->first();
        $unidad = $this->getConfUnidades();

        if ($isKeyActiculos) {
            $message = "La clave: " . $cat_articulos_request['keyClave'] . " ya existe en la base de datos";
            $status = false;
        } else {
            $cat_articulos = new CAT_ARTICLES();
            $cat_articulos->articles_key = $cat_articulos_request['keyClave'];
            $cat_articulos->articles_type = $cat_articulos_request['nameTipo'];
            $cat_articulos->articles_status = $cat_articulos_request['statusDG'];
            $cat_articulos->articles_descript = $cat_articulos_request['descripcion1'];
            $cat_articulos->articles_descript2 = $cat_articulos_request['descripcion2'];
            $cat_articulos->articles_unitSale = $cat_articulos_request['unidadVenta'];
            $cat_articulos->articles_transfer = $cat_articulos_request['unidadTraspaso'];
            $cat_articulos->articles_unitBuy = $cat_articulos_request['unidadCompra'];
            $cat_articulos->articles_group = $cat_articulos_request['grupo'];
            $cat_articulos->articles_category = $cat_articulos_request['categoria'];
            $cat_articulos->articles_family = $cat_articulos_request['familia'];
            $cat_articulos->articles_porcentIva = $cat_articulos_request['iva'];
            $cat_articulos->articles_retention1 = $cat_articulos_request['retencion1'];
            $cat_articulos->articles_retention2 = $cat_articulos_request['retencion2'];
            $cat_articulos->articles_listPrice1 = $cat_articulos_request['precio1'] === null ? 0 : str_replace(['$', ','], '', $cat_articulos_request['precio1']);
            $cat_articulos->articles_listPrice2 = $cat_articulos_request['precio2'] === null ? 0 : str_replace(['$', ','], '', $cat_articulos_request['precio2']);
            $cat_articulos->articles_listPrice3 = $cat_articulos_request['precio3'] === null ? 0 : str_replace(['$', ','], '', $cat_articulos_request['precio3']);
            $cat_articulos->articles_listPrice4 = $cat_articulos_request['precio4'] === null ? 0 : str_replace(['$', ','], '', $cat_articulos_request['precio4']);
            $cat_articulos->articles_listPrice5 = $cat_articulos_request['precio5'] === null ? 0 : str_replace(['$', ','], '', $cat_articulos_request['precio5']);
            $cat_articulos->articles_productService = $cat_articulos_request['prodServ'];
            $cat_articulos->articles_tariffFraction = $cat_articulos_request['fraccionArancelaria'];
            $cat_articulos->articles_customsUnit = $cat_articulos_request['unidadAduana'];
            $cat_articulos->articles_objectTax = $cat_articulos_request['objImpuesto'];
            $cat_articulos->articles_specifications = $cat_articulos_request['especifications'];

            $multiUnidadesForm = $cat_articulos_request['factorUnidad'];
            $multiUnidadesFactor = $cat_articulos_request['factor'];

            if ($cat_articulos_request['nameTipo'] == "Kit") {
                $listaArticulos = json_decode($cat_articulos_request['articulosLista'], true);
                $cat_articulos->articles_costoTotal =  str_replace(['$', ','], '', $listaArticulos['costoTotal']);
                $cat_articulos->articles_cantidadTotal = str_replace(['$', ','], '', $listaArticulos['cantidadTotal']);
            }

            try {
                $isCreate = $cat_articulos->save();
                $articlesData = $cat_articulos::latest('articles_id')->first();
                $lastArticle = $articlesData->articles_id;

                //Guardamos las imagenes del articulo
                //Obtenemos los parametros generales de la empresa
                $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();

                //Aqui validamos si tiene una ruta configurada
                if ($parametro === null || $parametro->generalParameters_filesArticles === Null || $parametro->generalParameters_filesArticles === '') {
                    $empresaRuta = session('company')->companies_routeFiles . 'Articulos';
                } else {
                    $empresaRuta = session('company')->companies_routeFiles . $parametro->generalParameters_filesArticles;
                }

                $fileImg = $request->file('files');

                if ($fileImg != null) {
                    foreach ($fileImg as $key => $imagen) {
                        if ($_FILES['files']['type'][$key] === 'image/png' || $_FILES['files']['type'][$key] === 'image/jpg' || $_FILES['files']['type'][$key] === 'image/jpeg') {
                            $tipoImagen = $imagen->getMimeType();
                            $formato = explode('/', $tipoImagen)[1];
                            $nombreImagen = $imagen->getClientOriginalName();
                            $nuevaImagen = new CAT_ARTICLES_IMG();
                            $nuevaImagen->articlesImg_article = $cat_articulos_request['keyClave'];
                            $nuevaImagen->articlesImg_path = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $cat_articulos_request['keyClave'] . '/' . $nombreImagen);
                            $nuevaImagen->articlesImg_file = $nombreImagen;
                            $rutaFinal = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $cat_articulos_request['keyClave'] . '/' . $nombreImagen);
                            $nuevaImagen->save();

                            $image_resize = Image::make($imagen);
                            $image_resize->resize(500, 500, function ($constraint) {
                                $constraint->aspectRatio();
                                $constraint->upsize();
                            });
                            $image_resize->orientate();
                            $image_resize->encode($formato);
                            Storage::disk('empresas')->put($rutaFinal, $image_resize);
                        }
                    }
                }

                if ($cat_articulos_request['nameTipo'] == "Kit") {
                    foreach ($listaArticulos as $articulo) {
                        if (is_array($articulo)) {
                            $kit_articles = new CAT_KIT_ARTICLES();
                            $kit_articles->kitArticles_article = $articulo['clave'];
                            $kit_articles->kitArticles_articleID = $lastArticle;
                            $kit_articles->kitArticles_articleDesp = $articulo['articulo'];
                            $kit_articles->kitArticles_tipo = $articulo['tipo'];
                            $kit_articles->kitArticles_costo = isset($articulo['costo']) ? $articulo['costo'] : 0;
                            $kit_articles->kitArticles_cantidad = isset($articulo['cantidad']) ? $articulo['cantidad'] : 0;
                            $kit_articles->save();
                        }
                    }
                }

                //para evitar el salto del increment
                $increment = $lastArticle != null ? $lastArticle : 0;
                DB::statement('DBCC CHECKIDENT (CAT_ARTICLES, RESEED, ' . $increment . ')');
                // {{dd ($isCreate);}}

                if ($isCreate) {
                    $message = "La clave: " . $cat_articulos_request['keyClave'] . " se registró correctamente";
                    $status = true;

                    foreach ($multiUnidadesForm as $key => $value) {
                        $cat_articulos_multiUnidades = new CAT_ARTICLES_UNITS();
                        $cat_articulos_multiUnidades->articlesUnits_article = $cat_articulos_request['keyClave'];
                        $cat_articulos_multiUnidades->articlesUnits_unit = $value;
                        $cat_articulos_multiUnidades->articlesUnits_factor = $multiUnidadesFactor[$key];
                        $cat_articulos_multiUnidades->save();
                    }
                } else {
                    $message = "No se ha podido crear el articulo " . $cat_articulos_request['keyClave'];
                    $status = false;
                }
            } catch (\Throwable $th) {
                dd($th);
                $message = "Por favor comuníquese con el administrador de sistemas ya que no se pudo crear el articulo.";
                return redirect()->route('catalogo.articulos.index')->with('message', $message)->with('status', false);
            }
        }
        return redirect()->route('catalogo.articulos.index')->with('message', $message)->with('status', $status);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $id = Crypt::decrypt($id);
            $articulo = CAT_ARTICLES::where('articles_id', '=', $id)->first();

            $multiUnidadesArticulo = CAT_ARTICLES_UNITS::where('articlesUnits_article', '=', $articulo->articles_key)->get();

            $create_objImp_array = $this->selectObjetoImp();
            $select_categoria = $this->selectCategoria();
            $select_grupo = $this->selectGrupo();
            $select_familia = $this->selectFamilia();
            $select_ConfUnidades = $this->getConfUnidades();
            $select_multiUnidad = $this->getConfUnidadesByName();

            $articulosImg = CAT_ARTICLES_IMG::WHERE('articlesImg_article', '=', $articulo->articles_key)->get();

            if ($articulo->articles_type == "Kit") {

                $kitArticles = [];
                $articulosRelacion = CAT_KIT_ARTICLES::WHERE('kitArticles_articleID', '=', $articulo->articles_id)->get();

                foreach ($articulosRelacion as $key => $componenteKit) {
                    if ($componenteKit->kitArticles_tipo == "Servicio") {
                        array_push($kitArticles, $articulosRelacion[$key]);
                    } else {
                        $kitArticlesCostoPromedio = CAT_KIT_ARTICLES::JOIN('PROC_ARTICLES_COST', 'PROC_ARTICLES_COST.articlesCost_article', '=', 'CAT_ARTICLES_KIT.kitArticles_article')->WHERE('PROC_ARTICLES_COST.articlesCost_companieKey', '=', session('company')->companies_key)->WHERE('PROC_ARTICLES_COST.articlesCost_branchKey', '=', session('sucursal')->branchOffices_key)->WHERE('CAT_ARTICLES_KIT.kitArticles_articleID', '=', $articulo->articles_id)->WHERE('PROC_ARTICLES_COST.articlesCost_article', '=', $componenteKit->kitArticles_article)->first();

                        if ($kitArticlesCostoPromedio != null) {
                            array_push($kitArticles, $kitArticlesCostoPromedio);
                        } else {
                            array_push($kitArticles, $articulosRelacion[$key]);
                        }
                    }
                }


                return view('page.catalogos.Articulos.show', compact('create_objImp_array', 'select_categoria', 'select_grupo', 'select_familia', 'select_ConfUnidades', 'articulo', 'multiUnidadesArticulo', 'select_multiUnidad', 'kitArticles', 'articulosImg'));
            } else {
                return view('page.catalogos.Articulos.show', compact('create_objImp_array', 'select_categoria', 'select_grupo', 'select_familia', 'select_ConfUnidades', 'articulo', 'multiUnidadesArticulo', 'select_multiUnidad', 'articulosImg'));
            }
        } catch (\Exception $e) {
            return redirect()->route('catalogo.articulos.index')->with('message', 'No se pudo encontrar el articulo')->with('status', false);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $id = Crypt::decrypt($id);
            $articulo = CAT_ARTICLES::where('articles_id', '=', $id)->first();
            $multiUnidadesArticulo = CAT_ARTICLES_UNITS::where('articlesUnits_article', '=', $articulo->articles_key)->get();
            $create_objImp_array = $this->selectObjetoImp();
            $select_categoria = $this->selectCategoria();
            $select_grupo = $this->selectGrupo();
            $select_familia = $this->selectFamilia();
            $select_ConfUnidades = $this->getConfUnidades();
            $select_multiUnidad = $this->getConfUnidadesByName();
            $articulos = CAT_ARTICLES::WHERE('articles_status', '=', 'Alta')->WHEREIN('articles_type', ['Normal', 'Serie', 'Servicio'])->get();

            $articulosImg = CAT_ARTICLES_IMG::WHERE('articlesImg_article', '=', $articulo->articles_key)->get();

            if ($articulo->articles_type == "Kit") {
                $kitArticles = [];
                $articulosRelacion = CAT_KIT_ARTICLES::WHERE('kitArticles_articleID', '=', $articulo->articles_id)->get();

                foreach ($articulosRelacion as $key => $componenteKit) {
                    if ($componenteKit->kitArticles_tipo == "Servicio") {
                        array_push($kitArticles, $articulosRelacion[$key]);
                    } else {
                        $kitArticlesCostoPromedio = CAT_KIT_ARTICLES::JOIN('PROC_ARTICLES_COST', 'PROC_ARTICLES_COST.articlesCost_article', '=', 'CAT_ARTICLES_KIT.kitArticles_article', 'left outer')
                        // ->WHERE('PROC_ARTICLES_COST.articlesCost_companieKey', '=', session('company')->companies_key)
                        // ->WHERE('PROC_ARTICLES_COST.articlesCost_branchKey', '=', session('sucursal')->branchOffices_key)
                        // ->wherenull('PROC_ARTICLES_COST.articlesCost_branchKey')
                        ->WHERE('CAT_ARTICLES_KIT.kitArticles_articleID', '=', $articulo->articles_id)
                        ->WHERE('PROC_ARTICLES_COST.articlesCost_article', '=', $componenteKit->kitArticles_article)->first();

                        if ($kitArticlesCostoPromedio != null) {
                            array_push($kitArticles, $kitArticlesCostoPromedio);
                        } else {
                            array_push($kitArticles, $articulosRelacion[$key]);
                        }
                    }
                }
                // dd($kitArticles);

                return view('page.catalogos.Articulos.edit', compact('create_objImp_array', 'select_categoria', 'select_grupo', 'select_familia', 'select_ConfUnidades', 'articulo', 'multiUnidadesArticulo', 'select_multiUnidad', 'kitArticles', 'articulos', 'articulosImg'));
            } else {
                return view('page.catalogos.Articulos.edit', compact('create_objImp_array', 'select_categoria', 'select_grupo', 'select_familia', 'select_ConfUnidades', 'articulo', 'multiUnidadesArticulo', 'select_multiUnidad', 'articulos', 'articulosImg'));
            }
        } catch (\Exception $e) {
            return redirect()->route('catalogo.articulos.index')->with('message', 'No se pudo encontrar el articulo')->with('status', false);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
            $cat_articulos_request = $request->except('_token');
            $cat_articulos = CAT_ARTICLES::where('articles_id', $id)->first();
            $listPriceChange = [];

            for ($i = 1; $i <= 5; $i++) {
                $historyData = new stdClass();
                $oldPrice = $cat_articulos->{"articles_listPrice" . $i};
                $newPrice = $cat_articulos_request['precio' . $i];


                if ($oldPrice != $newPrice) {
                    $historyData->articuloClave = $cat_articulos->articles_key;
                    $historyData->listPrecio = 'Lista ' . $i . '/Precio ' . $i;
                    $historyData->dateUpdate = Carbon::now()->format('Y-m-d H:i:s');
                    $historyData->newPrice = $newPrice;
                    $historyData->oldPrice = $oldPrice;


                    $listPriceChange[] = $historyData;
                }
            }

            $cat_articulos->articles_type = $cat_articulos_request['nameTipo'];
            $cat_articulos->articles_status = $cat_articulos_request['statusDG'];
            $cat_articulos->articles_descript = $cat_articulos_request['descripcion1'];
            $cat_articulos->articles_descript2 = $cat_articulos_request['descripcion2'];
            $cat_articulos->articles_unitSale = $cat_articulos_request['unidadVenta'];
            $cat_articulos->articles_transfer = $cat_articulos_request['unidadTraspaso'];
            $cat_articulos->articles_unitBuy = $cat_articulos_request['unidadCompra'];
            $cat_articulos->articles_group = $cat_articulos_request['grupo'];
            $cat_articulos->articles_category = $cat_articulos_request['categoria'];
            $cat_articulos->articles_family = $cat_articulos_request['familia'];
            $cat_articulos->articles_porcentIva = $cat_articulos_request['iva'];
            $cat_articulos->articles_retention1 = $cat_articulos_request['retencion1'];
            $cat_articulos->articles_retention2 = $cat_articulos_request['retencion2'];
            $cat_articulos->articles_listPrice1 = str_replace(['$', ','], '', $cat_articulos_request['precio1']);
            $cat_articulos->articles_listPrice2 = str_replace(['$', ','], '', $cat_articulos_request['precio2']);
            $cat_articulos->articles_listPrice3 = str_replace(['$', ','], '', $cat_articulos_request['precio3']);
            $cat_articulos->articles_listPrice4 = str_replace(['$', ','], '', $cat_articulos_request['precio4']);
            $cat_articulos->articles_listPrice5 = str_replace(['$', ','], '', $cat_articulos_request['precio5']);
            $cat_articulos->articles_productService = $cat_articulos_request['prodServ'];
            $cat_articulos->articles_objectTax = $cat_articulos_request['objImpuesto'];
            $cat_articulos->articles_tariffFraction = $cat_articulos_request['fraccionArancelaria'];
            $cat_articulos->articles_customsUnit = $cat_articulos_request['unidadAduana'];
            $cat_articulos->articles_specifications = $cat_articulos_request['especifications'];
            // dd($cat_articulos_request['especifications']);


            $multiUnidadesForm = $cat_articulos_request['factorUnidad'];
            $multiUnidadesFactor = $cat_articulos_request['factor'];

            if ($cat_articulos_request['nameTipo'] == "Kit") {
                $listaArticulos = json_decode($cat_articulos_request['articulosLista'], true);
                $cat_articulos->articles_costoTotal =  str_replace(['$', ','], '', $listaArticulos['costoTotal']);
                $cat_articulos->articles_cantidadTotal = str_replace(['$', ','], '', $listaArticulos['cantidadTotal']);
            }


            try {
                $isUpdate = $cat_articulos->update();
                //Guardamos las imagenes del articulo
                //Obtenemos los parametros generales de la empresa
                $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
                //Aqui validamos si tiene una ruta configurada

                if ($parametro === null || $parametro->generalParameters_filesArticles === Null || $parametro->generalParameters_filesArticles === '') {
                    $empresaRuta = session('company')->companies_routeFiles . 'Articulos';
                } else {
                    $empresaRuta = session('company')->companies_routeFiles . $parametro->generalParameters_filesArticles;
                }

                $fileImg = $request->file('files');

                if ($fileImg != null) {
                    foreach ($fileImg as $key => $imagen) {
                        if ($_FILES['files']['type'][$key] === 'image/png' || $_FILES['files']['type'][$key] === 'image/jpg' || $_FILES['files']['type'][$key] === 'image/jpeg') {

                            $tipoImagen = $imagen->getMimeType();
                            $formato = explode('/', $tipoImagen)[1];
                            $nombreImagen = $imagen->getClientOriginalName();
                            $nuevaImagen = new CAT_ARTICLES_IMG();
                            $nuevaImagen->articlesImg_article = $cat_articulos->articles_key;
                            $nuevaImagen->articlesImg_path = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $cat_articulos->articles_key . '/' . $nombreImagen);
                            $nuevaImagen->articlesImg_file = $nombreImagen;
                            $rutaFinal = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $cat_articulos->articles_key . '/' . $nombreImagen);
                            $nuevaImagen->save();


                            $image_resize = Image::make($imagen);
                            $image_resize->resize(500, 500, function ($constraint) {
                                $constraint->aspectRatio();
                                $constraint->upsize();
                            });
                            $image_resize->orientate();
                            $image_resize->encode($formato);
                            Storage::disk('empresas')->put($rutaFinal, $image_resize);
                        }
                    }
                }

                //Actualizamos los kit de articulos
                if ($cat_articulos_request['nameTipo'] == "Kit") {

                    foreach ($listaArticulos as $articulo) {
                        if (is_array($articulo)) {
                            if (isset($articulo['id'])) {
                                $kit_articles = CAT_KIT_ARTICLES::WHERE('kitArticles_id', '=', $articulo['id'])->first();
                            } else {
                                $kit_articles = new CAT_KIT_ARTICLES();
                            }

                            $kit_articles->kitArticles_article = $articulo['clave'];
                            $kit_articles->kitArticles_articleID = $id;
                            $kit_articles->kitArticles_articleDesp = $articulo['articulo'];
                            $kit_articles->kitArticles_tipo = $articulo['tipo'];
                            $kit_articles->kitArticles_costo = $articulo['costo'];
                            $kit_articles->kitArticles_cantidad = $articulo['cantidad'];
                            $kit_articles->save();
                        }
                    }
                }

                if ($isUpdate) {
                    $message = "El artículo se actualizó correctamente";
                    $status = true;

                    if (count($listPriceChange) > 0) {
                        foreach ($listPriceChange as $key => $value) {
                            $history = new HIST_ARTICLES_PRICES();
                            $history->histArticlesPrices_article = $value->articuloClave;
                            $history->histArticlesPrices_listPrice = $value->listPrecio;
                            $history->histArticlesPrices_dateChange = $value->dateUpdate;
                            $history->histArticlesPrices_newPrice = $value->newPrice;
                            $history->histArticlesPrices_previousPrice = $value->oldPrice;
                            $history->save();
                        }
                    }

                    CAT_ARTICLES_UNITS::where('articlesUnits_article', $cat_articulos->articles_key)->delete();

                    foreach ($multiUnidadesForm as $key => $value) {
                        $cat_articulos_multiUnidades = new CAT_ARTICLES_UNITS();
                        $cat_articulos_multiUnidades->articlesUnits_article = $cat_articulos->articles_key;
                        $cat_articulos_multiUnidades->articlesUnits_unit = $value;
                        $cat_articulos_multiUnidades->articlesUnits_factor = $multiUnidadesFactor[$key];
                        $cat_articulos_multiUnidades->save();
                    }
                } else {
                    $message = "No se ha podido actualizar el articulo";
                    $status = false;
                }
            } catch (\Throwable $th) {
                dd($th);
                $message = "Por favor, vaya con el administrador de sistemas, no se puede actualizar el articulo";
                return redirect()->route('catalogo.articulos.index')->with('message', $message)->with('status', false);
            }

            return redirect()->route('catalogo.articulos.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            return redirect()->route('catalogo.articulos.index')->with('message', 'No se pudo encontrar el articulo')->with('status', false);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $id = Crypt::decrypt($id);
            $articulo_delete = CAT_ARTICLES::where('articles_id', $id)->first();
            $articulo_delete->articles_status = 'Baja';

            $isRemoved = $articulo_delete->update();
            $status = false;
            if ($isRemoved) {
                $message = "El articulo se eliminó correctamente";
                $status = true;
            } else {
                $message = "No se ha podido eliminar el articulo";
                $status = false;
            }

            return redirect()->route('catalogo.articulos.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            return redirect()->route('catalogo.articulos.index')->with('message', 'No se pudo mostrar el articulo')->with('status', false);
        }
    }

    public function articuloAction(Request $request)
    {
        $claveArticulo = $request->claveArticulo;
        $articuloName = $request->nameArticulo;
        $category = $request->categoria;
        $group = $request->grupo;
        $family = $request->familia;
        $status = $request->status;
        $unidad = $this->getConfUnidades();
        switch ($request->input('action')) {
            case 'Búsqueda':

                $articulos_filtro = CAT_ARTICLES::whereArticlesKey($claveArticulo)
                ->whereArticlesNombre($articuloName)
                ->whereArticlesCategory($category)
                ->whereArticlesGroup($group)
                ->whereArticlesFamily($family)
                ->whereArticlesStatus($status)
                ->orderBy('articles_id', 'asc')->get();


                return redirect()->route('catalogo.articulos.index')->with('articulos_filtro', $articulos_filtro)
                ->with('claveArticulo', $claveArticulo)
                ->with('nameArticulo', $articuloName)
                ->with('categoria', $category)
                ->with('grupo', $group)
                ->with('familia', $family)
                ->with('status', $status);

                break;

            case 'Exportar excel':
                $articulos = new CAT_ArticulosExport($claveArticulo, $articuloName, $category, $group, $family, $status, $unidad);
                return Excel::download($articulos, 'Articulos.xlsx');
                break;

            default:

                break;
        }
        return redirect()->route('catalogo.articulos.index');
    }

    public function selectObjetoImp()
    {
        $unidades = [];
        $unidades_key_sat_collection = CAT_SAT_OBJETOIMP::all();
        $unidades_key_sat_array = $unidades_key_sat_collection->toArray();

        foreach ($unidades_key_sat_array as $key => $value) {
            $unidades[$value['c_ObjetoImp']] = $value['c_ObjetoImp'] . ' - ' . $value['descripcion'];
        }
        return $unidades;
    }



    public function selectCategoria()
    {
        $categoria_select = [];
        $categoria_collection = CAT_ARTICLES_CATEGORY::where('categoryArticle_status', 'Alta')->get();
        $categoria_array = $categoria_collection->toArray();

        foreach ($categoria_array as $key => $value) {
            $categoria_select[$value['categoryArticle_name']] = $value['categoryArticle_name'];
        }
        return $categoria_select;
    }

    public function selectGrupo()
    {
        $grupo_select = [];
        $grupo_collection = CAT_ARTICLES_GROUP::where('groupArticle_status', 'Alta')->get();
        $grupo_array = $grupo_collection->toArray();
        foreach ($grupo_array as $key => $value) {
            $grupo_select[$value['groupArticle_name']] = $value['groupArticle_name'];
        }
        return $grupo_select;
    }

    public function selectFamilia()
    {
        $familia_select = [];
        $familia_collection = CAT_ARTICLES_FAMILY::where('familyArticle_status', 'Alta')->get();
        $familia_array = $familia_collection->toArray();
        foreach ($familia_array as $key => $value) {
            $familia_select[$value['familyArticle_name']] = $value['familyArticle_name'];
        }
        return $familia_select;
    }

    public function getConfUnidades()
    {
        $unidades = [];
        $unidades_collection = CONF_UNITS::all();

        $unidades_array = $unidades_collection->toArray();

        foreach ($unidades_array as $key => $value) {
            $unidades[$value['units_id']] = $value['units_unit'];
        }
        return $unidades;
    }

    public function getConfUnidadesByName()
    {
        $unidades = [];
        $unidades_collection = CONF_UNITS::all();

        $unidades_array = $unidades_collection->toArray();

        foreach ($unidades_array as $key => $value) {
            $unidades[$value['units_unit']] = $value['units_unit'];
        }
        return $unidades;
    }

    public function getArticle()
    {
        $articleLast = CAT_ARTICLES::count();
        $getId = $articleLast + 1;
        return response()->json(['articles_id' => $getId]);
    }

    public function getRelacionArticulos(Request $request)
    {
        $articleID = $request->articulo;
        try {
            $kitArticle = CAT_ARTICLES::WHERE('articles_key', '=', $articleID)->first();
            $kitRelacion = CAT_KIT_ARTICLES::JOIN('PROC_ARTICLES_COST', 'PROC_ARTICLES_COST.articlesCost_article', '=', 'CAT_ARTICLES_KIT.kitArticles_article')->WHERE('PROC_ARTICLES_COST.articlesCost_companieKey', '=', session('company')->companies_key)->WHERE('PROC_ARTICLES_COST.articlesCost_branchKey', '=', session('sucursal')->branchOffices_key)->WHERE('CAT_ARTICLES_KIT.kitArticles_articleID', '=', $kitArticle->articles_id)->get()->toArray();

            return response()->json(['data' => $kitRelacion, 'status' => true], 200);
        } catch (\Throwable $th) {
            return response()->json(['data' => $th, 'status' => false], 500);
        }
    }

    public function getLastCosto(Request $request)
    {
        $costo = PROC_ARTICLES_COST::where('articlesCost_article', '=', $request->articulo)->where('articlesCost_companieKey', '=', session('company')->companies_key)->where('articlesCost_branchKey', '=', session('sucursal')->branchOffices_key)->first();

        return response()->json($costo);
    }

    public function eliminarImagen(Request $request)
    {
        $idImagen = $request->idImg;
        $delete = CAT_ARTICLES_IMG::WHERE('articlesImg_id', '=', $idImagen)->delete();

        if ($delete) {
            return response()->json(['status' => $delete, 'mensaje' => 'Imagen eliminada de la base de datos'], 200);
        } else {
            return response()->json(['status' => $delete, 'mensaje' => 'Imagen no eliminada de la base de datos'], 404);
        }
    }

    public function deleteKitArticle(Request $request)
    {
        $id = $request->id;
        $delete = CAT_KIT_ARTICLES::WHERE('kitArticles_id', '=', $id)->delete();

        if ($delete) {
            return response()->json(['status' => 200], 200);
        } else {
            return response()->json(['status' => 404], 404);
        }
    }
}
