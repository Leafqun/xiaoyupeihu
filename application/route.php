<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;

Route::any('/login','UserController/login');
Route::get('/logout','UserController/logout');
Route::any('/register','UserController/register');
Route::post('/updateUserPic','UserController/updateUserPic');
Route::post('/updateUserPwd','UserController/updateUserPwd');
Route::post('/updateUserPhone','UserController/updateUserPhone');
Route::get('/getUserInfo','UserController/getUserInfo');


return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

];
