<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToPatientVitalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientVitals', function (Blueprint $table) {
            $table->string('units')->after('udid');
            $table->dateTime('takeTime')->after('units');
            $table->dateTime('startTime')->after('takeTime');
            $table->dateTime('endTime')->after('startTime');
            $table->enum('addType',['Manual','Sync'])->after('endTime');
            $table->text('comment')->after('addType');
            $table->string('createdType')->after('comment');
            $table->string('deviceInfo')->after('createdType');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientVitals', function (Blueprint $table) {
            $table->dropColumn('units');
            $table->dropColumn('takeTime');
            $table->dropColumn('startTime');
            $table->dropColumn('endTime');
            $table->dropColumn('addType',['Manual','Sync']);
            $table->dropColumn('comment');
            $table->dropColumn('createdType');
            $table->dropColumn('deviceInfo');
        });
    }
}
