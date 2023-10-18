<?php

namespace App\Http\Controllers\erpNet\catalogos;

use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_AGENTS_CATEGORY;
use App\Models\agrupadores\CAT_ARTICLES_CATEGORY;
use App\Models\agrupadores\CAT_CUSTOMERS_CATEGORY;
use App\Models\agrupadores\CAT_EXPENSE_CONCEPTS_CATEGORY;
use App\Models\agrupadores\CAT_PROVIDER_CATEGORY;
use App\Models\agrupadores\CAT_VEHICLES_CATEGORY;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class AgrupadorCategoriaController extends Controller
{


    public function indexCategoria(Request $request)
    {

        $tipo = $request['tipo'];

        switch ($tipo) {
            case 'Agente':
                $categorias = CAT_AGENTS_CATEGORY::all();
                return view('page.catalogos.agentes.agrupadores.Categoria.index', compact('categorias'));
                break;
            case 'Proveedor':
                $proveedores = CAT_PROVIDER_CATEGORY::all();
                return view('page.catalogos.proveedores.agrupadores.Categoria.index', compact('proveedores'));
                break;
            case 'cptGastos':
                $categorias = CAT_EXPENSE_CONCEPTS_CATEGORY::all();
                return view('page.catalogos.conceptoGastos.agrupadores.Categoria.index', compact('categorias'));
                break;
            case 'Cliente':
                $categorias = CAT_CUSTOMERS_CATEGORY::all();
                return view('page.catalogos.clientes.agrupadores.Categoria.index', compact('categorias'));
                break;
            case 'Articulo':
                $categorias = CAT_ARTICLES_CATEGORY::all();
                return view('page.catalogos.articulos.agrupadores.Categoria.index', compact('categorias'));
                break;
            case 'Vehiculo':
                $categorias = CAT_VEHICLES_CATEGORY::all();
                return view('page.catalogos.vehiculos.agrupadores.Categoria.index', compact('categorias'));
                break;

            default:
                # code...
                break;
        }
    }
    public function createCategoria(Request $request)
    {

        $tipo = $request['tipo'];

        switch ($tipo) {
            case 'Agente':
                return view('page.catalogos.Agentes.Agrupadores.Categoria.create');
                break;
            case 'Proveedor':
                return view('page.catalogos.proveedores.agrupadores.Categoria.create');
                break;
            case 'cptGastos':
                return view('page.catalogos.conceptoGastos.agrupadores.Categoria.create');
                break;
            case 'Cliente':
                return view('page.catalogos.Clientes.Agrupadores.Categoria.create');
                break;

            case 'Articulo':
                return view('page.catalogos.Articulos.agrupadores.Categoria.create');
                break;

            case 'Vehiculo':
                return view('page.catalogos.Vehiculos.agrupadores.Categoria.create');
                break;
            default:
                # code...
                break;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function editCategoria(Request $request, $id)
    {

        $tipo = $request['tipo'];
        switch ($tipo) {
            case 'Agente':
                try {
                    $id = Crypt::decrypt($id);
                    $categoria_edit = CAT_AGENTS_CATEGORY::where('categoryAgents_id', '=', $id)->first();
                    return view('page.catalogos.agentes.agrupadores.Categoria.edit', compact('categoria_edit'));
                } catch (\Exception $e) {
                    return redirect()->route('categoriaIndex', ['tipo' => 'Agente'])->with('message', 'Error al mostrar la categoría')->with('status', false);
                }
                break;

            case 'Proveedor':
                try {
                    $id = Crypt::decrypt($id);
                    $categoria_edit = CAT_PROVIDER_CATEGORY::where('categoryProvider_id', '=', $id)->first();
                    return view('page.catalogos.proveedores.agrupadores.Categoria.edit', compact('categoria_edit'));
                } catch (\Exception $e) {
                    return redirect()->route('categoriaIndex', ['tipo' => 'Proveedor'])->with('message', 'Error al mostrar la categoría')->with('status', false);
                }
                break;

            case 'cptGastos':
                try {
                    $id = Crypt::decrypt($id);
                    $categoria_edit = CAT_EXPENSE_CONCEPTS_CATEGORY::where('categoryExpenseConcept_id', '=', $id)->first();
                    return view('page.catalogos.conceptoGastos.agrupadores.Categoria.edit', compact('categoria_edit'));
                } catch (\Exception $e) {
                    return redirect()->route('categoriaIndex', ['tipo' => 'cptGastos'])->with('message', 'Error al mostrar la categoría')->with('status', false);
                }
                break;

            case 'Cliente':
                try {
                    $id = Crypt::decrypt($id);
                    $categoria_edit = CAT_CUSTOMERS_CATEGORY::where('categoryCostumer_id', '=', $id)->first();
                    return view('page.catalogos.clientes.agrupadores.Categoria.edit', compact('categoria_edit'));
                } catch (\Exception $e) {
                    return redirect()->route('categoriaIndex', ['tipo' => 'Cliente'])->with('message', 'Error al mostrar la categoría')->with('status', false);
                }
                break;

            case 'Articulo':
                try {
                    $id = Crypt::decrypt($id);
                    $categoria_edit = CAT_ARTICLES_CATEGORY::where('categoryArticle_id', '=', $id)->first();
                    return view('page.catalogos.articulos.agrupadores.Categoria.edit', compact('categoria_edit'));
                } catch (\Exception $e) {
                    return redirect()->route('categoriaIndex', ['tipo' => 'Articulo'])->with('message', 'Error al mostrar la categoría')->with('status', false);
                }
                break;


            case 'Vehiculo':
                try {
                    $id = Crypt::decrypt($id);
                    $categoria_edit = CAT_VEHICLES_CATEGORY::where('categoryVehicle_id', '=', $id)->first();
                    return view('page.catalogos.vehiculos.agrupadores.Categoria.edit', compact('categoria_edit'));
                } catch (\Exception $e) {
                    return redirect()->route('categoriaIndex', ['tipo' => 'Vehiculo'])->with('message', 'Error al mostrar la categoría')->with('status', false);
                }
                break;
            default:
                # code...
                break;
        }
    }

    public function updateCategoria(Request $request, $id)
    {
        // dd($request->all());
        $tipo = $request['tipo'];

        switch ($tipo) {
            case 'Agente':
                try {
                    $id = Crypt::decrypt($id);
                    $categoria_request = $request->except('_token');
                    $categoria = CAT_AGENTS_CATEGORY::where('categoryAgents_id', '=', $id)->first();
                    $categoria->categoryAgents_name = $categoria_request['nameNombre'];
                    $categoria->categoryAgents_status = $categoria_request['statusDG'];

                    try {
                        $isCategory = $categoria->update();
                        if ($isCategory) {
                            $message = "La categoría se ha guardado correctamente";
                            $status = true;
                        } else {
                            $message = "La categoría no se ha guardado correctamente";
                            $status = false;
                        }
                    } catch (\Exception $e) {
                        dd($e);
                        $message = "Error al guardar la categoría";
                        $status = false;
                    }

                    return redirect()->route('categoriaIndex', ['tipo' => 'Agente'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('categoriaIndex', ['tipo' => 'Agente'])->with('message', 'Error al mostrar la categoría')->with('status', false);
                }
                break;

            case 'Proveedor':
                try {
                    $id = Crypt::decrypt($id);
                    $categoria_request = $request->except('_token');
                    $categoria = CAT_PROVIDER_CATEGORY::where('categoryProvider_id', '=', $id)->first();
                    $categoria->categoryProvider_name = $categoria_request['nameNombre'];
                    $categoria->categoryProvider_status = $categoria_request['statusDG'];

                    try {
                        $isCategory = $categoria->update();
                        if ($isCategory) {
                            $message = "La categoria se ha guardado correctamente";
                            $status = true;
                        } else {
                            $message = "La categoria no se ha guardado correctamente";
                            $status = false;
                        }
                    } catch (\Exception $e) {
                        dd($e);
                        $message = "Error al guardar la categoría";
                        $status = false;
                    }

                    return redirect()->route('categoriaIndex', ['tipo' => 'Proveedor'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('categoriaIndex', ['tipo' => 'Proveedor'])->with('message', 'Error al mostrar la categoría')->with('status', false);
                }
                break;

            case 'cptGastos':
                try {
                    $id = Crypt::decrypt($id);
                    $categoria_request = $request->except('_token');
                    $categoria = CAT_EXPENSE_CONCEPTS_CATEGORY::where('categoryExpenseConcept_id', '=', $id)->first();
                    $categoria->categoryExpenseConcept_name = $categoria_request['nameNombre'];
                    $categoria->categoryExpenseConcept_status = $categoria_request['statusDG'];

                    try {
                        $isCategory = $categoria->update();
                        if ($isCategory) {
                            $message = "La categoria se ha guardado correctamente";
                            $status = true;
                        } else {
                            $message = "La categoria no se ha guardado correctamente";
                            $status = false;
                        }
                    } catch (\Exception $e) {
                        dd($e);
                        $message = "Error al guardar la categoría";
                        $status = false;
                    }

                    return redirect()->route('categoriaIndex', ['tipo' => 'cptGastos'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('categoriaIndex', ['tipo' => 'cptGastos'])->with('message', 'Error al mostrar la categoría')->with('status', false);
                }
                break;

            case 'Cliente':
                try {
                    $id = Crypt::decrypt($id);
                    $categoria_request = $request->except('_token');
                    $categoria = CAT_CUSTOMERS_CATEGORY::where('categoryCostumer_id', '=', $id)->first();
                    $categoria->categoryCostumer_name = $categoria_request['nameNombre'];
                    $categoria->categoryCostumer_status = $categoria_request['statusDG'];

                    try {
                        $isCategory = $categoria->update();
                        if ($isCategory) {
                            $message = "La categoría se ha guardado correctamente";
                            $status = true;
                        } else {
                            $message = "La categoría no se ha guardado correctamente";
                            $status = false;
                        }
                    } catch (\Exception $e) {
                        dd($e);
                        $message = "Error al guardar la categoría";
                        $status = false;
                    }

                    return redirect()->route('categoriaIndex', ['tipo' => 'Cliente'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('categoriaIndex', ['tipo' => 'Cliente'])->with('message', 'Error al mostrar la categoría')->with('status', false);
                }
                break;

            case 'Articulo':
                try {
                    $id = Crypt::decrypt($id);
                    $categoria_request = $request->except('_token');
                    $categoria = CAT_ARTICLES_CATEGORY::where('categoryArticle_id', '=', $id)->first();
                    $categoria->categoryArticle_name = $categoria_request['nameNombre'];
                    $categoria->categoryArticle_status = $categoria_request['statusDG'];

                    try {
                        $isCategory = $categoria->update();
                        if ($isCategory) {
                            $message = "La categoría se ha guardado correctamente";
                            $status = true;
                        } else {
                            $message = "La categoría no se ha guardado correctamente";
                            $status = false;
                        }
                    } catch (\Exception $e) {
                        dd($e);
                        $message = "Error al guardar la categoría";
                        $status = false;
                    }

                    return redirect()->route('categoriaIndex', ['tipo' => 'Articulo'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('categoriaIndex', ['tipo' => 'Articulo'])->with('message', 'Error al mostrar la categoría')->with('status', false);
                }
                break;

            case 'Vehiculo':
                try {
                    $id = Crypt::decrypt($id);
                    $categoria_request = $request->except('_token');
                    $categoria = CAT_VEHICLES_CATEGORY::where('categoryVehicle_id', '=', $id)->first();
                    $categoria->categoryVehicle_name = $categoria_request['nameNombre'];
                    $categoria->categoryVehicle_status = $categoria_request['statusDG'];

                    try {
                        $isCategory = $categoria->update();
                        if ($isCategory) {
                            $message = "La categoría se ha guardado correctamente";
                            $status = true;
                        } else {
                            $message = "La categoría no se ha guardado correctamente";
                            $status = false;
                        }
                    } catch (\Exception $e) {
                        dd($e);
                        $message = "Error al guardar la categoría";
                        $status = false;
                    }

                    return redirect()->route('categoriaIndex', ['tipo' => 'Vehiculo'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('categoriaIndex', ['tipo' => 'Vehiculo'])->with('message', 'Error al mostrar la categoría')->with('status', false);
                }
                break;
            default:
                # code...
                break;
        }
    }

    public function deleteCategoria(Request $request)
    {

        // dd($id);
        // dd($request->all());
        $tipo = $request['tipo'];

        switch ($tipo) {
            case 'Agente':
                try {
                    $id = Crypt::decrypt($request['categoria']);
                    $categoria_destroy = CAT_AGENTS_CATEGORY::where('categoryAgents_id', '=', $id)->first();

                    $categoria_destroy['categoryAgents_status'] = 'Baja';
                    $isCategory = $categoria_destroy->update();
                    if ($isCategory) {
                        $message = "La categoría se ha eliminado correctamente";
                        $status = true;
                    } else {
                        $message = "La categoría no se ha eliminado correctamente";
                        $status = false;
                    }

                    return redirect()->route('categoriaIndex', ['tipo' => 'Agente'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('categoriaIndex', ['tipo' => 'Agente'])->with('message', 'Error al mostrar la categoría')->with('status', false);
                }
                break;

            case 'Proveedor':
                try {
                    $id = Crypt::decrypt($request['categoria']);
                    $categoria_destroy = CAT_PROVIDER_CATEGORY::where('categoryProvider_id', '=', $id)->first();

                    $categoria_destroy['categoryProvider_status'] = 'Baja';
                    $isCategory = $categoria_destroy->update();
                    if ($isCategory) {
                        $message = "La categoría se ha eliminado correctamente";
                        $status = true;
                    } else {
                        $message = "La categoría no se ha eliminado correctamente";
                        $status = false;
                    }

                    return redirect()->route('categoriaIndex', ['tipo' => 'Proveedor'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('categoriaIndex', ['tipo' => 'Proveedor'])->with('message', 'Error al mostrar la categoría')->with('status', false);
                }
                break;

            case 'cptGastos':
                try {
                    $id = Crypt::decrypt($request['categoria']);
                    $categoria_destroy = CAT_EXPENSE_CONCEPTS_CATEGORY::where('categoryExpenseConcept_id', '=', $id)->first();

                    $categoria_destroy['categoryExpenseConcept_status'] = 'Baja';
                    $isCategory = $categoria_destroy->update();
                    if ($isCategory) {
                        $message = "La categoría se ha eliminado correctamente";
                        $status = true;
                    } else {
                        $message = "La categoría no se ha eliminado correctamente";
                        $status = false;
                    }

                    return redirect()->route('categoriaIndex', ['tipo' => 'cptGastos'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('categoriaIndex', ['tipo' => 'cptGastos'])->with('message', 'Error al mostrar la categoria')->with('status', false);
                }
                break;

            case 'Cliente':
                try {
                    $id = Crypt::decrypt($request['categoria']);
                    $categoria_destroy = CAT_CUSTOMERS_CATEGORY::where('categoryCostumer_id', '=', $id)->first();

                    $categoria_destroy['categoryCostumer_status'] = 'Baja';
                    $isCategory = $categoria_destroy->update();
                    if ($isCategory) {
                        $message = "La categoría se ha eliminado correctamente";
                        $status = true;
                    } else {
                        $message = "La categoría no se ha eliminado correctamente";
                        $status = false;
                    }

                    return redirect()->route('categoriaIndex', ['tipo' => 'Cliente'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('categoriaIndex', ['tipo' => 'Cliente'])->with('message', 'Error al mostrar la categoría')->with('status', false);
                }
                break;

            case 'Articulo':
                try {
                    $id = Crypt::decrypt($request['categoria']);
                    $categoria_destroy = CAT_ARTICLES_CATEGORY::where('categoryArticle_id', '=', $id)->first();

                    $categoria_destroy['categoryArticle_status'] = 'Baja';
                    $isCategory = $categoria_destroy->update();
                    if ($isCategory) {
                        $message = "La categoría se ha eliminado correctamente";
                        $status = true;
                    } else {
                        $message = "La categoría no se ha eliminado correctamente";
                        $status = false;
                    }

                    return redirect()->route('categoriaIndex', ['tipo' => 'Articulo'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('categoriaIndex', ['tipo' => 'Articulo'])->with('message', 'Error al mostrar la categoría')->with('status', false);
                }
                break;

            case 'Vehiculo':
                try {
                    $id = Crypt::decrypt($request['categoria']);
                    $categoria_destroy = CAT_VEHICLES_CATEGORY::where('categoryVehicle_id', '=', $id)->first();

                    $categoria_destroy['categoryVehicle_status'] = 'Baja';
                    $isCategory = $categoria_destroy->update();
                    if ($isCategory) {
                        $message = "La categoría se ha eliminado correctamente";
                        $status = true;
                    } else {
                        $message = "La categoría no se ha eliminado correctamente";
                        $status = false;
                    }

                    return redirect()->route('categoriaIndex', ['tipo' => 'Vehiculo'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('categoriaIndex', ['tipo' => 'Vehiculo'])->with('message', 'Error al mostrar la categoría')->with('status', false);
                }
                break;

            default:
                # code...
                break;
        }
    }


    public function agrupadorCategoriaAgregar(Request $request)
    {
        $categoria_request = $request->except('_token'); //rechazamos el token que nos pasa el formulario

        $tipo = $categoria_request['tipo'];
        switch ($tipo) {
            case 'Proveedor':
                $categoria = new CAT_PROVIDER_CATEGORY();
                $categoria->categoryProvider_name = $categoria_request['nameNombre'];
                $categoria->categoryProvider_status = $categoria_request['statusDG'];

                try {
                    $isCategory = $categoria->save();
                    if ($isCategory) {
                        $message = "La categoría se ha guardado correctamente";
                        $status = true;
                    } else {
                        $message = "La categoría no se ha guardado correctamente";
                        $status = false;
                    }
                } catch (\Exception $e) {
                    dd($e);
                    $message = "Error al guardar la categoría";
                    $status = false;
                }

                return redirect()->route('catalogo.proveedor.index')->with('status', $status)->with('message', $message);
                break;

            case 'Agente':
                $categoria = new CAT_AGENTS_CATEGORY();
                $categoria->categoryAgents_name = $categoria_request['nameNombre'];
                $categoria->categoryAgents_status = $categoria_request['statusDG'];


                try {
                    $isCategory = $categoria->save();
                    if ($isCategory) {
                        $message = "La categoría se ha guardado correctamente";
                        $status = true;
                    } else {
                        $message = "La categoría no se ha guardado correctamente";
                        $status = false;
                    }
                } catch (\Exception $e) {
                    dd($e);
                    $message = "Error al guardar la categoría";
                    $status = false;
                }

                return redirect()->route('catalogo.agentes.index')->with('status', $status)->with('message', $message);
                break;

            case 'cptGastos':
                $categoria = new CAT_EXPENSE_CONCEPTS_CATEGORY();
                $categoria->categoryExpenseConcept_name = $categoria_request['nameNombre'];
                $categoria->categoryExpenseConcept_status = $categoria_request['statusDG'];


                try {
                    $isCategory = $categoria->save();
                    if ($isCategory) {
                        $message = "La categoría se ha guardado correctamente";
                        $status = true;
                    } else {
                        $message = "La categoría no se ha guardado correctamente";
                        $status = false;
                    }
                } catch (\Exception $e) {
                    dd($e);
                    $message = "Error al guardar la categoría";
                    $status = false;
                }

                return redirect()->route('catalogo.concepto-gastos.index')->with('status', $status)->with('message', $message);
                break;

            case 'Articulo':
                $categoria = new CAT_ARTICLES_CATEGORY();
                $categoria->categoryArticle_name = $categoria_request['nameNombre'];
                $categoria->categoryArticle_status = $categoria_request['statusDG'];

                try {
                    $isCategory = $categoria->save();
                    if ($isCategory) {
                        $message = "La categoría se ha guardado correctamente";
                        $status = true;
                    } else {
                        $message = "La categoría no se ha guardado correctamente";
                        $status = false;
                    }
                } catch (\Exception $e) {
                    dd($e);
                    $message = "Error al guardar la categoría";
                    $status = false;
                }

                return redirect()->route('catalogo.articulos.index')->with('status', $status)->with('message', $message);
                break;

            case 'Cliente':
                $categoria = new CAT_CUSTOMERS_CATEGORY();
                $categoria->categoryCostumer_name = $categoria_request['nameNombre'];
                $categoria->categoryCostumer_status = $categoria_request['statusDG'];

                try {
                    $isCategory = $categoria->save();
                    if ($isCategory) {
                        $message = "La categoría se ha guardado correctamente";
                        $status = true;
                    } else {
                        $message = "La categoría no se ha guardado correctamente";
                        $status = false;
                    }
                } catch (\Exception $e) {
                    dd($e);
                    $message = "Error al guardar la categoría";
                    $status = false;
                }

                return redirect()->route('catalogo.clientes.index')->with('status', $status)->with('message', $message);
                break;

            case 'Vehiculo':
                $categoria = new CAT_VEHICLES_CATEGORY();
                $categoria->categoryVehicle_name = $categoria_request['nameNombre'];
                $categoria->categoryVehicle_status = $categoria_request['statusDG'];

                try {
                    $isCategory = $categoria->save();
                    if ($isCategory) {
                        $message = "La categoría se ha guardado correctamente";
                        $status = true;
                    } else {
                        $message = "La categoría no se ha guardado correctamente";
                        $status = false;
                    }
                } catch (\Exception $e) {
                    dd($e);
                    $message = "Error al guardar la categoría";
                    $status = false;
                }

                return redirect()->route('catalogo.vehiculos.index')->with('status', $status)->with('message', $message);

            default:
                # code...
                break;
        }
    }

    public function getCategoria(Request $request)
    {

        $request_categoria = $request->except('_token');
        $tipo = $request_categoria['tipo'];
        switch ($tipo) {
            case 'Proveedor':
                $idLast = CAT_PROVIDER_CATEGORY::count();
                $categoria = $idLast + 1;

                return response()->json(['categoria' => $categoria]);
                break;


            case 'Agente':
                $idLast = CAT_AGENTS_CATEGORY::count();
                $categoria = $idLast + 1;

                return response()->json(['categoria' => $categoria]);
                break;

            case 'cptGastos':
                $idLast = CAT_EXPENSE_CONCEPTS_CATEGORY::count();
                $categoria = $idLast + 1;
                return response()->json(['categoria' => $categoria]);
                break;
            case 'Articulo':
                $idLast = CAT_ARTICLES_CATEGORY::count();
                $categoria = $idLast + 1;
                return response()->json(['categoria' => $categoria]);
                break;

            case 'Cliente':
                $idLast = CAT_CUSTOMERS_CATEGORY::count();
                $categoria = $idLast + 1;

                return response()->json(['categoria' => $categoria]);
                break;

            case 'Vehiculo':
                $idLast = CAT_VEHICLES_CATEGORY::count();
                $categoria = $idLast + 1;

                return response()->json(['categoria' => $categoria]);
            default:
                # code...
                break;
        }
    }
}
