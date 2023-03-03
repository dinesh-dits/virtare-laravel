<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PatientReferalCountProcedure4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `referalCount`;";
        DB::unprepared($procedure);
        $procedure = "
        CREATE PROCEDURE  referalCount() 
        BEGIN
        SELECT(COUNT(patientReferrals.patientId)) AS total,
        CONCAT(referrals.firstName,' ',referrals.middleName,' ',referrals.lastName) AS text,
        referrals.udid AS referralId
        FROM
        referrals
        LEFT JOIN patientReferrals
        ON patientReferrals.referralId=referrals.id
        GROUP BY
        patientReferrals.referralId;
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
