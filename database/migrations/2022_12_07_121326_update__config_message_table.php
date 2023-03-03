<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateConfigMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configMessage')->where('id', 11)->update(['messageBody' => '<tr style="max-width:610px; width:100%;  box-sizing: border-box;">
        <td>

            <table align="center" valign="middle" cellpadding="0" cellspacing="0" border="0" style=" overflow:hidden; max-width: 610px; width:100%; box-sizing: border-box; margin:0; padding:30px 15px 10px;">
                <tr>
                    <td style="text-align:left; ">
                        <h2 style="text-align:center;">Reset Task</h2>
                        <p style="color:#575F62; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:20px; margin-top:0; margin-bottom:20px; padding:0;
                               text-align: left;">
                            Hello {fullName}<span style="text-transform:uppercase; font-weight:600;"></span>,
                        </p>								
                        <p style="color:#575f62; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0;">
                            A new task has been assigned to you in our Virtare Health Platform. Please login and being working on the task.
                        </p>
                         <p style="color:#575f62; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; margin-top:0; margin-bottom:20px; padding:0;">
                             Thank you for being part of the Virtare Team, we appreciate you!
                        </p>


                    </td>
                </tr>
                <tr>
                    <td align="center" valign="middle" style="text-align: center;">
                        <a style="text-decoration: none; display: inline-block; margin-bottom:15px;  font-weight: 400; " href="{link-to-request}" target="_blank"><img src="my_hostings/Content/TemplateImg/images/view-details.jpg" alt="" /></a>
                    </td>
                </tr>						
                
            </table>

        </td>
    </tr>
    <tr style=" box-sizing: border-box; background-color: #fff; height:22px">
        <td style="text-align:center; border-left:1px solid #eee; border-right:1px solid #eee;">&nbsp;</td>
    </tr>']);
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
