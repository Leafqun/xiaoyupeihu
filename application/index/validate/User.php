<?php
/**
 * Created by PhpStorm.
 * User: Leafqun
 * Date: 2017/9/19
 * Time: 12:10
 */
namespace app\index\validate;


use think\Validate;

class User extends Validate
{
    protected $rule = [
        '__token__'  =>  'require|token',
    ];
}