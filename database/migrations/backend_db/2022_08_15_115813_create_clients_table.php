<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            // $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sector_id');
            $table->string('name');
            $table->string('sexe', 10);
            $table->string('phone');
            $table->string('email')->unique();
            $table->string('cni', 15);
            $table->date('date_of_issue')->nullable();
            $table->string('delivered_in')->nullable();
            $table->string('location')->nullable();
            $table->string('password');
            $table->string('numero_comptoir',10);
            $table->string('numero_registre_de_commerce',30);
            $table->string('avatar', 50)->nullable();
            $table->integer('created_by');
            $table->foreign('sector_id')->references('id')->on('sectors');
            $table->softDeletes();
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
        Schema::dropIfExists('clients');
    }
}
