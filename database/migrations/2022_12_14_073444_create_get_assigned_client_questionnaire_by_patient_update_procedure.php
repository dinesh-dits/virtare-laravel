<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGetAssignedClientQuestionnaireByPatientUpdateProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getAssignedClientQuestionnaireByPatient`";
        DB::unprepared($procedure);

        $procedure = "CREATE PROCEDURE `getAssignedClientQuestionnaireByPatient`(assignByUser INT,assignToUser INT,search Varchar(100))
        BEGIN
        SELECT cqt.*,qt.templateName,qt.templateTypeId,qt.udid as questionnaireTemplateUdid,gc.name as templateType,sf.firstName,sf.lastName, sf.udid as assignByUdid, CONCAT(sf.firstName, ' ',sf.lastName) AS assignBy, p.udid as assignToUdid, CONCAT(p.firstName, ' ',p.lastName) as assignTo,p.firstName as userFirstName,p.lastName as userLastName,cq.clientFillUpQuestionnaireId,cq.udid as clientFillUpQuestionnaireUdid,cq.referenceId as fillUpReferenceId, cq.entityType as fillUpEntityType
        FROM `clientQuestionnaireAssign` as cqt
        LEFT JOIN questionnaireTemplates as qt ON qt.questionnaireTemplateId = cqt.questionnaireTemplateId
        LEFT JOIN globalCodes as gc ON gc.id = qt.templateTypeId
        LEFT JOIN staffs as sf ON sf.userId = cqt.createdBy
        LEFT JOIN patients as p ON p.id = cqt.referenceId
        LEFT JOIN clientFillUpQuestionnaire as cq ON cq.referenceId = cqt.referenceId AND cq.entityType = '247' AND cq.clientQuestionnaireAssignId = cqt.clientQuestionnaireAssignId
        WHERE cqt.entityType = '247'
        AND
        (cqt.createdBy=assignByUser OR assignByUser='')
        AND
        (cqt.referenceId=assignToUser OR assignToUser='')
        AND
        sf.deletedAt IS NULL
        GROUP BY cqt.clientQuestionnaireAssignId
        HAVING
        (qt.templateName LIKE CONCAT('%', search ,'%'))
        OR
        (sf.firstName LIKE CONCAT('%', search ,'%')) 
        OR
        (sf.lastName LIKE CONCAT('%', search ,'%')) 
        OR 
        (CONCAT(sf.firstName, ' ', sf.lastName))  LIKE CONCAT('%', search ,'%')
        OR
        (CONCAT(sf.lastName, ' ', sf.firstName))  LIKE CONCAT('%', search ,'%')
        OR
        (userFirstName LIKE CONCAT('%', search ,'%')) 
        OR
        (userLastName LIKE CONCAT('%', search ,'%')) 
        OR 
        (CONCAT(userFirstName, ' ', userLastName))  LIKE CONCAT('%', search ,'%')
        OR
        (CONCAT(userLastName, ' ', userFirstName))  LIKE CONCAT('%', search ,'%') ORDER BY cqt.createdAt DESC;
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
        // Schema::dropIfExists('get_assigned_client_questionnaire_by_patient_update_procedure');
    }
}
