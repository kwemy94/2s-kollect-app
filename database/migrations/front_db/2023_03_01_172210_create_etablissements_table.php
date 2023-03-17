<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEtablissementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('etablissements', function (Blueprint $table) {
            $table->id();
            $table->string('ets_name', 50); 
            $table->string('ets_email', 60);
            $table->tinyInteger('status')->nullable();
            $table->string('domain', 60)->nullable();
            $table->string('website', 60)->nullable();
            $table->string('activity', 50)->nullable();
            $table->string('address', 50)->nullable();
            $table->string('postal_box', 50)->nullable();
            $table->string('country', 50)->nullable();
            $table->string('region', 50)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('ets_phone', 50)->nullable();
            $table->string('logo', 50)->nullable();
            $table->text('settings')->nullable();
            $table->string('taxpayer_number', 100)->nullable()->unique('taxpayer_number');
            $table->string('trade_register_number', 100)->nullable()->unique('trade_register_number');
            $table->integer('number_user')->nullable()->default(1);
            $table->integer('user_created_id')->nullable();
            $table->integer('user_updated_id')->nullable();
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
        Schema::dropIfExists('etablissements');
    }
}
