<?php

namespace App\Http\Controllers\erpNet\catalogos;

use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_ARTICLES_FAMILY;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class AgrupadorFamiliaController extends Controller
{


    public function indexCategoria(Request $request)
    {

        $tipo = $request['tipo'];

        switch ($tipo) {

            case 'Articulo':
                $familias = CAT_ARTICLES_FAMILY::all();
                return view('page.catalogos.articulos.agrupadores.familia.index', compact('familias'));
                break;
            default:
                # code...
                break;
        }
    }

    public function createFamilia(Request $request)
    {

        $tipo = $request['tipo'];

        switch ($tipo) {
            case 'Articulo':
                return view('page.catalogos.Articulos.agrupadores.Familia.create');
                break;
            default:
                # code...
                break;
        }
    }

    public function editFamilia(Request $request, $id)
    {

        $tipo = $request['tipo'];
        switch ($tipo) {
            case 'Articulo':
                try {
                    $id = Crypt::decrypt($id);
                    $familia_edit = CAT_ARTICLES_FAMILY::where('familyArticle_id', '=', $id)->first();
                    return view('page.catalogos.articulos.agrupadores.Familia.edit', compact('familia_edit'));
                } catch (\Exception $e) {
                    return redirect()->route('familiaIndex', ['tipo' => 'Articulo'])->with('message', 'Error al mostrar la familia')->with('status', false);
                }
                break;
            default:
                # code...
                break;
        }
    }

    public function updateFamilia(Request $request, $id)
    {
        // dd($request->all());
        $tipo = $request['tipo'];

        switch ($tipo) {
            case 'Articulo':
                try {
                    $id = Crypt::decrypt($id);
                    $familia_request = $request->except('_token');
                    $familia = CAT_ARTICLES_FAMILY::where('familyArticle_id', '=', $id)->first();
                    $familia->familyArticle_name = $familia_request['nameNombre'];
                    $familia->familyArticle_status = $familia_request['statusDG'];

                    try {
                        $isCategory = $familia->update();
                        if ($isCategory) {
                            $message = "La familia se ha guardado correctamente";
                            $status = true;
                        } else {
                            $message = "La familia no se ha guardado correctamente";
                            $status = false;
                        }
                    } catch (\Exception $e) {
                        dd($e);
                        $message = "Error al guardar la familia";
                        $status = false;
                    }

                    return redirect()->route('familiaIndex', ['tipo' => 'Articulo'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('familiaIndex', ['tipo' => 'Articulo'])->with('message', 'Error al mostrar la categoria')->with('status', false);
                }
                break;
            default:
                # code...
                break;
        }
    }

    public function deleteFamilia(Request $request)
    {

        // dd($id);
        // dd($request->all());
        $tipo = $request['tipo'];

        switch ($tipo) {
            case 'Articulo':
                try {
                    $id = Crypt::decrypt($request['categoria']);
                    $familia_destroy = CAT_ARTICLES_FAMILY::where('familyArticle_id', '=', $id)->first();

                    $familia_destroy['familyArticle_status'] = 'Baja';
                    $isFamily = $familia_destroy->update();
                    if ($isFamily) {
                        $message = "La familia se ha eliminado correctamente";
                        $status = true;
                    } else {
                        $message = "La familia no se ha eliminado correctamente";
                        $status = false;
                    }

                    return redirect()->route('familiaIndex', ['tipo' => 'Articulo'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('familiaIndex', ['tipo' => 'Articulo'])->with('message', 'Error al mostrar la familia')->with('status', false);
                }
                break;

            default:
                # code...
                break;
        }
    }



    public function agrupadorFamiliaAgregar(Request $request)
    {

        $familia_request = $request->except('_token'); //rechazamos el token que nos pasa el formulario
        $tipo = $familia_request['tipo'];

        switch ($tipo) {
            case 'Articulo':
                $familia = new CAT_ARTICLES_FAMILY();
                $familia->familyArticle_name = $familia_request['nameNombre'];
                $familia->familyArticle_status = $familia_request['statusDG'];

                try {
                    $isGroup = $familia->save();
                    if ($isGroup) {
                        $message = "La familia se ha guardado correctamente";
                        $status = true;
                    } else {
                        $message = "La familia no se ha guardado correctamente";
                        $status = false;
                    }
                } catch (\Exception $e) {
                    dd($e);
                    $message = "Error al guardar la familia";
                    $status = false;
                }

                return redirect()->route('catalogo.articulos.index')->with('status', $status)->with('message', $message);
                break;

            default:
                # code...
                break;
        }
    }

    public function getFamilia(Request $request)
    {
        $request_familia = $request->except('_token');
        $tipo = $request_familia['tipo'];

        switch ($tipo) {
            case 'Articulo':
                $idLast = CAT_ARTICLES_FAMILY::count();
                $familia = $idLast + 1;
                return response()->json(['familia' => $familia]);
                break;
            default:
                # code...
                break;
        }
    }
}
