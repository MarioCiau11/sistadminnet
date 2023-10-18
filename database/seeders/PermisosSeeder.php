<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // ----->> Movimientos <<------
        Permission::create( ['name' => 'Afectar', 'guard_name' =>  'web', 'categoria' => 'Movimientos', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'Si el usuario tiene habilitada esta opción podrá Afectar movimientos a los que tiene permiso']);
       
        Permission::create(['name' => 'Cancelar', 'guard_name' =>  'web', 'categoria' => 'Movimientos', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'Si el usuario tiene habilitada esta opción podrá Cancelar movimientos a los que tiene permiso']);

        // ----->> Permisos de Configuración <<------
        Permission::create(['name' => 'Párametros Generales', 'guard_name' => 'web', 'categoria' => 'Permisos de Configuración', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol  tiene habilitada esta opción podrá acceder a la configuración: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create(['name' => 'Roles y Usuarios', 'guard_name' => 'web', 'categoria' => 'Permisos de Configuración', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol  tiene habilitada esta opción podrá acceder a la configuración: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create(['name' => 'Monedas', 'guard_name' => 'web', 'categoria' => 'Permisos de Configuración', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol  tiene habilitada esta opción podrá acceder a la configuración: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create(['name' => 'Formas Cobro/Pago', 'guard_name' => 'web', 'categoria' => 'Permisos de Configuración', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol  tiene habilitada esta opción podrá acceder a la configuración: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create(['name' => 'Condiciones de Crédito', 'guard_name' => 'web', 'categoria' => 'Permisos de Configuración', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol  tiene habilitada esta opción podrá acceder a la configuración: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create(['name' => 'Unidades', 'guard_name' => 'web', 'categoria' => 'Permisos de Configuración', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol  tiene habilitada esta opción podrá acceder a la configuración: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create(['name' => 'Unidades Empaque', 'guard_name' => 'web', 'categoria' => 'Permisos de Configuración', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol  tiene habilitada esta opción podrá acceder a la configuración: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create(['name' => 'Conceptos de Módulos', 'guard_name' => 'web', 'categoria' => 'Permisos de Configuración', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol  tiene habilitada esta opción podrá acceder a la configuración: CREAR/EDITAR/BAJA/VISUALIZAR']);
        
        // ----->> Permisos de Catálogos <<------

        Permission::create(['name' => 'Empresa', 'guard_name' => 'web', 'categoria' => 'Permisos de Catálogos', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol tiene habilitada esta opción podrá acceder al catálogo: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create(['name' => 'Sucursal', 'guard_name' => 'web', 'categoria' => 'Permisos de Catálogos', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol tiene habilitada esta opción podrá acceder al catálogo: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create(['name' => 'Almacen', 'guard_name' => 'web', 'categoria' => 'Permisos de Catálogos', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol tiene habilitada esta opción podrá acceder al catálogo: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create(['name' => 'Cuentas de Dinero', 'guard_name' => 'web', 'categoria' => 'Permisos de Catálogos', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol tiene habilitada esta opción podrá acceder al catálogo: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create(['name' => 'Instituciones Financieras', 'guard_name' => 'web', 'categoria' => 'Permisos de Catálogos', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol tiene habilitada esta opción podrá acceder al catálogo: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create(['name' => 'Conceptos de Gastos', 'guard_name' => 'web', 'categoria' => 'Permisos de Catálogos', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol tiene habilitada esta opción podrá acceder al catálogo: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create(['name' => 'Clientes', 'guard_name' => 'web', 'categoria' => 'Permisos de Catálogos', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol tiene habilitada esta opción podrá acceder al catálogo: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create(['name' => 'Proveedores', 'guard_name' => 'web', 'categoria' => 'Permisos de Catálogos', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol tiene habilitada esta opción podrá acceder al catálogo: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create(['name' => 'Artículos', 'guard_name' => 'web', 'categoria' => 'Permisos de Catálogos', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol tiene habilitada esta opción podrá acceder al catálogo: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create (['name' => 'Lista de Artículos', 'guard_name' => 'web', 'categoria' => 'Permisos de Catálogos', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol tiene habilitada esta opción podrá acceder al catálogo: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create (['name' => 'Agentes', 'guard_name' => 'web', 'categoria' => 'Permisos de Catálogos', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol tiene habilitada esta opción podrá acceder al catálogo: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create (['name' => 'Vehículos', 'guard_name' => 'web', 'categoria' => 'Permisos de Catálogos', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol tiene habilitada esta opción podrá acceder al catálogo: CREAR/EDITAR/BAJA/VISUALIZAR']);

        //----> Permisos de los modulos <----
         Permission::create (['name' => 'Cotización E', 'guard_name' => 'web', 'categoria' => 'Ventas', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol tiene habilitada esta opción podrá acceder al catálogo: CREAR/EDITAR/BAJA/VISUALIZAR']);

        Permission::create (['name' => 'Cotización C', 'guard_name' => 'web', 'categoria' => 'Ventas', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),'descripcion' => 'SI el rol tiene habilitada esta opción podrá acceder al catálogo: CREAR/EDITAR/BAJA/VISUALIZAR']);
    }
}
