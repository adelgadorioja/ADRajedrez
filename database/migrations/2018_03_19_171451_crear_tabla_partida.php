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
            $table->integer('jug_negras')->unsigned();
            $table->integer('jug_blancas')->unsigned();
            $table->integer('turno');
            $table->date('fec_inicio');
            // FOREIGN KEYS
            $table->foreign('jug_negras')->references('id')->on('users');
            $table->foreign('jug_blancas')->references('id')->on('users');
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
