<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectorSectorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collector_sector', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('collector_id')->index('collector_sector_collector_id_foreign');
            $table->unsignedBigInteger('sector_id')->index('collector_sector_sector_id_foreign');
            $table->foreign('collector_id')->references('id')->on('collectors');
            $table->foreign('sector_id')->references('id')->on('sectors');
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
        Schema::dropIfExists('collector_sectors');
    }
}
