<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventTicketTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events_tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id');
            $table->string('title');
            $table->string('short_desc');
            $table->string('full_desc');
            $table->string('ticket_price');
            $table->string('ticket_currency');
            $table->string('ticket_image');
            $table->integer('status');
            $table->dateTime('updated_at');
            $table->dateTime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('events_tickets');
    }
}
