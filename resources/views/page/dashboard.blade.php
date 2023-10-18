@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="pageheader">
            <div class="media">
                <div class="pageicon pull-left ">
                    <i class="fa fa-tachometer fa-spin"></i>
                </div>
                <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href=""><i class="glyphicon glyphicon-home"></i></a></li>
                        <li>Dashboard</li>
                    </ul>
                    <h4>Dashboard</h4>
                </div>
            </div><!-- media -->
        </div><!-- pageheader -->

        <div class="contentpanel">
            <div class="row row-dashboard">
                {{-- <div class="col-md-6">
                    <div class="panel-group" id="accordion2">
                        <div class="panel panel-info" style="border: ;">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion2" href="#collapseOne2">
                                        <i class="glyphicon glyphicon-info-sign"></i> <span>PENDIENTES
                                            {{ $ordenesCompra }}</span>
                                    </a>
                                </h4>
                            </div>
                            <div id="collapseOne2" class="panel-collapse collapse in">
                                <div class="panel-body">
                                    @if ($ordenesCompra > 0)
                                        <ul>
                                            <li style="color: red">TIENE ORDENES DE COMPRA PENDIENTES POR RECIBIR</li>
                                        </ul>
                                    @else
                                        <ul>
                                            <li>Todo en orden</li>
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div><!-- panel -->
                    </div><!-- panel-group -->
                </div> --}}

                {{-- <div class="col-md-12"></div> --}}
            </div><!-- row -->

            <div class="col-md-2" style="display: none">
                <label for="dashboard1" class="negrita">Primer Dashboard<span class='asterisk'>
                        *</span></label>
                <div class="input-group form-group mb15">
                    <input type="text" class="form-control" id="dashboard1" name="dashboard1"
                        value="{{ isset($usuario) ? $usuario['user_getTop10SalesArticles'] : null }}" />
                </div>
            </div>
            <div class="col-md-2" style="display: none">
                <label for="dashboard2" class="negrita">Segundo Dashboard<span class='asterisk'>
                        *</span></label>
                <div class="input-group form-group mb15">
                    <input type="text" class="form-control" id="dashboard2" name="dashboard2"
                        value="{{ isset($usuario) ? $usuario['user_getSalesByFamily'] : null }}" />
                </div>
            </div>
            <div class="col-md-2" style="display: none">
                <label for="dashboard3" class="negrita">Tercer Dashboard<span class='asterisk'>
                        *</span></label>
                <div class="input-group form-group mb15">
                    <input type="text" class="form-control" id="dashboard3" name="dashboard3"
                        value="{{ isset($usuario) ? $usuario['user_getCurrentSaleVSPreviousSale'] : null }}" />
                </div>
            </div>
            <div class="col-md-2" style="display: none">
                <label for="dashboard4" class="negrita">Cuarto Dashboard<span class='asterisk'>
                        *</span></label>
                <div class="input-group form-group mb15">
                    <input type="text" class="form-control" id="dashboard4" name="dashboard4"
                        value="{{ isset($usuario) ? $usuario['user_getSalesAndFlows'] : null }}" />
                </div>
            </div>
            <div class="col-md-2" style="display: none">
                <label for="dashboard5" class="negrita">Quinto Dashboard<span class='asterisk'>
                        *</span></label>
                <div class="input-group form-group mb15">
                    <input type="text" class="form-control" id="dashboard5" name="dashboard5"
                        value="{{ isset($usuario) ? $usuario['user_calculateSalesSummary'] : null }}" />
                </div>
            </div>
            <div class="col-md-2" style="display: none">
                <label for="dashboard6" class="negrita">Sexto Dashboard<span class='asterisk'>
                        *</span></label>
                <div class="input-group form-group mb15">
                    <input type="text" class="form-control" id="dashboard6" name="dashboard6"
                        value="{{ isset($usuario) ? $usuario['user_getEarningAndExpenses'] : null }}" />
                </div>
            </div>


            <div class="row">
                @if (Auth::user()->user_getTop10SalesArticles == 1)
                    <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('nameSucursal', 'Sucursal', ['class' => 'negrita']) !!}
                                                    {!! Form::select(
                                                        'nameSucursal',
                                                        $select_sucursales,
                                                        session()->has('nameSucursal') ? session()->get('nameSucursal') : 'Todos',
                                                        [
                                                            'id' => 'sucursalSelect',
                                                            'class' => 'widthAll select-status select-search-hide',
                                                        ],
                                                    ) !!}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('nameFecha', 'Fecha', ['class' => 'negrita']) !!}
                                                    {!! Form::select(
                                                        'nameFecha',
                                                        [
                                                            'Mes' => 'Mes',
                                                            'Mes Anterior' => 'Mes Anterior',
                                                            'Semana' => 'Semana',
                                                            'Hoy' => 'Hoy',
                                                            'Ayer' => 'Ayer',
                                                            'Rango Fechas' => 'Rango Fechas',
                                                        ],
                                                        session()->has('nameFecha') ? session()->get('nameFecha') : 'Mes',
                                                        [
                                                            'id' => 'fechaSelect',
                                                            'class' => 'widthAll select-status select-search-hide',
                                                            'placeholder' => 'Seleccione una opción',
                                                        ],
                                                    ) !!}
                                                </div>
                                            </div>
                                            <div class="col-md-12 fecha-rango">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        {!! Form::label('nameFechaInicio', 'Fecha Inicio', ['class' => 'negrita']) !!}
                                                        <div class="form-group">
                                                            <input type="text" class="form-control datepicker"
                                                                name="fechaInicio" id="fechaInicial"
                                                                placeholder="DD/MM/AAAA" autocomplete="off"
                                                                value="{{ session()->has('fechaInicio') ? session()->get('fechaInicio') : '' }}">
                                                            <span class="input-group-addon"><i
                                                                    class="glyphicon glyphicon-calendar"></i></span>
                                                        </div><!-- input-group -->
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group  form-group">
                                                        {!! Form::label('nameFechaFinal', 'Fecha Final', ['class' => 'negrita']) !!}
                                                        <div class="form-group">
                                                            <input type="text" class="form-control datepicker"
                                                                name="fechaFinal" id="fechaFinal"
                                                                placeholder="DD/MM/AAAA" autocomplete="off"
                                                                value="{{ session()->has('fechaFinal') ? session()->get('fechaFinal') : '' }}">
                                                            <span class="input-group-addon"><i
                                                                    class="glyphicon glyphicon-calendar"></i></span>
                                                        </div><!-- input-group -->
                                                    </div>
                                                </div>
                                            </div>
                                            <button id="filterButton" class="btn btn-primary">Filtrar</button>
                                            <canvas id="myChart"></canvas>
                                        </div>
                                    </div><!-- col-md-7 -->

                                </div><!-- row -->
                            </div><!-- panel-body -->
                        </div><!-- panel -->
                    </div>
                @endif
                @if (Auth::user()->user_getSalesByFamily == 1)
                    <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('nameSucursal', 'Sucursal', ['class' => 'negrita']) !!}
                                                    {!! Form::select(
                                                        'nameSucursal',
                                                        $select_sucursales,
                                                        session()->has('nameSucursal') ? session()->get('nameSucursal') : 'Todos',
                                                        [
                                                            'id' => 'sucursalSelect2',
                                                            'class' => 'widthAll select-status select-search-hide',
                                                        ],
                                                    ) !!}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('nameFecha', 'Fecha', ['class' => 'negrita']) !!}
                                                    {!! Form::select(
                                                        'nameFecha',
                                                        [
                                                            'Mes' => 'Mes',
                                                            'Mes Anterior' => 'Mes Anterior',
                                                            'Semana' => 'Semana',
                                                            'Hoy' => 'Hoy',
                                                            'Ayer' => 'Ayer',
                                                            'Rango Fechas' => 'Rango Fechas',
                                                        ],
                                                        session()->has('nameFecha') ? session()->get('nameFecha') : 'Mes',
                                                        [
                                                            'id' => 'fechaSelect2',
                                                            'class' => 'widthAll select-status select-search-hide',
                                                            'placeholder' => 'Seleccione una opción',
                                                        ],
                                                    ) !!}
                                                </div>
                                            </div>
                                            <div class="col-md-12 fecha-rango2">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        {!! Form::label('nameFechaInicio', 'Fecha Inicio', ['class' => 'negrita']) !!}
                                                        <div class="form-group">
                                                            <input type="text" class="form-control datepicker"
                                                                name="fechaInicio" id="fechaInicial2"
                                                                placeholder="DD/MM/AAAA" autocomplete="off"
                                                                value="{{ session()->has('fechaInicio') ? session()->get('fechaInicio') : '' }}">
                                                            <span class="input-group-addon"><i
                                                                    class="glyphicon glyphicon-calendar"></i></span>
                                                        </div><!-- input-group -->
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group  form-group">
                                                        {!! Form::label('nameFechaFinal', 'Fecha Final', ['class' => 'negrita']) !!}
                                                        <div class="form-group">
                                                            <input type="text" class="form-control datepicker"
                                                                name="fechaFinal" id="fechaFinal2"
                                                                placeholder="DD/MM/AAAA" autocomplete="off"
                                                                value="{{ session()->has('fechaFinal') ? session()->get('fechaFinal') : '' }}">
                                                            <span class="input-group-addon"><i
                                                                    class="glyphicon glyphicon-calendar"></i></span>
                                                        </div><!-- input-group -->
                                                    </div>
                                                </div>
                                            </div>
                                            <button id="filterButton2" class="btn btn-primary">Filtrar</button>
                                            <canvas id="myChart2"></canvas>
                                        </div>
                                    </div><!-- col-md-7 -->

                                </div><!-- row -->
                            </div><!-- panel-body -->
                        </div><!-- panel -->
                    </div>
                @endif
                @if (Auth::user()->user_getCurrentSaleVSPreviousSale == 1)
                    <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    {!! Form::label('nameSucursal', 'Sucursal', ['class' => 'negrita']) !!}
                                                    {!! Form::select(
                                                        'nameSucursal',
                                                        $select_sucursales,
                                                        session()->has('nameSucursal') ? session()->get('nameSucursal') : 'Todos',
                                                        [
                                                            'id' => 'sucursalSelect3',
                                                            'class' => 'widthAll select-status select-search-hide',
                                                        ],
                                                    ) !!}
                                                </div>
                                            </div>
                                            <button id="filterButton3" class="btn btn-primary">Filtrar</button>
                                            <canvas id="myChart3"></canvas>
                                        </div>
                                    </div><!-- col-md-7 -->

                                </div><!-- row -->
                            </div><!-- panel-body -->
                        </div><!-- panel -->
                    </div>
                @endif
            </div><!-- row -->
            <div class="row">
                @if (Auth::user()->user_getSalesAndFlows == 1)
                    <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('nameSucursal', 'Sucursal', ['class' => 'negrita']) !!}
                                                    {!! Form::select(
                                                        'nameSucursal',
                                                        $select_sucursales,
                                                        session()->has('nameSucursal') ? session()->get('nameSucursal') : 'Todos',
                                                        [
                                                            'id' => 'sucursalSelect4',
                                                            'class' => 'widthAll select-status select-search-hide',
                                                        ],
                                                    ) !!}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('nameFecha', 'Fecha', ['class' => 'negrita']) !!}
                                                    {!! Form::select(
                                                        'nameFecha',
                                                        [
                                                            'Mes' => 'Mes',
                                                            'Mes Anterior' => 'Mes Anterior',
                                                            'Semana' => 'Semana',
                                                            'Hoy' => 'Hoy',
                                                            'Ayer' => 'Ayer',
                                                            'Rango Fechas' => 'Rango Fechas',
                                                        ],
                                                        session()->has('nameFecha') ? session()->get('nameFecha') : 'Mes',
                                                        [
                                                            'id' => 'fechaSelect4',
                                                            'class' => 'widthAll select-status select-search-hide',
                                                            'placeholder' => 'Seleccione una opción',
                                                        ],
                                                    ) !!}
                                                </div>
                                            </div>
                                            <div class="col-md-12 fecha-rango4">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        {!! Form::label('nameFechaInicio', 'Fecha Inicio', ['class' => 'negrita']) !!}
                                                        <div class="form-group">
                                                            <input type="text" class="form-control datepicker"
                                                                name="fechaInicio" id="fechaInicial4"
                                                                placeholder="DD/MM/AAAA" autocomplete="off"
                                                                value="{{ session()->has('fechaInicio') ? session()->get('fechaInicio') : '' }}">
                                                            <span class="input-group-addon"><i
                                                                    class="glyphicon glyphicon-calendar"></i></span>
                                                        </div><!-- input-group -->
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group  form-group">
                                                        {!! Form::label('nameFechaFinal', 'Fecha Final', ['class' => 'negrita']) !!}
                                                        <div class="form-group">
                                                            <input type="text" class="form-control datepicker"
                                                                name="fechaFinal" id="fechaFinal4"
                                                                placeholder="DD/MM/AAAA" autocomplete="off"
                                                                value="{{ session()->has('fechaFinal') ? session()->get('fechaFinal') : '' }}">
                                                            <span class="input-group-addon"><i
                                                                    class="glyphicon glyphicon-calendar"></i></span>
                                                        </div><!-- input-group -->
                                                    </div>
                                                </div>
                                            </div>
                                            <button id="filterButton4" class="btn btn-primary">Filtrar</button>
                                            <canvas id="myChart4"></canvas>
                                        </div>
                                    </div><!-- col-md-7 -->

                                </div><!-- row -->
                            </div><!-- panel-body -->
                        </div><!-- panel -->
                    </div>
                @endif
                @if (Auth::user()->user_calculateSalesSummary == 1)
                    <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('nameSucursal', 'Sucursal', ['class' => 'negrita']) !!}
                                                    {!! Form::select(
                                                        'nameSucursal',
                                                        $select_sucursales,
                                                        session()->has('nameSucursal') ? session()->get('nameSucursal') : 'Todos',
                                                        [
                                                            'id' => 'sucursalSelect5',
                                                            'class' => 'widthAll select-status select-search-hide',
                                                        ],
                                                    ) !!}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('nameFecha', 'Fecha', ['class' => 'negrita']) !!}
                                                    {!! Form::select(
                                                        'nameFecha',
                                                        [
                                                            'Mes' => 'Mes',
                                                            'Mes Anterior' => 'Mes Anterior',
                                                            'Semana' => 'Semana',
                                                            'Hoy' => 'Hoy',
                                                            'Ayer' => 'Ayer',
                                                            'Rango Fechas' => 'Rango Fechas',
                                                        ],
                                                        session()->has('nameFecha') ? session()->get('nameFecha') : 'Mes',
                                                        [
                                                            'id' => 'fechaSelect5',
                                                            'class' => 'widthAll select-status select-search-hide',
                                                            'placeholder' => 'Seleccione una opción',
                                                        ],
                                                    ) !!}
                                                </div>
                                            </div>
                                            <div class="col-md-12 fecha-rango5">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        {!! Form::label('nameFechaInicio', 'Fecha Inicio', ['class' => 'negrita']) !!}
                                                        <div class="form-group">
                                                            <input type="text" class="form-control datepicker"
                                                                name="fechaInicio" id="fechaInicial5"
                                                                placeholder="DD/MM/AAAA" autocomplete="off"
                                                                value="{{ session()->has('fechaInicio') ? session()->get('fechaInicio') : '' }}">
                                                            <span class="input-group-addon"><i
                                                                    class="glyphicon glyphicon-calendar"></i></span>
                                                        </div><!-- input-group -->
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group  form-group">
                                                        {!! Form::label('nameFechaFinal', 'Fecha Final', ['class' => 'negrita']) !!}
                                                        <div class="form-group">
                                                            <input type="text" class="form-control datepicker"
                                                                name="fechaFinal" id="fechaFinal5"
                                                                placeholder="DD/MM/AAAA" autocomplete="off"
                                                                value="{{ session()->has('fechaFinal') ? session()->get('fechaFinal') : '' }}">
                                                            <span class="input-group-addon"><i
                                                                    class="glyphicon glyphicon-calendar"></i></span>
                                                        </div><!-- input-group -->
                                                    </div>
                                                </div>
                                            </div>
                                            <button id="filterButton5" class="btn btn-primary">Filtrar</button>
                                            <canvas id="myChart5"></canvas>
                                        </div>
                                    </div><!-- col-md-7 -->

                                </div><!-- row -->
                            </div><!-- panel-body -->
                        </div><!-- panel -->
                    </div>
                @endif
                @if (Auth::user()->user_getEarningAndExpenses == 1)
                    <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('nameSucursal', 'Sucursal', ['class' => 'negrita']) !!}
                                                    {!! Form::select(
                                                        'nameSucursal',
                                                        $select_sucursales,
                                                        session()->has('nameSucursal') ? session()->get('nameSucursal') : 'Todos',
                                                        [
                                                            'id' => 'sucursalSelect6',
                                                            'class' => 'widthAll select-status select-search-hide',
                                                        ],
                                                    ) !!}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('nameFecha', 'Fecha', ['class' => 'negrita']) !!}
                                                    {!! Form::select(
                                                        'nameFecha',
                                                        [
                                                            'Mes' => 'Mes',
                                                            'Mes Anterior' => 'Mes Anterior',
                                                            'Semana' => 'Semana',
                                                            'Hoy' => 'Hoy',
                                                            'Ayer' => 'Ayer',
                                                            'Rango Fechas' => 'Rango Fechas',
                                                        ],
                                                        session()->has('nameFecha') ? session()->get('nameFecha') : 'Mes',
                                                        [
                                                            'id' => 'fechaSelect6',
                                                            'class' => 'widthAll select-status select-search-hide',
                                                            'placeholder' => 'Seleccione una opción',
                                                        ],
                                                    ) !!}
                                                </div>
                                            </div>
                                            <div class="col-md-12 fecha-rango6">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        {!! Form::label('nameFechaInicio', 'Fecha Inicio', ['class' => 'negrita']) !!}
                                                        <div class="form-group">
                                                            <input type="text" class="form-control datepicker"
                                                                name="fechaInicio" id="fechaInicial6"
                                                                placeholder="DD/MM/AAAA" autocomplete="off"
                                                                value="{{ session()->has('fechaInicio') ? session()->get('fechaInicio') : '' }}">
                                                            <span class="input-group-addon"><i
                                                                    class="glyphicon glyphicon-calendar"></i></span>
                                                        </div><!-- input-group -->
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group  form-group">
                                                        {!! Form::label('nameFechaFinal', 'Fecha Final', ['class' => 'negrita']) !!}
                                                        <div class="form-group">
                                                            <input type="text" class="form-control datepicker"
                                                                name="fechaFinal" id="fechaFinal6"
                                                                placeholder="DD/MM/AAAA" autocomplete="off"
                                                                value="{{ session()->has('fechaFinal') ? session()->get('fechaFinal') : '' }}">
                                                            <span class="input-group-addon"><i
                                                                    class="glyphicon glyphicon-calendar"></i></span>
                                                        </div><!-- input-group -->
                                                    </div>
                                                </div>
                                            </div>
                                            <button id="filterButton6" class="btn btn-primary">Filtrar</button>
                                            <canvas id="myChart6"></canvas>
                                        </div>
                                    </div><!-- col-md-7 -->

                                </div><!-- row -->
                            </div><!-- panel-body -->
                        </div><!-- panel -->
                    </div>
                @endif

            </div><!-- row -->
            <div class="col-md-12">
                <div class="panel-group" id="accordion3">
                    <div class="panel panel-info" style="border: ;">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion3" href="#collapseOne3">
                                    <i class="glyphicon glyphicon-info-sign"></i> <span> Movimientos pendientes de
                                        cancelación
                                        {{ count($movimientosCancelados) }}</span>
                                </a>
                            </h4>
                        </div>
                        <div id="collapseOne3" class="panel-collapse collapse" style="padding: 5px">
                            <table id="shTableDashBoard" class="table table-striped table-bordered">
                                <thead class="">
                                    <tr>
                                        <th>ID</th>
                                        <th>MODULO</th>
                                        <th>FOLIO</th>
                                        <th>UUID</th>
                                        <th>ESTATUS</th>
                                        <th>ESTADO CONSULTA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($movimientosCancelados as $mov)
                                        <tr>
                                            <td style="font-size: 13px;">{{ $mov->canceledCfdi_id }}</td>
                                            <td style="font-size: 13px;">{{ $mov->canceledCfdi_module }}</td>
                                            <td style="font-size: 13px;">{{ $mov->canceledCfdi_moduleID }}</td>
                                            <td style="font-size: 13px;">{{ $mov->canceledCfdi_Uuid }}</td>
                                            <td style="font-size: 13px;">{{ $mov->canceledCfdi_status }}</td>
                                            <td>
                                                <a href="javascript:void(0);"
                                                    onclick="verEstadoConsulta('{{ $mov->canceledCfdi_Uuid }}')">Ver
                                                    estado de la consulta</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div><!-- panel -->
                </div><!-- panel-group -->
            </div>
        </div><!-- contentpanel -->

    </div><!-- mainpanel -->
    </div><!-- mainwrapper -->
    <script>
        function verEstadoConsulta(uuid) {
            swal({
                title: '¿Estás seguro de consultar el estado de la factura?',
                text: 'Esta acción consumirá un timbre de tu paquete de timbres',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
                    }).then((willDelete) => {
                    if (willDelete) {
                    // Usuario confirmó, proceder con la consulta
                    $.ajax({
                        type: 'GET',
                        url: '/consultarEstado',
                        data: {
                            uuid: uuid
                        },
                        success: function(response) {
                            // Mostrar el estado de la consulta en un modal o donde prefieras
                            showModalWithContent(response);
                        },
                        error: function(xhr, status, error) {
                            swal({
                                title: 'Error',
                                text: 'Ocurrió un error al consultar el estado de la factura',
                                icon: 'error',
                            });
                        }
                    });
                }
            });
        }

        function showModalWithContent(content) {
            console.log(content);
            //como co
            //como content tiene uuid, estado, detalle lo que haremos será mostrarlo en un modal
            //primero sacamos estado y detalle
            var estado = content.estado;
            var detalle = content.detalle;
            // Aquí puedes mostrar el contenido de la consulta en un modal. Usaremos sweetalert2
            swal({
                title: estado,
                text: detalle,
                icon: 'info',
            });
        }
    </script>

    <script src="{{ asset('js/flot/jquery.flot.min.js') }}"></script>
    <script src="{{ asset('js/flot/jquery.flot.resize.min.js') }}"></script>
    <script src="{{ asset('js/flot/jquery.flot.spline.min.js') }}"></script>
    <script src="{{ asset('js/graficos.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/language/DatePicker/datePicker.js') }}"></script>
@stop
