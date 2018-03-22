<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Partida extends Model
{
    public $primaryKey  = 'id_partida';
    protected $table = 'partidas';

    public function piezas() {
        return $this->hasMany('App\Pieza', 'id_partida');
    }
}
