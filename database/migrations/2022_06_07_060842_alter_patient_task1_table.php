<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPatientTask1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientTasks', function (Blueprint $table) {
            $table->dropColumn('startTime');
            $table->timestamp('startTimeDate')->nullable()->after('priorityId');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientTasks', function (Blueprint $table) {
            $table->time('startTime')->nullable()->after('priorityId');
            $table->dropColumn('startTimeDate');
            
        });
    }
}
