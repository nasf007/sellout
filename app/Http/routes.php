<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return Redirect::to("index.html");
});
Route::get('/reporte', function () {
    return view("reporte");
});
Route::get('/reportecobertura', function () {
    return view("reportecobertura");
});
Route::group(['prefix'=>'ws'],function(){
	Route::any('login', 'UsuariosController@login');

	Route::any('sucursalesporpais', 'SucursalesController@sucursales_por_pais');
	Route::any('sucursalesporpais2', 'SucursalesController@sucursales_por_pais2');//problem

	Route::any('puntosventasporsucursal', 'PuntosVentasController@puntosventas_por_sucursal');
	Route::any('puntosventasporplantilla', 'PuntosVentasController@puntosventas_por_plantilla');
	Route::any('seriesporcategoria', 'SeriesController@series_por_categoria');
	Route::any('modelosporserie','ModelosController@modelos_por_serie');
	Route::any('filtro','VentasController@filtro');
	Route::any('exportarexcel','VentasController@exportarexcel');
	Route::any('exportarexcelTopSeller', 'VentasController@exportarexcelTopSeller');//exporta dashboard excel
	Route::any('exportarpdfTopSeller', 'VentasController@exportarpdfTopSeller');//exporta dashboard pdf
	Route::any('obtenerregistros', 'VentasController@obtenerregistros');
	Route::any('obtenersemanaventa','VentasController@semana_consulta');
	//Route::any('obtenerconsultaventasemana','VentasController@semana_venta_consulta');///mi controlador
	Route::resource('obtenersucursal', 'SucursalesController');
	Route::any('obtenerselloutpuntoventa','VentasController@sell_out_punto_ventas');
	Route::any('obtenerventasporsemana','VentasController@ventas_por_semana');
	Route::any('rutawilson','VentasController@joinwilson');//ruta para hacer lo de plantillas y categorias pantillas
	Route::any('actualizarregistro','VentasController@agregarItem');
	Route::any('tendenciaPorCategoria', 'DashboardSelloutVentasController@tendenciaPorCategoria');
    Route::any('tendenciaPorSerie', 'DashboardSelloutVentasController@tendenciaPorSerie');
	Route::post('upload', 'VentasController@ImportarExcel');
    Route::any('obtenercategoriasplantillasporsucursal', 'CategoriasPlantillasController@obtener_categorias_por_sucursal');
    
    Route::any('filtrarplantilla', 'PlantillasController@filtrarplantilla');//filtro de plantilla por sucursal y modelo

    Route::any('calcularcobertura', 'DashCoberturaController@calcularCobertura');
    Route::any('exportarexcelcobertura', 'DashCoberturaController@exportarexcel');
    Route::any('obtenerTop5Model','Top15ModelSelloutController@obtenerTop5Model');
	Route::any('sucursales_por_usuario', 'SucursalesController@sucursales_por_usuario');


	Route::resource('usuarios', 'UsuariosController');
	Route::resource('tiposusuarios', 'TiposUsuariosController');
	Route::resource('categorias', 'CategoriasController');
	Route::resource('modelos', 'ModelosController');
	Route::resource('series', 'SeriesController');
	Route::resource('sinonimos', 'SinonimosController');
	Route::resource('paises', 'PaisesController');
	Route::resource('sucursales', 'SucursalesController');
	Route::resource('puntosventas', 'PuntosVentasController');
	Route::resource('inventarios', 'VentasController');
	Route::resource('permisos', 'PermisosController');
	Route::resource('ventaspendientes', 'VentasPendientesController');
	Route::resource('top15modelsellout', 'Top15ModelSelloutController');
	Route::resource('top15pdvsellout', 'Top15PDVSelloutController');
    Route::resource('dashboardSelloutVentas', 'DashboardSelloutVentasController');
    Route::resource('cuentas', 'CuentasController');
    Route::resource('plantillas', 'PlantillasController');
    Route::resource('categoriasplantillas', 'CategoriasPlantillasController');
});