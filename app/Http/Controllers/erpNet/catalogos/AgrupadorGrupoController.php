<?php

namespace App\Http\Controllers\erpNet\catalogos;

use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_AGENTS_GROUP;
use App\Models\agrupadores\CAT_ARTICLES_GROUP;
use App\Models\agrupadores\CAT_CUSTOMERS_GROUP;
use App\Models\agrupadores\CAT_EXPENSE_CONCEPTS_GROUP;
use App\Models\agrupadores\CAT_PROVEEDORES_GRUPO;
use App\Models\agrupadores\CAT_PROVIDER_GROUP;
use App\Models\agrupadores\CAT_VEHICLES_GROUP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class AgrupadorGrupoController extends Controller
{




    public function indexGrupo(Request $request)
    {

        $tipo = $request['tipo'];

        switch ($tipo) {
            case 'Agente':
                $grupos = CAT_AGENTS_GROUP::all()->sortBy('groupAgents_status');
                return view('page.catalogos.agentes.agrupadores.Grupos.index', compact('grupos'));
                break;
            case 'Cliente':
                $grupos = CAT_CUSTOMERS_GROUP::all()->sortBy('groupCustomer_status');
                return view('page.catalogos.clientes.agrupadores.Grupos.index', compact('grupos'));

                break;
            case 'Articulo':
                $grupos = CAT_ARTICLES_GROUP::all()->sortBy('groupArticles_status');
                return view('page.catalogos.articulos.agrupadores.Grupos.index', compact('grupos'));
                break;
            case 'Concepto':
                $grupos = CAT_EXPENSE_CONCEPTS_GROUP::all()->sortBy('groupExpenseConcepts_status');
                return view('page.catalogos.conceptoGastos.agrupadores.Grupos.index', compact('grupos'));
                break;

            case 'Proveedor':
                $grupos = CAT_PROVIDER_GROUP::all()->sortBy('groupProvider_status');
                return view('page.catalogos.proveedores.agrupadores.Grupos.index', compact('grupos'));

                break;
            case 'Vehiculo':
                $grupos = CAT_VEHICLES_GROUP::all()->sortBy('groupVehicles_status');
                return view('page.catalogos.vehiculos.agrupadores.Grupos.index', compact('grupos'));
            default:
                # code...
                break;
        }
    }

    public function createGrupo(Request $request)
    {
        $tipo = $request['tipo'];

        switch ($tipo) {
            case 'Agente':
                return view('page.catalogos.Agentes.Agrupadores.Grupos.create');
                break;

                break;
            case 'Articulo':
                return view('page.catalogos.Articulos.agrupadores.Grupos.create');

                break;
            case 'Concepto':
                return view('page.catalogos.conceptoGastos.agrupadores.Grupos.create');

                break;

            case 'Cliente':
                return view('page.catalogos.Clientes.Agrupadores.Grupos.create');
                break;

            case 'Proveedor':
                return view('page.catalogos.proveedores.agrupadores.Grupos.create');

                break;

            case 'Vehiculo':
                return view('page.catalogos.vehiculos.agrupadores.Grupos.create');

                break;

            default:
                # code...
                break;
        }
    }

    public function editGrupo(Request $request, $id)
    {

        $tipo = $request['tipo'];
        switch ($tipo) {
            case 'Agente':
                try {
                    $id = Crypt::decrypt($id);
                    $grupo_edit = CAT_AGENTS_GROUP::where('groupAgents_id', '=', $id)->first();
                    return view('page.catalogos.agentes.agrupadores.Grupos.edit', compact('grupo_edit'));
                } catch (\Exception $e) {
                    return redirect()->route('grupoIndex', ['tipo' => 'Agente'])->with('message', 'Error al mostrar el Grupo')->with('status', false);
                }
                break;

            case 'Articulo':
                try {
                    $id = Crypt::decrypt($id);
                    $grupo_edit = CAT_ARTICLES_GROUP::where('groupArticle_id', '=', $id)->first();
                    return view('page.catalogos.articulos.agrupadores.Grupos.edit', compact('grupo_edit'));
                } catch (\Exception $e) {
                    return redirect()->route('grupoIndex', ['tipo' => 'Articulo'])->with('message', 'Error al mostrar el Grupo')->with('status', false);
                }
                break;

            case 'Concepto':
                try {
                    $id = Crypt::decrypt($id);
                    $grupo_edit = CAT_EXPENSE_CONCEPTS_GROUP::where('groupExpenseConcept_id', '=', $id)->first();
                    return view('page.catalogos.conceptoGastos.agrupadores.Grupos.edit', compact('grupo_edit'));
                } catch (\Exception $e) {
                    return redirect()->route('grupoIndex', ['tipo' => 'Concepto'])->with('message', 'Error al mostrar el Grupo')->with('status', false);
                }

                break;

            case 'Cliente':
                try {
                    $id = Crypt::decrypt($id);
                    $grupo_edit = CAT_CUSTOMERS_GROUP::where('groupCustomer_id', '=', $id)->first();
                    return view('page.catalogos.clientes.agrupadores.Grupos.edit', compact('grupo_edit'));
                } catch (\Exception $e) {
                    return redirect()->route('grupoIndex', ['tipo' => 'Cliente'])->with('message', 'Error al mostrar el Grupo')->with('status', false);
                }

                break;

            case 'Proveedor':
                try {
                    $id = Crypt::decrypt($id);
                    $grupo_edit = CAT_PROVIDER_GROUP::where('groupProvider_id', '=', $id)->first();
                    return view('page.catalogos.proveedores.agrupadores.Grupos.edit', compact('grupo_edit'));
                } catch (\Exception $e) {
                    return redirect()->route('grupoIndex', ['tipo' => 'Proveedor'])->with('message', 'Error al mostrar el Grupo')->with('status', false);
                }

                break;

            case 'Vehiculo':
                try {
                    $id = Crypt::decrypt($id);
                    $grupo_edit = CAT_VEHICLES_GROUP::where('groupVehicle_id', '=', $id)->first();
                    return view('page.catalogos.vehiculos.agrupadores.Grupos.edit', compact('grupo_edit'));
                } catch (\Exception $e) {
                    return redirect()->route('grupoIndex', ['tipo' => 'Vehiculo'])->with('message', 'Error al mostrar el Grupo')->with('status', false);
                }

                break;

            default:
                # code...
                break;
        }
    }

    public function updateGrupo(Request $request, $id)
    {
        // dd($request->all());
        $tipo = $request['tipo'];

        switch ($tipo) {
            case 'Agente':
                try {
                    $id = Crypt::decrypt($id);
                    $grupo_request = $request->except('_token');
                    $grupo = CAT_AGENTS_GROUP::where('groupAgents_id', '=', $id)->first();
                    $grupo->groupAgents_name = $grupo_request['nameNombre'];
                    $grupo->groupAgents_status = $grupo_request['statusDG'];

                    try {
                        $isCategory = $grupo->update();
                        if ($isCategory) {
                            $message = "El grupo se ha guardado correctamente";
                            $status = true;
                        } else {
                            $message = "El grupo no se ha guardado correctamente";
                            $status = false;
                        }
                    } catch (\Exception $e) {
                        dd($e);
                        $message = "Error al guardar el grupo";
                        $status = false;
                    }

                    return redirect()->route('grupoIndex', ['tipo' => 'Agente'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('grupoIndex', ['tipo' => 'Agente'])->with('message', 'Error al mostrar la categoria')->with('status', false);
                }
                break;

            case 'Articulo':
                try {
                    $id = Crypt::decrypt($id);
                    $grupo_request = $request->except('_token');
                    $grupo = CAT_ARTICLES_GROUP::where('groupArticle_id', '=', $id)->first();
                    $grupo->groupArticle_name = $grupo_request['nameNombre'];
                    $grupo->groupArticle_status = $grupo_request['statusDG'];

                    try {
                        $isCategory = $grupo->update();
                        if ($isCategory) {
                            $message = "El grupo se ha guardado correctamente";
                            $status = true;
                        } else {
                            $message = "El grupo no se ha guardado correctamente";
                            $status = false;
                        }
                    } catch (\Exception $e) {
                        dd($e);
                        $message = "Error al guardar el grupo";
                        $status = false;
                    }

                    return redirect()->route('grupoIndex', ['tipo' => 'Articulo'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('grupoIndex', ['tipo' => 'Articulo'])->with('message', 'Error al mostrar la categoria')->with('status', false);
                }
                break;

            case 'Concepto':
                try {
                    $id = Crypt::decrypt($id);
                    $grupo_request = $request->except('_token');
                    $grupo = CAT_EXPENSE_CONCEPTS_GROUP::where('groupExpenseConcept_id', '=', $id)->first();
                    $grupo->groupExpenseConcept_name = $grupo_request['nameNombre'];
                    $grupo->groupExpenseConcept_status = $grupo_request['statusDG'];

                    try {
                        $isCategory = $grupo->update();
                        if ($isCategory) {
                            $message = "El grupo se ha guardado correctamente";
                            $status = true;
                        } else {
                            $message = "El grupo no se ha guardado correctamente";
                            $status = false;
                        }
                    } catch (\Exception $e) {
                        dd($e);
                        $message = "Error al guardar el grupo";
                        $status = false;
                    }

                    return redirect()->route('grupoIndex', ['tipo' => 'Concepto'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('grupoIndex', ['tipo' => 'Concepto'])->with('message', 'Error al mostrar la categoria')->with('status', false);
                }
                break;

            case 'Cliente':
                try {
                    $id = Crypt::decrypt($id);
                    $grupo_request = $request->except('_token');
                    $grupo = CAT_CUSTOMERS_GROUP::where('groupCustomer_id', '=', $id)->first();
                    $grupo->groupCustomer_name = $grupo_request['nameNombre'];
                    $grupo->groupCustomer_status = $grupo_request['statusDG'];

                    try {
                        $isCategory = $grupo->update();
                        if ($isCategory) {
                            $message = "El grupo se ha guardado correctamente";
                            $status = true;
                        } else {
                            $message = "El grupo no se ha guardado correctamente";
                            $status = false;
                        }
                    } catch (\Exception $e) {
                        dd($e);
                        $message = "Error al guardar el grupo";
                        $status = false;
                    }

                    return redirect()->route('grupoIndex', ['tipo' => 'Cliente'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('grupoIndex', ['tipo' => 'Cliente'])->with('message', 'Error al mostrar la categoria')->with('status', false);
                }
                break;

            case 'Proveedor':
                try {
                    $id = Crypt::decrypt($id);
                    $grupo_request = $request->except('_token');
                    $grupo = CAT_PROVIDER_GROUP::where('groupProvider_id', '=', $id)->first();
                    $grupo->groupProvider_name = $grupo_request['nameNombre'];
                    $grupo->groupProvider_status = $grupo_request['statusDG'];

                    try {
                        $isCategory = $grupo->update();
                        if ($isCategory) {
                            $message = "El grupo se ha guardado correctamente";
                            $status = true;
                        } else {
                            $message = "El grupo no se ha guardado correctamente";
                            $status = false;
                        }
                    } catch (\Exception $e) {
                        dd($e);
                        $message = "Error al guardar el grupo";
                        $status = false;
                    }

                    return redirect()->route('grupoIndex', ['tipo' => 'Proveedor'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('grupoIndex', ['tipo' => 'Proveedor'])->with('message', 'Error al mostrar la categoria')->with('status', false);
                }
                break;

            case 'Vehiculo':
                try {
                    $id = Crypt::decrypt($id);
                    $grupo_request = $request->except('_token');
                    $grupo = CAT_VEHICLES_GROUP::where('groupVehicle_id', '=', $id)->first();
                    $grupo->groupVehicle_name = $grupo_request['nameNombre'];
                    $grupo->groupVehicle_status = $grupo_request['statusDG'];

                    try {
                        $isCategory = $grupo->update();
                        if ($isCategory) {
                            $message = "El grupo se ha guardado correctamente";
                            $status = true;
                        } else {
                            $message = "El grupo no se ha guardado correctamente";
                            $status = false;
                        }
                    } catch (\Exception $e) {
                        dd($e);
                        $message = "Error al guardar el grupo";
                        $status = false;
                    }

                    return redirect()->route('grupoIndex', ['tipo' => 'Vehiculo'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('grupoIndex', ['tipo' => 'Vehiculo'])->with('message', 'Error al mostrar la categoria')->with('status', false);
                }
                break;

            default:
                # code...
                break;
        }
    }

    public function deleteGrupo(Request $request)
    {

        // dd($id);
        // dd($request->all());
        $tipo = $request['tipo'];

        switch ($tipo) {
            case 'Agente':
                try {
                    $id = Crypt::decrypt($request['grupo']);
                    $grupo_destroy = CAT_AGENTS_GROUP::where('groupAgents_id', '=', $id)->first();

                    $grupo_destroy['groupAgents_status'] = 'Baja';
                    $isGroup = $grupo_destroy->update();
                    if ($isGroup) {
                        $message = "El grupo se ha eliminado correctamente";
                        $status = true;
                    } else {
                        $message = "El grupo no se ha eliminado correctamente";
                        $status = false;
                    }

                    return redirect()->route('grupoIndex', ['tipo' => 'Agente'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('grupoIndex', ['tipo' => 'Agente'])->with('message', 'Error al mostrar el grupo')->with('status', false);
                }
                break;

            case 'Articulo':
                try {
                    $id = Crypt::decrypt($request['grupo']);
                    $grupo_destroy = CAT_ARTICLES_GROUP::where('groupArticle_id', '=', $id)->first();

                    $grupo_destroy['groupArticle_status'] = 'Baja';
                    $isGroup = $grupo_destroy->update();
                    if ($isGroup) {
                        $message = "El grupo se ha eliminado correctamente";
                        $status = true;
                    } else {
                        $message = "El grupo no se ha eliminado correctamente";
                        $status = false;
                    }

                    return redirect()->route('grupoIndex', ['tipo' => 'Articulo'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('grupoIndex', ['tipo' => 'Articulo'])->with('message', 'Error al mostrar el grupo')->with('status', false);
                }
                break;

            case 'Concepto':
                try {
                    $id = Crypt::decrypt($request['grupo']);
                    $grupo_destroy = CAT_EXPENSE_CONCEPTS_GROUP::where('groupConcept_id', '=', $id)->first();

                    $grupo_destroy['groupConcept_status'] = 'Baja';
                    $isGroup = $grupo_destroy->update();
                    if ($isGroup) {
                        $message = "El grupo se ha eliminado correctamente";
                        $status = true;
                    } else {
                        $message = "El grupo no se ha eliminado correctamente";
                        $status = false;
                    }

                    return redirect()->route('grupoIndex', ['tipo' => 'Concepto'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('grupoIndex', ['tipo' => 'Concepto'])->with('message', 'Error al mostrar el grupo')->with('status', false);
                }
                break;

            case 'Cliente':
                try {
                    $id = Crypt::decrypt($request['grupo']);
                    $grupo_destroy = CAT_CUSTOMERS_GROUP::where('groupCustomer_id', '=', $id)->first();

                    $grupo_destroy['groupCustomer_status'] = 'Baja';
                    $isGroup = $grupo_destroy->update();
                    if ($isGroup) {
                        $message = "El grupo se ha eliminado correctamente";
                        $status = true;
                    } else {
                        $message = "El grupo no se ha eliminado correctamente";
                        $status = false;
                    }

                    return redirect()->route('grupoIndex', ['tipo' => 'Cliente'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('grupoIndex', ['tipo' => 'Cliente'])->with('message', 'Error al mostrar el grupo')->with('status', false);
                }
                break;

            case 'Proveedor':

                try {
                    $id = Crypt::decrypt($request['grupo']);
                    $grupo_destroy = CAT_PROVIDER_GROUP::where('groupProvider_id', '=', $id)->first();

                    $grupo_destroy['groupProvider_status'] = 'Baja';
                    $isGroup = $grupo_destroy->update();
                    if ($isGroup) {
                        $message = "El grupo se ha eliminado correctamente";
                        $status = true;
                    } else {
                        $message = "El grupo no se ha eliminado correctamente";
                        $status = false;
                    }

                    return redirect()->route('grupoIndex', ['tipo' => 'Proveedor'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('grupoIndex', ['tipo' => 'Proveedor'])->with('message', 'Error al mostrar el grupo')->with('status', false);
                }
                break;

            case 'Vehiculo':
                try {
                    $id = Crypt::decrypt($request['grupo']);
                    $grupo_destroy = CAT_VEHICLES_GROUP::where('groupVehicle_id', '=', $id)->first();

                    $grupo_destroy['groupVehicle_status'] = 'Baja';
                    $isGroup = $grupo_destroy->update();
                    if ($isGroup) {
                        $message = "El grupo se ha eliminado correctamente";
                        $status = true;
                    } else {
                        $message = "El grupo no se ha eliminado correctamente";
                        $status = false;
                    }

                    return redirect()->route('grupoIndex', ['tipo' => 'Vehiculo'])->with('status', $status)->with('message', $message);
                } catch (\Exception $e) {
                    return redirect()->route('grupoIndex', ['tipo' => 'Vehiculo'])->with('message', 'Error al mostrar el grupo')->with('status', false);
                }
                break;



            default:
                # code...
                break;
        }
    }

    public function agrupadorGrupoAgregar(Request $request)
    {

        $grupo_request = $request->except('_token'); //rechazamos el token que nos pasa el formulario
        $tipo = $grupo_request['tipo'];

        switch ($tipo) {
            case 'Proveedor':
                $grupo = new CAT_PROVIDER_GROUP();

                $grupo->groupProvider_name = $grupo_request['nameNombre'];
                $grupo->groupProvider_status = $grupo_request['statusDG'];

                try {
                    $isGroup = $grupo->save();
                    if ($isGroup) {
                        $message = "El grupo se ha guardado correctamente";
                        $status = true;
                    } else {
                        $message = "EL grupo no se ha guardado correctamente";
                        $status = false;
                    }
                } catch (\Exception $e) {
                    dd($e);
                    $message = "Error al guardar el grupo";
                    $status = false;
                }

                return redirect()->route('catalogo.proveedor.index')->with('status', $status)->with('message', $message);
                break;

            case 'Agente':
                $grupo = new CAT_AGENTS_GROUP();

                $grupo->groupAgents_name = $grupo_request['nameNombre'];
                $grupo->groupAgents_status = $grupo_request['statusDG'];

                try {
                    $isGroup = $grupo->save();
                    if ($isGroup) {
                        $message = "El grupo se ha guardado correctamente";
                        $status = true;
                    } else {
                        $message = "EL grupo no se ha guardado correctamente";
                        $status = false;
                    }
                } catch (\Exception $e) {
                    dd($e);
                    $message = "Error al guardar el grupo";
                    $status = false;
                }

                return redirect()->route('catalogo.agentes.index')->with('status', $status)->with('message', $message);
                break;

            case 'cptGastos':
                $grupo = new CAT_EXPENSE_CONCEPTS_GROUP();

                $grupo->groupExpenseConcept_name = $grupo_request['nameNombre'];
                $grupo->groupExpenseConcept_status = $grupo_request['statusDG'];

                try {
                    $isGroup = $grupo->save();
                    if ($isGroup) {
                        $message = "El grupo se ha guardado correctamente";
                        $status = true;
                    } else {
                        $message = "EL grupo no se ha guardado correctamente";
                        $status = false;
                    }
                } catch (\Exception $e) {
                    dd($e);
                    $message = "Error al guardar el grupo";
                    $status = false;
                }

                return redirect()->route('catalogo.concepto-gastos.index')->with('status', $status)->with('message', $message);
                break;

            case 'Articulo':
                $grupo = new CAT_ARTICLES_GROUP();
                $grupo->groupArticle_name = $grupo_request['nameNombre'];
                $grupo->groupArticle_status = $grupo_request['statusDG'];

                try {
                    $isGroup = $grupo->save();
                    if ($isGroup) {
                        $message = "El grupo se ha guardado correctamente";
                        $status = true;
                    } else {
                        $message = "El grupo no se ha guardado correctamente";
                        $status = false;
                    }
                } catch (\Exception $e) {
                    dd($e);
                    $message = "Error al guardar el grupo";
                    $status = false;
                }

                return redirect()->route('catalogo.articulos.index')->with('status', $status)->with('message', $message);
                break;

            case 'Cliente':
                $grupo = new CAT_CUSTOMERS_GROUP();

                $grupo->groupCustomer_name = $grupo_request['nameNombre'];
                $grupo->groupCustomer_status = $grupo_request['statusDG'];

                try {
                    $isGroup = $grupo->save();
                    if ($isGroup) {
                        $message = "El grupo se ha guardado correctamente";
                        $status = true;
                    } else {
                        $message = "EL grupo no se ha guardado correctamente";
                        $status = false;
                    }
                } catch (\Exception $e) {
                    dd($e);
                    $message = "Error al guardar el grupo";
                    $status = false;
                }

                return redirect()->route('catalogo.clientes.index')->with('status', $status)->with('message', $message);
                break;

            case 'Proveedor':

                $grupo = new CAT_PROVIDER_GROUP();
                $grupo->groupProvider_name = $grupo_request['nameNombre'];
                $grupo->groupProvider_status = $grupo_request['statusDG'];

                try {
                    $isGroup = $grupo->save();
                    if ($isGroup) {
                        $message = "El grupo se ha guardado correctamente";
                        $status = true;
                    } else {
                        $message = "EL grupo no se ha guardado correctamente";
                        $status = false;
                    }
                } catch (\Exception $e) {
                    dd($e);
                    $message = "Error al guardar el grupo";
                    $status = false;
                }

                return redirect()->route('catalogo.proveedores.index')->with('status', $status)->with('message', $message);
                break;

            case 'Vehiculo':
                $grupo = new CAT_VEHICLES_GROUP();

                $grupo->groupVehicle_name = $grupo_request['nameNombre'];
                $grupo->groupVehicle_status = $grupo_request['statusDG'];

                try {
                    $isGroup = $grupo->save();
                    if ($isGroup) {
                        $message = "El grupo se ha guardado correctamente";
                        $status = true;
                    } else {
                        $message = "EL grupo no se ha guardado correctamente";
                        $status = false;
                    }
                } catch (\Exception $e) {
                    dd($e);
                    $message = "Error al guardar el grupo";
                    $status = false;
                }

                return redirect()->route('catalogo.vehiculos.index')->with('status', $status)->with('message', $message);
                break;

            default:
                # code...
                break;
        }
    }

    public function getGrupo(Request $request)
    {
        $request_grupo = $request->except('_token');
        $tipo = $request_grupo['tipo'];

        switch ($tipo) {
            case 'Proveedor':
                $idLast = CAT_PROVIDER_GROUP::count();
                $grupo = $idLast + 1;
                return response()->json(['grupo' => $grupo]);
                break;

            case 'Agente':
                $idLast = CAT_AGENTS_GROUP::count();
                $grupo = $idLast + 1;
                return response()->json(['grupo' => $grupo]);
                break;

            case 'cptGastos':
                $idLast = CAT_EXPENSE_CONCEPTS_GROUP::count();
                $grupo = $idLast + 1;
                return response()->json(['grupo' => $grupo]);
                break;

            case 'Articulo':
                $idLast = CAT_ARTICLES_GROUP::count();
                $grupo = $idLast + 1;
                return response()->json(['grupo' => $grupo]);
                break;

            case 'Cliente':
                $idLast = CAT_CUSTOMERS_GROUP::count();
                $grupo = $idLast + 1;
                return response()->json(['grupo' => $grupo]);
                break;

            case 'Vehiculo':
                $idLast = CAT_VEHICLES_GROUP::count();
                $grupo = $idLast + 1;
                return response()->json(['grupo' => $grupo]);
                break;
            default:
                # code...
                break;
        }
    }
}
