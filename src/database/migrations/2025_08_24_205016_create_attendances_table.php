<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->unique(['user_id','date']);
            $table->enum('status', ['off_duty', 'working', 'on_break', 'clocked_out'])->default('off_duty');
            // 'off_duty'=勤務外 'working'=勤務中 'on_break'=休憩中 'clocked_out'=退勤済
            $table->dateTime('clock_in');
            $table->dateTime('clock_out')->nullable();
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('attendances');
    }
}
