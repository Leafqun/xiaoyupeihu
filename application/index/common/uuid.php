<?php
/**
 * Created by PhpStorm.
 * User: Leafqun
 * Date: 2017/10/25
 * Time: 16:04
 */

namespace app\index\common;


class uuid
{
    public static function uuid($prefix = '')
    {
        $chars = md5(uniqid(mt_rand(), true));
        return $prefix . $chars;
    }
}