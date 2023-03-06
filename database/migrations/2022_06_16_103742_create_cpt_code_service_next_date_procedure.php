<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCptCodeServiceNextDateProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $search = "DROP PROCEDURE IF EXISTS `getVitalByCPTServices`;";
        DB::unprepared($search);
        $search = "
        CREATE PROCEDURE  getVitalByCPTServices(IN patientIdx INT,IN fromDate VARCHAR(100)) 
        BEGIN
        SELECT patientVitals.* FROM `cptCodeServices`
        LEFT JOIN 
        patientVitals ON patientVitals.patientId = cptCodeServices.patientId
        AND
        patientVitals.deviceTypeId = cptCodeServices.referenceId
        WHERE
        patientVitals.takeTime >= fromDate AND patientVitals.takeTime <= CURDATE()
        AND
        cptCodeServices.patientId = patientIdx;
        END;";
        DB::unprepared($search);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('cpt_code_service_next_date_procedure');
    }
}
