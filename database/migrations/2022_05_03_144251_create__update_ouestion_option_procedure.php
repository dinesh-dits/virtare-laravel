<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUpdateOuestionOptionProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `UpdateQuestionOption`";
        DB::unprepared($procedure);
        $procedure =
            'CREATE PROCEDURE `UpdateQuestionOption`(IN data TEXT)
       BEGIN
        UPDATE
        questionOptions
                    SET
                    options =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.options")),
                    updatedBy =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.updatedBy"))
                    WHERE
                    questionOptions.udid = JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid"));
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
        // Schema::dropIfExists('_update_ouestion_option_procedure');
    }
}
