<?php

namespace App\Http\Controllers\erpNet\catalogos;

use App\Exports\CAT_AgentesExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_AGENTS_CATEGORY;
use App\Models\agrupadores\CAT_AGENTS_GROUP;
use App\Models\catalogos\CAT_AGENTS;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_SUCURSALES;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;

class AgentesController extends Controller
{
     public function __construct()
    {
        $this->middleware(['permission:Agentes']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $categoria_array = $this->SelectCategorias();
        $grupo_array = $this->SelectGrupos();
        $agentes = CAT_AGENTS::join('CAT_BRANCH_OFFICES', 'CAT_AGENTS.agents_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
        ->select('CAT_AGENTS.*', 'CAT_BRANCH_OFFICES.branchOffices_name')
        ->where('CAT_AGENTS.agents_status', '=', 'Alta')
        ->get();
        return view('page.catalogos.Agentes.index', compact('agentes', 'categoria_array', 'grupo_array'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $select_sucursales = $this->SelectSucursales();
        $select_categoria = $this->SelectCategorias();
        $select_grupo = $this->SelectGrupos();
        return view('page.catalogos.Agentes.create', compact('select_sucursales', 'select_categoria', 'select_grupo'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $cat_agentes_request = $request->except('_token');
        $isKeyAgente = CAT_AGENTS::where('agents_name', $cat_agentes_request['nameNombre'])->first();
        if($isKeyAgente){
            $message = "El agente: ".$cat_agentes_request['agente_nombre']." ya existe";
            $status = false;
        }else{
            $cat_agentes = new CAT_AGENTS();
            $cat_agentes->agents_name = $cat_agentes_request['nameNombre'];
            $cat_agentes->agents_type = $cat_agentes_request['nameTipo'];
            $cat_agentes->agents_category = $cat_agentes_request['nameCategoria'];
            $cat_agentes->agents_group = $cat_agentes_request['nameGrupo'];
            $cat_agentes->agents_branchOffice = $cat_agentes_request['nombreSuc'];
            $cat_agentes->agents_status = $cat_agentes_request['statusDG'];

            try{
                $isCreate = $cat_agentes->save();

                if($isCreate){
                    $message = "El agente: ".$cat_agentes_request['nameNombre']." se ha guardado correctamente";
                    $status = true;
                }else{
                    $message = "El agente: ".$cat_agentes_request['nameNombre']." no se ha guardado correctamente";
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Error al guardar el agente: ".$cat_agentes_request['nameNombre'];
                return redirect()->route('catalogo.agentes.index')->with('message', $message)->with('status', false);
            }
        }
        return redirect()->route('catalogo.agentes.index')->with('message', $message)->with('status', $status);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try{
            $id = Crypt::decrypt($id);
            $select_sucursales = $this->SelectSucursales();
            $select_categoria = $this->SelectCategorias();
            $select_grupo = $this->SelectGrupos();
            $agentes = CAT_AGENTS::where('agents_key', $id)->first();
            return view('page.catalogos.Agentes.show', compact('agentes', 'select_sucursales', 'select_categoria', 'select_grupo'));

        }catch (\Throwable $th) {
            return redirect()->route('catalogo.agentes.index')->with('message', 'Error al mostrar el agente')->with('status', false);
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
        try{
            $id = Crypt::decrypt($id);
            $agentes_edit = CAT_AGENTS::where('agents_key', $id)->first();
            $select_sucursales = $this->SelectSucursales();
            $select_categoria = $this->SelectCategorias();
            $select_grupo = $this->SelectGrupos();
            return view('page.catalogos.Agentes.edit', compact('agentes_edit', 'select_sucursales', 'select_categoria', 'select_grupo'));
        }catch (\Exception $e) {
            return redirect()->route('catalogo.agentes.index')->with('message', 'Error al mostrar el agente')->with('status', false);
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
        $id = Crypt::decrypt($id);
        $cat_agentes_request = $request->except('_token');
        $cat_agentes = CAT_AGENTS::where('agents_key', $id)->first();
            $cat_agentes->agents_type = $cat_agentes_request['nameTipo'];
            $cat_agentes->agents_category = $cat_agentes_request['nameCategoria'];
            $cat_agentes->agents_group = $cat_agentes_request['nameGrupo'];
            $cat_agentes->agents_branchOffice = $cat_agentes_request['nombreSuc'];
            $cat_agentes->agents_status = $cat_agentes_request['statusDG'];

            try{
                $isUpdate = $cat_agentes->save();

                if($isUpdate){
                    $message = "El agente: ".$cat_agentes_request['nameNombre']." se ha guardado correctamente";
                    $status = true;
                }else{
                    $message = "El agente: ".$cat_agentes_request['nameNombre']." no se ha guardado correctamente";
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Error al guardar el agente: ".$cat_agentes_request['nameNombre'];
                return redirect()->route('catalogo.agentes.index')->with('message', $message)->with('status', false);
            }
            return redirect()->route('catalogo.agentes.index')->with('message', $message)->with('status', $status);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $id = Crypt::decrypt($id);
            $cat_agentes = CAT_AGENTS::where('agents_key', $id)->first();
            $cat_agentes->agents_status = 'Baja';

            $IsRemoved = $cat_agentes->update();
            $status = false;

            if($IsRemoved){
                $message = "El agente: ".$cat_agentes->agents_name." se ha eliminado correctamente";
                $status = true;
            }else{
                $message = "El agente: ".$cat_agentes->agents_name." no se ha eliminado correctamente";
                $status = false;
            }
            return redirect()->route('catalogo.agentes.index')->with('message', $message)->with('status', $status);
        }catch (\Throwable $th) {
            $message = "Error al eliminar el agente";
            return redirect()->route('catalogo.agentes.index')->with('message', $message)->with('status', false);
        }
    }

    public function agentesAction(Request $request)
    {
        $keyAgente = $request->keyAgente;
        $nameAgente = $request->nameAgente;
        $category = $request->categoria;
        $group = $request->grupo;
        $status = $request->status;

        switch ($request->input('action')){
            case 'Búsqueda':

                $agentes_collection_filtro = CAT_AGENTS::join('CAT_BRANCH_OFFICES', 'CAT_AGENTS.agents_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                ->select('CAT_AGENTS.*', 'CAT_BRANCH_OFFICES.branchOffices_name')->whereAgentKey($keyAgente)->whereAgentName($nameAgente)->whereAgentCategory($category)->whereAgentGroup($group)->whereAgentStatus($status)->get();

                return redirect()->route('catalogo.agentes.index')->with('agente_filtro_array', $agentes_collection_filtro)->with('keyAgente', $keyAgente)->with('nameAgente', $nameAgente)->with('categoria', $category)->with('grupo', $group)->with('status', $status);
                break;

                case 'Exportar excel':
                    $almacen = new CAT_AgentesExport($keyAgente, $nameAgente, $category, $group, $status);
                    return Excel::download($almacen, 'agentes.xlsx');
                    break;

                    default:
                    break;
        }
    }

    function SelectSucursales(){
        $sucursales = CAT_BRANCH_OFFICES::where('branchOffices_status', '=', 'Alta')->get();
        $sucursales_array = array();
        foreach ($sucursales as $sucursal){
            $sucursales_array[$sucursal->branchOffices_key] = $sucursal->branchOffices_name;
        }
        return $sucursales_array;
    }

    function SelectCategorias(){
        $categorias = CAT_AGENTS_CATEGORY::where('categoryAgents_status', '=', 'Alta')->orderBy('categoryAgents_id', 'asc')->get();
        $categorias_array = array();
        foreach ($categorias as $categoria){
            $categorias_array[$categoria->categoryAgents_name] = $categoria->categoryAgents_name;
        }
        return $categorias_array;
    }

    function SelectGrupos(){
        $grupos = CAT_AGENTS_GROUP::where('groupAgents_status', '=', 'Alta')->orderBy('groupAgents_id', 'asc')->get();
        $grupos_array = array();
        foreach ($grupos as $grupo){
            $grupos_array[$grupo->groupAgents_name] = $grupo->groupAgents_name;
        }
        return $grupos_array;
    }

    public function getCategoriaAgente(){
        $idLast = CAT_AGENTS::all()->last();
        if($idLast == null){
            $getid = 0;
        }else{
            $getid=$idLast->agents_key;
        }
    //    $getCategoria= $idLast->categoriaProveedor_id;
         $AgenteID = $getid + 1;

        return response()->json(['agente' => $AgenteID]);
    }
}
