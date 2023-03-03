<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientCriticalNoteProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $createPatientCriticalNote = "DROP PROCEDURE IF EXISTS `createPatientCriticalNote`;";
        DB::unprepared($createPatientCriticalNote);
        $createPatientCriticalNote = 
       "CREATE PROCEDURE  createPatientCriticalNote(IN providerId bigInt,IN udid varchar(255), IN patientId int, criticalNote text) 
        BEGIN
        INSERT INTO patientCriticalNotes (providerId,udid,patientId,criticalNote) 
        values(providerId,udid,patientId,criticalNote);
        END;";
        DB::unprepared($createPatientCriticalNote);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_critical_note_procedure');
    }
}
