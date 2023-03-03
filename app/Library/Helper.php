<?php

namespace App\Library;


use \Illuminate\Support\Facades\Crypt;

class Helper
{
    public static function encrypt($text)
    {
        return Crypt::encrypt($text);
    }

    public static function decrypt($code)
    {
        return Crypt::decrypt($code);
    }
}
