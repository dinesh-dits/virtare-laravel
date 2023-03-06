<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcedureGetEventColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `getWorkflowEventColumn`;';

        DB::unprepared($procedure);

        $procedure ='CREATE PROCEDURE  getWorkflowEventColumn(IN eventId varchar(255)) 
        BEGIN
         SELECT workflowEventsColumns.*, (
                SELECT 
                  JSON_ARRAYAGG(
                      JSON_OBJECT(
                        "id", dataTypeOperators.udid, "operator", operators.operatorTitle, "symbol", operators.symbol
                      )
                    
                  ) 
                FROM 
                  dataTypeOperators
                  
                INNER join operators on operators.operatorsId = dataTypeOperators.operatorId
                WHERE 
                  dataTypeOperators.dataTypeId = workflowEventsColumns.dataType
                  AND dataTypeOperators.deletedAt IS NULL
              ) as operator from workflowEventsColumns Inner Join workflowEvents on workflowEvents.workflowEventId = workflowEventsColumns.workflowEventId where (workflowEvents.udid = eventId OR eventId = "") AND (workflowEventsColumns.deletedAt IS NULL) AND (workflowEvents.deletedAt IS NULL) ;
        END;';

        DB::unprepared($procedure);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('getWorkflowEventColumn');
    }
}
