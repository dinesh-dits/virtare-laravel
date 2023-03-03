<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcedureNotesListNewPatientId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `NotesListByPatientId`";
        DB::unprepared($procedure);

        $procedure = "CREATE PROCEDURE `NotesListByPatientId`(referenceId INT)
        BEGIN
        SELECT notes.udid as id, notes.date, notes.note, gc1.name as category, gc2.name as type, notes.createdBy as userId,s1.firstName,s1.lastName FROM `notes` 
        LEFT JOIN globalCodes as gc1 ON gc1.id = notes.categoryId
        LEFT JOIN globalCodes as gc2 ON gc2.id = notes.type
        LEFT JOIN staffs as s1 ON s1.userId = notes.createdBy AND s1.roleId = 1 OR s1.userId = notes.createdBy AND s1.roleId = 3
        WHERE notes.referenceId = referenceId AND notes.entityType = 'patient'
        UNION
        SELECT notes.udid as id, notes.date, notes.note, gc1.name as category, gc2.name as type, notes.createdBy as userId,s1.firstName,s1.lastName FROM `notes` 
        LEFT JOIN globalCodes as gc1 ON gc1.id = notes.categoryId
        LEFT JOIN globalCodes as gc2 ON gc2.id = notes.type
        LEFT JOIN staffs as s1 ON s1.userId = notes.createdBy AND s1.roleId = 1 OR s1.userId = notes.createdBy AND s1.roleId = 3
        WHERE notes.referenceId IN (SELECT id FROM appointments WHERE `patientID` = referenceId) AND notes.entityType = 'appointment'
        UNION
        SELECT notes.udid as id, notes.date, notes.note, gc1.name as category, gc2.name as type, notes.createdBy as userId,s1.firstName,s1.lastName FROM `notes` 
        LEFT JOIN globalCodes as gc1 ON gc1.id = notes.categoryId
        LEFT JOIN globalCodes as gc2 ON gc2.id = notes.type
        LEFT JOIN staffs as s1 ON s1.userId = notes.createdBy AND s1.roleId = 1 OR s1.userId = notes.createdBy AND s1.roleId = 3
        WHERE notes.referenceId IN (SELECT id FROM patientTimeLogs WHERE `patientID` = referenceId) AND notes.entityType = 'auditlog'
        UNION
        SELECT notes.udid as id, notes.date, notes.note, gc1.name as category, gc2.name as type, notes.createdBy as userId,s1.firstName,s1.lastName FROM `notes` 
        LEFT JOIN globalCodes as gc1 ON gc1.id = notes.categoryId
        LEFT JOIN globalCodes as gc2 ON gc2.id = notes.type
        LEFT JOIN staffs as s1 ON s1.userId = notes.createdBy AND s1.roleId = 1 OR s1.userId = notes.createdBy AND s1.roleId = 3
        WHERE notes.referenceId IN (SELECT id FROM patientVitals WHERE `patientID` = referenceId) AND notes.entityType = 'patientVital';
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
        Schema::dropIfExists('NotesListByPatientId');
    }
}
