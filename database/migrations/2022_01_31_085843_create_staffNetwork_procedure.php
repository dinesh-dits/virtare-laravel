<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffNetworkProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getStaffNeworkCount`;
        CREATE PROCEDURE `getStaffNeworkCount`(timelineId INT(20))
        BEGIN
        IF timelineId = 122 THEN
                    SELECT count(networkId) as total,
                    globalCodes.name as text
                   FROM staffs
                    JOIN globalCodes ON staffs.networkId = globalCodes.id
                   WHERE staffs.createdAt > date_sub(now(), interval 1 day)
                   GROUP BY (staffs.networkId);
        ELSEIF timelineId = 123 THEN
                    SELECT count(networkId) as total,
                    globalCodes.name as text
                FROM staffs
                    JOIN globalCodes ON staffs.networkId = globalCodes.id
                WHERE staffs.createdAt > date_sub(now(), interval 7 day)
                GROUP BY (staffs.networkId);
        ELSEIF timelineID = 124 THEN
                        SELECT count(networkId) as total,
                        globalCodes.name as text
                    FROM staffs
                        JOIN globalCodes ON staffs.networkId = globalCodes.id
                    WHERE staffs.createdAt > date_sub(now(), interval 30 day)
                    GROUP BY (staffs.networkId);
        ELSEIF timelineId = 125 THEN
                            SELECT count(networkId) as total,
                            globalCodes.name as text
                        FROM staffs
                            JOIN globalCodes ON staffs.networkId = globalCodes.id
                        WHERE staffs.createdAt > date_sub(now(), interval 1 year)
                        GROUP BY (staffs.networkId);
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
        Schema::dropIfExists('staffNetwork_procedure');
    }
}
