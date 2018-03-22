<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pieza extends Model
{
    public $primaryKey  = 'id_pieza';
    protected $table = 'piezas';
    public $timestamps = false;
    
}
