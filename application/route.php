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
Route::any('/updatePwd', 'LoginController/updatePwd');
Route::any('/getLoginInfo', 'LoginController/getLoginInfo');
Route::any('/updateName', 'LoginController/updateName');

Route::any('/login','UserController/login');
Route::get('/logout','UserController/logout');
Route::any('/register','UserController/register');
Route::any('users/deleteUser','UserController/deleteUser');
Route::any('users/updateUserPic','UserController/updateUserPic');
Route::any('users/updateUserName','UserController/updateUserName');
Route::any('users/updateUserPwd','UserController/updateUserPwd');
Route::any('users/updateUserPhone','UserController/updateUserPhone');
Route::get('users/getUserInfo','UserController/getUserInfo');
Route::get('users/getAllUsers','UserController/getAllUsers');
Route::get('users/getAllGroupFromUser','UserController/getAllGroupFromUser');
Route::get('users/getAllFriendFromUser','UserController/getAllFriendFromUser');
Route::any('users/addFriend', 'UserController/addFriend');
Route::any('users/deleteFriend', 'UserController/deleteFriend');
Route::get('users/getUserTotalNum','UserController/getUserTotalNum');
Route::get('users/getActiveUserTotalNum','UserController/getActiveUserTotalNum');
Route::get('users/getActiveUserCityStatistics','UserController/getActiveUserCityStatistics');

Route::get('groups/getGroupList','GroupController/getGroupList');
Route::get('groups/getGroupInfo','GroupController/getGroupInfo');
Route::get('groups/getGroup','GroupController/getGroup');
Route::get('groups/getAllUserFromGroup','GroupController/getAllUserFromGroup');
Route::post('groups/registerGroup', 'GroupController/registerGroup');
Route::any('groups/joinGroup', 'GroupController/joinGroup');
Route::any('groups/updateGroupPic', 'GroupController/updateGroupPic');
Route::any('groups/updateGroupName', 'GroupController/updateGroupName');

Route::get('posts/getPostList', 'PostController/getPostList');
Route::any('posts/getPostListFromUser', 'PostController/getPostListFromUser');
Route::get('posts/getPostInfo', 'PostController/getPostInfo');
Route::get('posts/getPostPic', 'PostController/getPostPic');
Route::any('posts/insertPost', 'PostController/insertPost');
Route::any('posts/deletePost', 'PostController/deletePost');
Route::any('posts/getPostListFromFriends','PostController/getPostListFromFriends');
Route::any('posts/getPostListFromGroup','PostController/getPostListFromGroup');
Route::any('posts/getPostListByFiltering','PostController/getPostListByFiltering');
Route::any('posts/addLikes','PostController/addLikes');
Route::get('posts/getMonthPostTotalNum','PostController/getMonthPostTotalNum');
Route::get('posts/getTodayPostNum','PostController/getTodayPostNum');
Route::any('posts/getLastWeekPosts','PostController/getLastWeekPosts');
Route::any('posts/getPostNumByMonth','PostController/getPostNumByMonth');
Route::any('posts/get7DaysWebsiteFlow','PostController/get7DaysWebsiteFlow');

Route::any('devs/addDev', 'DevController/addDev');
Route::any('devs/bindDev', 'DevController/bindDev');
Route::any('devs/isDevBinded', 'DevController/isDevBinded');
Route::any('devs/updateDevInfo', 'DevController/updateDevInfo');
Route::any('devs/deleteDev', 'DevController/deleteDev');
Route::any('devs/getDevList', 'DevController/getDevList');
Route::any('devs/getDevIdFromId', 'DevController/getDevIdFromId');
Route::any('devs/getUserDevid', 'DevController/getUserDevid');
Route::get('devs/getDevInfo', 'DevController/getDevInfo');
Route::get('devs/getDevTotalNum', 'DevController/getDevTotalNum');

Route::get('comments/getCommentList', 'CommentController/getCommentList');
Route::get('comments/insertComment', 'CommentController/insertComment');

Route::any('alarm/getAlarmList', 'AlarmController/getAlarmList');
Route::any('alarm/getMonthAlarmList', 'AlarmController/getMonthAlarmList');

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

];
