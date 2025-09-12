<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('description');
            $table->enum('status', ['pending', 'active', 'close']);
            $table->bigInteger('dapertement_id')->unsigned();
            $table->foreign('dapertement_id')->references('id')->on('dapertements')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->bigInteger('ticket_id')->unsigned();
            $table->foreign('ticket_id')->references('id')->on('tickets')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->bigInteger('staff_id')->unsigned();
            $table->foreign('staff_id')->references('id')->on('staffs')->onUpdate('RESTRICT')->onDelete('RESTRICT');
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
        Schema::dropIfExists('actions');
    }
}
