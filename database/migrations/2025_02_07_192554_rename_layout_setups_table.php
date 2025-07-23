<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameLayoutSetupsTable extends Migration
{
    public function up()
    {
        Schema::rename('layout_setup', 'layout_setups'); // Přejmenování tabulky
    }

    public function down()
    {
        Schema::rename('layout_setups', 'layout_setup'); // Vrácení zpět v případě rollbacku
    }
}