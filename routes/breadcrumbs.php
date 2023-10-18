<?php
//home

use App\Models\catalogos\CAT_COMPANIES;
use Illuminate\Support\Facades\Crypt;

Breadcrumbs::for('home', function ($trail) {
    $trail->push('Inicio', route('dashboard.index'));
});
//companies
Breadcrumbs::for('catalogo.empresa.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Empresas', route('catalogo.empresa.index'));
});

Breadcrumbs::for('catalogo.empresa.create', function ($trail) {
    $trail->parent('catalogo.empresa.index');
    $trail->push('Crear Empresa', route('catalogo.empresa.create'));
});

Breadcrumbs::for('catalogo.empresa.show', function ($trail, $cat_empresas) {
    $trail->parent('catalogo.empresa.index');
    //como $empresas_edit viene encrptado, se debe desencriptar para poder usarlo
    $cat_empresas = Crypt::decrypt($cat_empresas);
    $empresas = CAT_COMPANIES::where('companies_id', $cat_empresas)->first();
    // dd($empresas);
    $trail->push($empresas->companies_name, route('catalogo.empresa.show', $empresas->companies_id));
});

Breadcrumbs::for('catalogo.empresa.edit', function ($trail, $cat_empresas) {
    $cat_empresas = Crypt::decrypt($cat_empresas);
    $empresas = CAT_COMPANIES::where('companies_id', $cat_empresas)->first();
    //ahora lo volvemos a encriptar para poder usarlo
    $cat_empresas = Crypt::encrypt($cat_empresas);
    $trail->parent('catalogo.empresa.show', $empresas);
    $trail->push('Editar Empresa', route('catalogo.empresa.edit', $cat_empresas));
});




