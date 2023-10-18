@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="contentpanel">
        <div class="row row-stat">
            <div class="contenedor-formulario">
                <h2 class="text-black">Empresas actuales</h2>

                <form action="" method="post">
                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-body">
                        <div class="content">
                          <table class="table" id="tableTimbres">
                            <thead>
                              <tr>
                                <th>ID Empresa</th>
                                <th>Nombre Empresa</th>
                                <th>Timbres</th>
                                <th>Acción</th>
                              </tr>
                            </thead>
                            <tbody>
                              @foreach ($stamps as $stamp)
                              <tr>
                                <td>{{$stamp->companies_key}}</td>
                                <td>{{$stamp->companies_name}}</td>
                                <td>
                                  <input type="number" name="timbres" id="timbres" value="{{$stamp->companies_AvailableStamps == null ? 0 : $stamp->companies_AvailableStamps }}" class="form-control input-sm">
                                </td>
                                <td>
                                  <button class="btn btn-info btn-sm btn-agregar">
                                    <i class="glyphicon glyphicon-plus"></i>
                                  </button>
                                </td>
                              </tr>
                              @endforeach
                            </tbody>
                            <tfoot>
                              <tr>
                                <td colspan="2">Total</td>
                                <td id="totalTimbres"></td>
                                <td>
                                  <button class="btn btn-success btn-sm" id="agregar-timbres">
                                    Agregar Timbres
                                  </button>
                                </td>
                              </tr>
                            </tfoot>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>

            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
      $('.btn-agregar').click(function(e) {
        e.preventDefault();

        // Desactivar el enter en el input

        // Sacar fila de la tabla
        let fila = $(this).closest('tr');

        // Aumentar la cantidad de timbres del input
        let timbres = parseInt(fila.find('input').val());
        timbres++;
        fila.find('input').val(timbres);

        // Agregar el total de timbres en el footer
        let total = 0;
        $('#tableTimbres tbody tr').each(function() {
          total += parseInt($(this).find('input').val());
        });
        $('#totalTimbres').text(total);
      });

      // Poner change en el input de timbres
      $('input').change(function(e) {
        e.preventDefault();

        // Agregar el total de timbres en el footer
        let total = 0;
        $('#tableTimbres tbody tr').each(function() {
          total += parseInt($(this).find('input').val());
        });
        $('#totalTimbres').text(total);
      });
    });


    $('#agregar-timbres').click(function(e) {
      e.preventDefault();
      let json = armarJson();
      console.log(json);
      swal({
        title: '¿Estás seguro de agregar los timbres?',
        text: '¡No podrás revertir esta acción!',
        icon: "warning",
        buttons: true,
        dangerMode: true,
        buttons: ["Cancelar", "Aceptar"],
      }).then(willDelete => {
        if (willDelete) {
        // $("#loader").show();
          $.ajax({
            url: '/administrar-timbres/timbradoStore',
            type: "POST",
            data: {
              _token: '{{ csrf_token() }}',
              timbres: json
            },
            success: function(response) {
              console.log(response);
            $("#loader").hide();
              if (response.status == 'success') {
                swal({
                  title: 'Agregados',
                  text: 'Los timbres se han agregado correctamente',
                  icon: 'success'
                }).then(function(result) {
                  if (result) {
                    window.location.href = response.redirect;

                  }
                });
              }
            },
            error: function(error) {
              console.log(error);
            }
          });
        }
      });
    });


    function armarJson() {
      // Armar JSON con los datos de la tabla para agregar los timbres
      let json = [];
      $('#tableTimbres tbody tr').each(function() {
        let id = $(this).find('td:first-child').text();
        let timbres = $(this).find('input').val();
        json.push({id, timbres});
      });
      return json;
    }




  </script>
@endsection