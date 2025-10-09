<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_id')->unique();
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
            $table->unsignedBigInteger('request_by');
            $table->foreign('request_by')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

            $table->datetime('clock_in');
            $table->datetime('clock_out');
            $table->text('remarks');

            $table->enum('status', ['pending', 'approved'])->default('pending');
            // pending=承認待ち approved=承認済み
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
        Schema::dropIfExists('requests');
    }
}
