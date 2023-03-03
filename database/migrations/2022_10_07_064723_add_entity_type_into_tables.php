<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEntityTypeIntoTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $dbName = config('database.connections.' . config('database.default') . '.database');
        $db = DB::select('SHOW TABLES');
        foreach ($db as $name) {
            $column = "SHOW COLUMNS FROM `" . $name->{'Tables_in_' . $dbName} . "`where field='entityType'";
            $column = DB::select($column);
            if (!$column) {
                if ($name->{'Tables_in_' . $dbName} != 'getUserDetails') {
                    Schema::table($name->{'Tables_in_' . $dbName}, function (Blueprint $table) {
                        $table->string('entityType')->default('Country')->nullable()->after('udid');
                    });
                }
            }
        }
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