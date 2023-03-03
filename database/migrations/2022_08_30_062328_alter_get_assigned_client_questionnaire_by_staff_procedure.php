<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterGetAssignedClientQuestionnaireByStaffProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getAssignedClientQuestionnaireByStaff`";
        DB::unprepared($procedure);

        $procedure = "CREATE PROCEDURE `getAssignedClientQuestionnaireByStaff`(assignByUser INT,assignToUser INT,search Varchar(100))
        BEGIN
        SELECT cqt.*,qt.templateName,qt.templateTypeId,qt.udid as questionnaireTemplateUdid,gc.name as templateType,sf.firstName,sf.lastName, sf.udid as assignByUdid, CONCAT(sf.firstName, ' ',sf.lastName) AS assignBy, s.udid as assignToUdid, CONCAT(s.firstName, ' ',s.lastName) as assignTo,s.firstName as userFirstName,s.lastName as userLastName,cq.clientQuestionnaireTemplateId,cq.udid as clientQuestionnaireTemplateUdid,cq.referenceId as fillUpReferenceId, cq.entityType as fillUpEntityType
        FROM `clientQuestionnaireAssign` as cqt
        LEFT JOIN questionnaireTemplates as qt ON qt.questionnaireTemplateId = cqt.questionnaireTemplateId
        LEFT JOIN globalCodes as gc ON gc.id = qt.templateTypeId
        LEFT JOIN staffs as sf ON sf.userId = cqt.createdBy
        LEFT JOIN staffs as s ON s.id = cqt.referenceId
        LEFT JOIN clientQuestionnaireTemplates as cq ON cq.referenceId = cqt.referenceId AND cq.entityType = '246' AND cq.questionnaireTemplateId = cqt.questionnaireTemplateId AND cq.clientQuestionnaireAssignId = cqt.clientQuestionnaireAssignId
        WHERE cqt.entityType = '246'
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
        (CONCAT(userLastName, ' ', userFirstName))  LIKE CONCAT('%', search ,'%');
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
        //
    }
}
