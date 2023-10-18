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
                            <li>Administración de Timbres</li>
                        </ul>
                        <h4>Administración de Timbres</h4>
                    </div>
            </div>

            <div class="object-create"> 
                <a href="{{route("timbrado.create")}}" class="btn btn-success">Asignar timbres</a>
            </div>
        </div> <!-- media -->
    </div><!-- pageheader -->
    <div class="contentpanel">
        <div class="row row-stat">
                <div class="col-md-12 mt20">
                </div>
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel-group" id="accordion2">
                        <div class="panel panel-info" style="border: ;">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion2" href="#collapseOne2">
                                        <i class="glyphicon glyphicon-info-sign"></i> <span>Resumen de cuenta</span>
                                    </a>
                                </h4>
                            </div>
                            <div class="panel-collapse collapse in">
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p style="font-size: 21px">Créditos: <strong>{{$company->companies_AvailableStamps ?? '0'}}</strong></p>
                                        </div>

                                        <div class="col-md-6">
                                            <p style="font-size: 21px">Última carga: <strong>
                                            @if(isset($company->companies_LastUpdateStamps))
                                                {{ \Carbon\Carbon::parse($company->companies_LastUpdateStamps)->format('d/m/Y') }}
                                            @else
                                                No hay última carga
                                            @endif
                                            </strong></p>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div><!-- panel -->
                    </div><!-- panel-group -->
                </div>
                <div class="col-md-12 mt20">
                </div>
                <div class="col-md-10 col-md-offset-1 mt20">
                    <div class="panel-group" id="accordion2">
                        <div class="panel panel-info" style="border: ;">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion2" href="#collapseOne2">
                                        <i class="glyphicon glyphicon-info-sign"></i> <span>Estadísticas</span>
                                    </a>
                                </h4>
                            </div>
                            <div class="panel-collapse collapse in">
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p style="font-size: 21px">Consumo total: <strong>{{$total}}</strong></p>
                                            <p style="font-size: 21px">Consumo de ayer: <strong>{{$totalAyer}}</strong></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p style="font-size: 21px; color:white;">Consumo de hoy: <strong>0</strong></p>
                                            <p style="font-size: 21px">Consumo de hoy: <strong>{{$totalHoy}}</strong></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- panel -->
                    </div><!-- panel-group -->
                </div>
        </div>
    </div>
</div>

@endsection