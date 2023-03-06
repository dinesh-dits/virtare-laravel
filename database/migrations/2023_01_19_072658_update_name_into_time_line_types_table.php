<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateNameIntoTimeLineTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('timeLineTypes')->where('id', 10)->update([
            'name' => 'Health Data'
        ]);

        DB::table('timeLineTypes')->insert(
            array(
                [
                    'id'=>11,
                    'name' => 'Escalation',
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
        Schema::table('timeLineTypes', function (Blueprint $table) {
        });
    }
}
