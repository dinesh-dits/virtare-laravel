<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateAddStaffConfigMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configMessage')->where('id', 2)->update([
            'messageBody' => 'You have been granted access to the Virtare Healthcare site Tethr. Here is link for future reference {base_url}
            <p>Click on this link to set up a password {base_url}/create-password</p>','messageBodyParameter' => ["base_url"]
        ]);
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
