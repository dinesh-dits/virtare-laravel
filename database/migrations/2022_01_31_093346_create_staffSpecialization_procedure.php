<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffSpecializationProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getStaffSpecializationCount`;
        CREATE PROCEDURE `getStaffSpecializationCount`(timelineId INT(20))
        BEGIN
        IF timelineId = 122 THEN
                    SELECT count(specializationId) as total,
                    globalCodes.name as text
                   FROM staffs
                    JOIN globalCodes ON staffs.specializationId = globalCodes.id
                   WHERE staffs.createdAt > date_sub(now(), interval 1 day)
                   GROUP BY (staffs.specializationId);
        ELSEIF timelineId = 123 THEN
                    SELECT count(specializationId) as total,
                    globalCodes.name as text
                FROM staffs
                    JOIN globalCodes ON staffs.specializationId = globalCodes.id
                WHERE staffs.createdAt > date_sub(now(), interval 7 day)
                GROUP BY (staffs.specializationId);
        ELSEIF timelineID = 124 THEN
                        SELECT count(specializationId) as total,
                        globalCodes.name as text
                    FROM staffs
                        JOIN globalCodes ON staffs.specializationId = globalCodes.id
                    WHERE staffs.createdAt > date_sub(now(), interval 30 day)
                    GROUP BY (staffs.specializationId);
        ELSEIF timelineId = 125 THEN
                            SELECT count(specializationId) as total,
                            globalCodes.name as text
                        FROM staffs
                            JOIN globalCodes ON staffs.specializationId = globalCodes.id
                        WHERE staffs.createdAt > date_sub(now(), interval 1 year)
                        GROUP BY (staffs.specializationId);
        END IF;
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
        Schema::dropIfExists('staffSpecialization_procedure');
    }
}
