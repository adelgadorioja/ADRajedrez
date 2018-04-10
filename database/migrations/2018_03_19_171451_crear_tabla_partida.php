<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrearTablaPartida extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partidas', function (Blueprint $table) {
            // COLUMNAS
            $table->increments('id_partida');
            $table->integer('jugador1')->unsigned();
            $table->integer('jugador2')->unsigned();
            $table->integer('turno');
            $table->date('fec_inicio');
            // FOREIGN KEYS
            $table->foreign('jugador1')->references('id')->on('users');
            $table->foreign('jugador2')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partidas');
    }
}
