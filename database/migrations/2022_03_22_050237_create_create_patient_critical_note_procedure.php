<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCreatePatientCriticalNoteProcedure extends Migration
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
       "CREATE PROCEDURE  createPatientCriticalNote(IN udid varchar(255), IN patientId int, criticalNote text) 
        BEGIN
        INSERT INTO patientCriticalNotes (udid,patientId,criticalNote) 
        values(udid,patientId,criticalNote);
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
        Schema::dropIfExists('create_patient_critical_note_procedure');
    }
}
