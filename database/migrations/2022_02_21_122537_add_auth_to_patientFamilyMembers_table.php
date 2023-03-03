<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuthToPatientFamilyMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientFamilyMembers', function (Blueprint $table) {
            $table->boolean('vital')->default(1)->after('isPrimary');
            $table->boolean('messages')->default(1)->after('isPrimary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientFamilyMembers', function (Blueprint $table) {
            $table->dropColumn('vital');
            $table->dropColumn('messages');
        });
    }
}
