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

            generarPiezas($partida, $usuarioRival, $usuarioCreador);

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
                if(comprobarMovimiento($pieza, $nuevaFila, $nuevaColumna)) {
                    $pieza['fila'] = $nuevaFila;
                    $pieza['columna'] = $nuevaColumna;
                    $pieza->save();
                    $partida = Partida::where('id_partida','=',$idPartida)->first();
                    $partida->piezas;
                    $estado = "OK";
                    $mensaje = "Se ha realizado el movimiento con éxito.";
                } else {
                    $partida = Partida::where('id_partida','=',$idPartida)->first();
                    $partida->piezas;
                    $estado = "KO";
                    $mensaje = "El movimiento no es correcto.";
                }          
            } else {
                $partida = null;
                $estado = "KO";
                $mensaje = "Ha ocurrido un error al realizar el movimiento.";
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

    function generarPiezas($partida, $usuarioRival, $usuarioCreador) {
        // PEONES USUARIORIVAL
        for ($columna=1; $columna < 9; $columna++) { 
            $pieza = new Pieza;
                $pieza['id_partida'] = $partida['id_partida'];
                $pieza['id_usuario'] = $usuarioRival;
                $pieza['color'] = 1;
                $pieza['fila'] = 2;
                $pieza['columna'] = $columna;
                $pieza['tipo'] = 'PEON';
            $pieza->save();
        }
        // PEONES USUARIOCREADOR
        for ($columna=1; $columna < 9; $columna++) { 
            $pieza = new Pieza;
                $pieza['id_partida'] = $partida['id_partida'];
                $pieza['id_usuario'] = $usuarioCreador;
                $pieza['color'] = 2;
                $pieza['fila'] = 7;
                $pieza['columna'] = $columna;
                $pieza['tipo'] = 'PEON';
            $pieza->save();
        }  
    }

    function comprobarMovimiento($pieza, $nuevaFila, $nuevaColumna) {
        $movimientoCorrecto = false;

        if($nuevaFila<1 || $nuevaFila>8 || $nuevaColumna<1 || $nuevaColumna>8) {
            return $movimientoCorrecto;
        }

        $color = $pieza['color'];
        $tipo = $pieza['tipo'];
        $filaActual = $pieza['fila'];
        $columnaActual = $pieza['columna'];

        if($tipo == 'PEON') {
            // COMPROBACIÓN COLUMNA
            if($columnaActual != $nuevaColumna) {
                return $movimientoCorrecto;
            }
            // COMPROBACIÓN MOVIMIENTO HACIA ALANTE DE UNA CASILLA
            if(($color == 1 && $nuevaFila-$filaActual != 1) || ($color == 2 && $nuevaFila-$filaActual != -1)) {
                return $movimientoCorrecto;
            }
            
            $movimientoCorrecto = true;
        }

        comprobarPiezaEliminada($pieza, $nuevaFila, $nuevaColumna);

        return $movimientoCorrecto;
    }

    function comprobarPiezaEliminada($pieza, $nuevaFila, $nuevaColumna) {
        $id_partida = $pieza['id_partida'];
        $piezaEliminada = Pieza::where('id_partida', '=', $id_partida)->where('fila','=', $nuevaFila)->where('columna','=', $nuevaColumna);
        if($pieza != null) {
            $piezaEliminada->delete();
        }
    }

});