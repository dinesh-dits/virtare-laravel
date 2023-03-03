<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PatientVitalGoalProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `patientVitalGoal`;
        CREATE PROCEDURE `patientVitalGoal`(patientIdx INT(20),vitalFieldIdX INT(20))
        BEGIN
SELECT IF(patientGoals.highValue IS NULL , vitalFields.high,patientGoals.highValue) as high , IF(patientGoals.highValue IS NULL , vitalFields.low,patientGoals.lowValue) as low FROM vitalFields  LEFT JOIN patientGoals on vitalFields.id = patientGoals.vitalFieldId AND patientGoals.`patientId` = patientIdx  AND patientGoals.`deletedAt` is NULL  WHERE  vitalFields.id = vitalFieldIdX;

END";
        DB::unprepared($procedure);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::dropIfExists('patientVitalGoal');
    }
}
