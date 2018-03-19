<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrearTablaPiezas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('piezas', function (Blueprint $table) {
            // COLUMNAS
            $table->increments('id_pieza');
            $table->integer('id_partida')->unsigned();
            $table->integer('color');
            $table->integer('fila');
            $table->integer('columna');
            $table->string('tipo');
            // FOREIGN KEYS
            $table->foreign('id_partida')->references('id_partida')->on('partidas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('piezas');
    }
}
