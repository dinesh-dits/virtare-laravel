<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGetPatientVitalNotesByNotesIdProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getPatientVitalNotesByNotesId`;
        CREATE PROCEDURE `getPatientVitalNotesByNotesId`(IN notesID INT)
        BEGIN
        SELECT notes.id as newId, notes.udid as udid, notes.createdAt as date, notes.note, 'General' as category, 'Vitals' as type, notes.createdBy as userId,
        if(p1.firstName IS NULL, CONCAT(s1.firstName,' ',s1.lastName), CONCAT(p1.firstName,' ',p1.lastName)) AS addedBy,
        if(u1.profilePhoto IS NULL, u1.profilePhoto,u2.profilePhoto) AS profilePhoto, g1.name AS specialization,
        ' ' as flagIcon,flags.color AS flagColor, flags.name AS flagName FROM `notes`
        LEFT JOIN globalCodes as gc1 ON gc1.id = notes.categoryId
        LEFT JOIN globalCodes as gc2 ON gc2.id = notes.type
        LEFT JOIN flags ON flags.id = notes.flagId
        LEFT JOIN staffs as s1 ON (s1.userId = notes.createdBy AND s1.roleId = 1) OR (s1.userId = notes.createdBy AND s1.roleId = 3)
        LEFT JOIN globalCodes AS g1 ON g1.id=s1.specializationId
        LEFT JOIN users as u1 ON u1.id=s1.userId
        LEFT JOIN patients as p1 ON p1.userId = notes.createdBy
        LEFT JOIN users as u2 ON u2.id=p1.userId
        WHERE notes.id = notesID AND notes.entityType = 'patientVital';
        END;";
        DB::unprepared($procedure);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('get_patient_vital_notes_by_notes_id_procedure');
    }
}
