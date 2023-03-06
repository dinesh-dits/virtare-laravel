<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PatientReferalMonthCountProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `referalMonthCount`;";
        DB::unprepared($procedure);
        $procedure = "
        CREATE PROCEDURE  referalMonthCount(IN patientIdx INT,IN fromDate VARCHAR(50),IN toDate VARCHAR(50)) 
        BEGIN
        SELECT(COUNT(patientReferrals.patientId)) AS total,
        CONCAT(referrals.firstName,' ',referrals.middleName,' ',referrals.lastName) AS text,
        referrals.udid AS id,
        DATE_FORMAT(patientReferrals.createdAt,'%b %d,%Y') as time
        FROM
        referrals
        LEFT JOIN patientReferrals
        ON patientReferrals.referralId=referrals.id
        WHERE (patientReferrals.patientId=patientIdx OR patientIdx='') AND patientReferrals.createdAt >= fromDate AND patientReferrals.createdAt <= toDate AND patientReferrals.deletedAt IS NULL AND referrals.deletedAt IS NULL
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
