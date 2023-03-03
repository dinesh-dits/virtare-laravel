<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterworkFlowQueueStepActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workFlowQueueStepActions', function (Blueprint $table) {
            $table->string('customFormAssignedId')->after('workFlowQueueStepId');
            $table->string('assignStatus')->default('0')->after('customFormAssignedId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       
    }
}
