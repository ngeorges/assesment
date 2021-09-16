<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_imports', function (Blueprint $table) {
            $table->id();
            $table->integer('read_count');
            $table->integer('import_count')->nullable();
            $table->integer('queue')->nullable();
            $table->integer('import_skiped')->nullable();
            $table->integer('import_attempts');
            $table->integer('status')->nullable();
            $table->string('import_file');
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_imports');
    }
}
