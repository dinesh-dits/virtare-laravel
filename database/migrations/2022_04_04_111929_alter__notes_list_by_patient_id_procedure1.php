<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterNotesListByPatientIdProcedure1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $data = "DROP PROCEDURE IF EXISTS `NotesListByPatientId`;";
        DB::unprepared($data);
        $data = "CREATE PROCEDURE  NotesListByPatientId(IN referenceId INT)
        BEGIN 

(SELECT notes.id as newId, notes.udid as udid, notes.date as date, notes.note, gc1.name as category, gc2.name as type, notes.createdBy as userId,
s1.firstName,s1.lastName, pf.icon as flagIcon,flags.color AS flagColor, flags.name AS flagName FROM `notes`
LEFT JOIN globalCodes as gc1 ON gc1.id = notes.categoryId
LEFT JOIN globalCodes as gc2 ON gc2.id = notes.type
LEFT JOIN patientFlags AS pf ON pf.id = notes.patientFlagId
LEFT JOIN flags ON flags.id = pf.flagId
LEFT JOIN staffs as s1 ON s1.userId = notes.createdBy AND s1.roleId = 1 OR s1.userId = notes.createdBy AND s1.roleId = 3
WHERE notes.referenceId = referenceId AND notes.entityType = 'patient'
UNION
SELECT notes.id as newId, notes.udid as udid, notes.createdAt as date, notes.note, 'General' as category, 'Appointment' as type, notes.createdBy as userId,
s1.firstName,s1.lastName, pf.icon AS flagIcon,flags.color AS flagColor, flags.name AS flagName FROM `notes`
LEFT JOIN globalCodes as gc1 ON gc1.id = notes.categoryId
LEFT JOIN globalCodes as gc2 ON gc2.id = notes.type
LEFT JOIN patientFlags AS pf ON pf.id = notes.patientFlagId
 LEFT JOIN flags ON flags.id = pf.flagId
LEFT JOIN staffs as s1 ON (s1.userId = notes.createdBy AND s1.roleId = 1) OR (s1.userId = notes.createdBy AND s1.roleId = 3)
WHERE notes.referenceId IN (SELECT id FROM appointments WHERE `patientID` = referenceId) AND notes.entityType = 'appointment'
UNION
SELECT notes.id as newId, notes.udid as udid, notes.createdAt as date, notes.note, 'General' as category, 'Timelogs' as type, notes.createdBy as userId,s1.firstName,
s1.lastName, pf.icon as flagIcon,flags.color AS flagColor, flags.name AS flagName FROM `notes`
LEFT JOIN globalCodes as gc1 ON gc1.id = notes.categoryId
LEFT JOIN globalCodes as gc2 ON gc2.id = notes.type
LEFT JOIN patientFlags AS pf ON pf.id = notes.patientFlagId
 LEFT JOIN flags ON flags.id = pf.flagId
LEFT JOIN staffs as s1 ON (s1.userId = notes.createdBy AND s1.roleId = 1) OR (s1.userId = notes.createdBy AND s1.roleId = 3)
WHERE notes.referenceId IN (SELECT id FROM patientTimeLogs WHERE `patientID` = referenceId) AND notes.entityType = 'auditlog'
UNION
SELECT notes.id as newId, notes.udid as udid, notes.createdAt as date, notes.note, 'General' as category, 'Vitals' as type, notes.createdBy as userId,s1.firstName,
s1.lastName, pf.icon as flagIcon,flags.color AS flagColor, flags.name AS flagName FROM `notes`
LEFT JOIN globalCodes as gc1 ON gc1.id = notes.categoryId
LEFT JOIN globalCodes as gc2 ON gc2.id = notes.type
LEFT JOIN patientFlags AS pf ON pf.id = notes.patientFlagId
 LEFT JOIN flags ON flags.id = pf.flagId
LEFT JOIN staffs as s1 ON (s1.userId = notes.createdBy AND s1.roleId = 1) OR (s1.userId = notes.createdBy AND s1.roleId = 3)
WHERE notes.referenceId IN (SELECT id FROM patientVitals WHERE `patientID` = referenceId) AND notes.entityType = 'patientVital')
ORDER BY newId DESC;





END;";
        DB::unprepared($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
