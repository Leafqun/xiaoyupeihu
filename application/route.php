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
Route::any('/managerLogin', 'LoginController/login');

Route::any('/login','UserController/login');
Route::get('/logout','UserController/logout');
Route::any('/register','UserController/register');
Route::post('users/updateUserPic','UserController/updateUserPic');
Route::any('users/updateUserName','UserController/updateUserName');
Route::post('users/updateUserPwd','UserController/updateUserPwd');
Route::post('users/updateUserPhone','UserController/updateUserPhone');
Route::get('users/getUserInfo','UserController/getUserInfo');
Route::get('users/getAllUsers','UserController/getAllUsers');
Route::get('users/getAllGroupFromUser','UserController/getAllGroupFromUser');
Route::get('users/getAllFriendFromUser','UserController/getAllFriendFromUser');
Route::post('users/addFriend', 'UserController/addFriend');
Route::post('users/deleteFriend', 'UserController/deleteFriend');

Route::get('groups/getGroupList','GroupController/getGroupList');
Route::get('groups/getGroupInfo','GroupController/getGroupInfo');
Route::get('groups/getAllUserFromGroup','GroupController/getAllUserFromGroup');
Route::post('groups/registerGroup', 'GroupController/registerGroup');
Route::any('groups/joinGroup', 'GroupController/joinGroup');
Route::any('groups/updateGroupPic', 'GroupController/updateGroupPic');
Route::any('groups/updateGroupName', 'GroupController/updateGroupName');

Route::get('posts/getPostList', 'PostController/getPostList');
Route::any('posts/getPostListFromUser', 'PostController/getPostListFromUser');
Route::get('posts/getPostInfo', 'PostController/getPostInfo');
Route::get('posts/getPostPic', 'PostController/getPostPic');
Route::post('posts/insertPost', 'PostController/insertPost');
Route::any('posts/deletePost', 'PostController/deletePost');
Route::any('posts/getPostListFromFriends','PostController/getPostListFromFriends');
Route::any('posts/getPostListFromGroup','PostController/getPostListFromGroup');
Route::any('posts/getPostListByFiltering','PostController/getPostListByFiltering');
Route::any('posts/addLikes','PostController/addLikes');

Route::any('devs/addDev', 'DevController/addDev');
Route::any('devs/updateDevInfo', 'DevController/updateDevInfo');
Route::any('devs/deleteDev', 'DevController/deleteDev');
Route::any('devs/getDevList', 'DevController/getDevList');
Route::get('devs/getDevInfo', 'DevController/getDevInfo');

Route::get('comments/getCommentList', 'CommentController/getCommentList');
Route::get('comments/insertComment', 'CommentController/insertComment');


return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

];
