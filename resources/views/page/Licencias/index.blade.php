@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="pageheader">
        <div class=" display-space-between">
            <div>
                    <div class="pageicon pull-left mr10">
                         <i class="glyphicon glyphicon-user" aria-hidden="true"></i>
                    </div>
                     <div class="media-body">
                        <ul class="breadcrumb">
                            <li><a href="{{ route( "dashboard.index" )}}"><i class="glyphicon glyphicon-home"></i></a></li>
                            <li>Control de Licencias</li>
                        </ul>
                        <h4>Control de Licencias</h4>
                    </div>
            </div>
        </div> <!-- media -->
    </div><!-- pageheader -->
    <div class="contentpanel">
        <div class="row row-stat">
            <form action="" method="get">

              <h3><strong>Licencias actuales</strong></h3>
              <div class="col-md-12">
                <div class="panel panel-default">
                  <div class="panel-body">
                    <div class="table-responsive">

                      <table class="table table-striped">
                        <thead>
                          <tr>
                            <th>Sesión</th>
                            <th>ID del Usuario</th>
                            <th>Usuario</th>
                            <th>Nombre</th>
                            <th>Licencia</th>
                            <th>Estatus</th>
                            <th>Acción</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($licenses as $license)
                          <tr>
                            <td>{{$license->Identificador}}</td>
                            <td>{{$license->license_UserID}}</td>
                            <td>{{$license->user_name}}</td>
                            <td>{{$license->username}}</td>
                            <td>{{$license->license_Licenses}}</td>
                            <td>{{$license->license_Active == 1 ? 'Activo' : 'Vencido'}}</td>
                            <td>
                              <a href="{{ route('licenses.delete', [$license->Identificador])}}" class="btn btn-danger btn-sm"><i class="glyphicon glyphicon-minus-sign"></i></a>
                            </td>
                          </tr>
                          @endforeach
                        </tbody>
                      </table>

                    </div>
                  </div>
                </div>
              </div>

            </form>

        </div>
    </div>
</div>

@endsection