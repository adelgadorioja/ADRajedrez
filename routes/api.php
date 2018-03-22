<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Partida;
use App\Pieza;
use App\User;

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
            $partida->piezas;
            $estado = "OK";
            $mensaje = "Se ha obtenido la partida con éxito.";
        } catch (Exception $e) {
            $estado = "KO";
            $mensaje = $e->getMessage();
        }
        return response()->json(
            ['estado' => $estado,
            'mensaje' => $mensaje,
            'partida' => $partida
        ]);
    });

    Route::get('en-espera', function() {
        try {
            $usuarios = User::select('id', 'name', 'email')->where('api_token', '!=', null)->get();
            $estado = "OK";
            $mensaje = "Se ha obtenido la lista de usuarios con éxito.";
        } catch (Exception $e) {
            $usuarios = null;
            $estado = "KO";
            $mensaje = $e->getMessage();
        }
        return response()->json(
            ['estado' => $estado,
            'mensaje' => $mensaje,
            'usuarios' => $usuarios
        ]);
    });

    Route::post('jugar', function() {
        try {
            $data = Input::all();
            $usuarioRival = $data['usuario-rival'];
            $usuarioCreador = \Auth::user()['id'];
            $partida = new Partida;
                $partida['jug_negras'] = $usuarioRival;
                $partida['jug_blancas'] = $usuarioCreador;
                $partida['turno'] = 1;
                $partida['fec_inicio'] = date("Y-m-d");
            $partida->save();

            // FALTA INTRODUCIR LAS PIEZAS EN LA PARTIDA (CREARLAS)

            $estado = "OK";
            $mensaje = "La partida se ha creado con éxito.";
        } catch(Exception $e) {
            $partida = null;
            $estado = "KO";
            $mensaje = $e->getMessage();
        }
        return response()->json([
            'estado' => $estado,
            'mensaje' => $mensaje,
            'partida' => $partida
        ]);
    });

    Route::post('mover', function() {
        try {
            $data = Input::all();
            $idPartida = $data['id_partida'];
            $idPieza = $data['id_pieza'];
            $nuevaFila = $data['fila'];
            $nuevaColumna = $data['columna'];

            // FALTA COMPROBACIÓN USUARIO

            $pieza = Pieza::where('id_pieza', '=', $idPieza)->where('id_partida', '=', $idPartida)->first();
            if($pieza != null) {

                // FALTA COMPROBACIÓN MOVIMIENTO

                $pieza['fila'] = $nuevaFila;
                $pieza['columna'] = $nuevaColumna;
                $pieza->save();
                $partida = Partida::where('id_partida','=',$idPartida)->first();
                $partida->piezas;
                $estado = "OK";
                $mensaje = "Se ha realizado el movimiento con éxito.";
            } else {
                $partida = null;
                $estado = "KO";
                $mensaje = "La pieza no existe o no corresponde con la partida.";
            }
        } catch (Exception $e) {
            $partida = null;
            $estado = "KO";
            $mensaje = $e->getMessage();
        }
        return response()->json(
            ['estado' => $estado,
            'mensaje' => $mensaje,
            'partida' => $partida
        ]);
    });
});