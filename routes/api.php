<?php

use Illuminate\Http\Request;
use App\Partida;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// RUTAS PARA LOGIN, REGISTRO Y LOGOUT
Route::post('register', 'Auth\RegisterController@register');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout');

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('test', function () {
        $user = \Auth::user();
        return $user;
    });

    Route::get('partida/{id_partida}', function($id_partida) {
        try {
            $partida = Partida::where('id_partida','=',$id_partida)->first();
            $piezas = $partida->piezas;
            $estado = "OK";
            $mensaje = "Se ha obtenido la partida con éxito.";
        } catch (Exception $e) {
            $estado = "KO";
            $mensaje = "Se ha producido un error al obtener la partida.";
        }
        return response()->json(
            ['estado' => $estado,
            'mensaje' => $mensaje,
            'partida' => $partida
        ]);
    });
});