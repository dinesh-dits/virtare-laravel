<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfigMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configMessage')->insert(
            array(
                [
                    'id'=>19,
                    'udid' => Str::uuid()->toString(),
                    'subject' => 'Create New Account',
                    'messageBody' => 'Your account was successfully created with Virtare Health. Your password is <br> {password}',
                    'messageBodyParameter' => '["password"]',
                    'otherParameter' => '{"fromName": "Virtare Health"}',
                    'type' => 'patientFamilyAdd',
                    'entityType' => 'sendMail',
                ],
                [
                    'id'=>20,
                    'udid' => Str::uuid()->toString(),
                    'subject' => 'Create New Account',
                    'messageBody' => 'Your account was successfully created with Virtare Health. Your password is <br> {password}',
                    'messageBodyParameter' => '["password"]',
                    'otherParameter' => '',
                    'type' => 'patientFamilyAdd',
                    'entityType' => 'sendSMS',
                ],
        )
    );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configMessage', function (Blueprint $table) {
            //
        });
    }
}
