<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToPatientPhysiciansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientPhysicians', function (Blueprint $table) {
            $table->string('name',30)->after('patientId');
            $table->bigInteger('designationId')->unsigned()->after('patientId');
            $table->foreign('designationId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('userId')->unsigned()->after('patientId');
            $table->foreign('userId')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('phoneNumber',20)->after('designationId');
            $table->string('fax',50)->after('phoneNumber');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientPhysicians', function (Blueprint $table) {
            $table->dropColumn('name',30);
            $table->dropForeign('designationId')->unsigned();
            $table->dropColumn('phoneNumber',20);
            $table->dropColumn('fax',50);
        });
    }
}
