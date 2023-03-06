<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReferalWeekCountProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `referalWeekCount`;";
        DB::unprepared($procedure);
        $procedure = "
        CREATE PROCEDURE  referalWeekCount(IN patientIdx INT,IN fromDate VARCHAR(50),IN toDate VARCHAR(50),IN providerId INT,IN providerLocationId INT) 
        BEGIN
        SELECT(COUNT(patientReferrals.patientId)) AS total,
        CONCAT(referrals.firstName,' ',referrals.middleName,' ',referrals.lastName) AS text,
        referrals.udid AS id,
        dayname(patientReferrals.createdAt) as time
        FROM
        referrals
        LEFT JOIN patientReferrals
        ON patientReferrals.referralId=referrals.id
        WHERE (patientReferrals.patientId=patientIdx OR patientIdx='') AND patientReferrals.createdAt >= fromDate AND patientReferrals.createdAt <= toDate AND patientReferrals.deletedAt IS NULL AND referrals.deletedAt IS NULL
        AND (patientReferrals.providerId=providerId OR providerId='') AND (patientReferrals.providerLocationId=providerLocationId OR providerLocationId='')
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
