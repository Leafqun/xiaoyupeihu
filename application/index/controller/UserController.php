<?php
/**
 * Created by PhpStorm.
 * User: Leafqun
 * Date: 2017/9/18
 * Time: 23:40
 */

namespace app\index\controller;


use app\index\common\url;
use Firebase\JWT\JWT;
use think\Db;
use think\Request;

class UserController
{
    public function login(Request $request){
        header('Access-Control-Allow-Origin:*');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
        //获取请求中的用户名和密码
        $username = $request->param('username');
        $password = $request->param('password');
        //验证token
        $key = "example_key";
        $token = array(
            "iss" => "Leafqun",
            "aud" => "xiaoyupeihu",
            "iat" => 1356999524,
            "nbf" => 1357000000
        );
        $token = $request->param('token');
        if($token) {
            $decoded = JWT::decode($token, $key, array('HS256'));
            if ($decoded) return ['msg' => 'success'];
        }
        // 根据请求用户名获取数据库中的用户信息，有则返回用户信息，否则返回Null
        $user = Db::table('users')->where('userName', $username)->find();
        //登录判断
        if($user){
            if(strcmp($user['userPwd'], $password) === 0){
                $msg = 'success';
                //创建token
                $jwt = JWT::encode($token, $key);
                return ['msg' => $msg, 'token' => $jwt, 'userId' => $user['userId']];
            }else{
                $msg = '密码错误';
                return ['msg' => $msg];
            }
        }else{
            $msg = '用户不存在';
            return ['msg' => $msg];
        }
    }
    public function logout(){
        //清空token
        return ['msg' => '成功注销', 'token' => null, 'userId' => null];
    }
    public function register(Request $request){
        header('Access-Control-Allow-Origin:*');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
        //获取请求中的用户名和密码
        $username = $request->param('username');
        $password = $request->param('password');
        // 根据请求用户名获取数据库中的用户信息，有则返回用户信息，否则返回Null
        $user = Db::table('users')->where('userName', $username)->find();
        $msg = null;
        //判断用户名是否已经注册
        if(strcmp($user['userName'], $username) === 0) $msg = '用户名已注册';
        else{
            //添加用户
            $bool = Db::table('users')->insert(['userName' => $username, 'userPwd' => $password]);
            if($bool) $msg = '注册成功';
            else $msg = '注册失败';
        }
        return ['msg' => $msg];
    }
    public function getUserInfo(Request $request){
        $userId = $request->param('userId');
        if($userId){
            $user = Db::table('users')->where('userId', $userId)
                ->field('userId,userName,avatar,phone')->find();
            if($user) return ['msg' => 'success', 'user' => $user];
            else return ['msg' => '获取失败'];
        }else return ['msg' => '请求参数userId不存在'];

    }
    public function updateUserPwd(Request $request){
        $password = $request->param('password');
        if($password){
            $bool = Db::table('users')->where('userId', $request->param('userId'))
                ->update(['userPwd' => $password]);
            if($bool) return ['msg' => 'success'];
            else return ['msg' => '密码更新失败'];
        }else return ['msg' => '密码参数不存在'];
    }
    public function updateUserPhone(Request $request){
        $phone = $request->param('phone');
        if($phone){
            $bool = Db::table('users')->where('userId', $request->param('userId'))
                ->update(['phone' => $phone]);
            if($bool) return ['msg' => 'success'];
            else return ['msg' => '更新失败'];
        }else return ['msg' => '请求参数不存在'];
    }
    public function updateUserPic(Request $request){
        $avatar = $request->param('avatar');
        $pic = $request->file('pic');
        $tip = null;
        if($pic) {
            $info = $pic->move(url::$fileURL, '');
            if ($info) {
                $filename = $info->getFilename();
                //删除旧文件
                if(!empty($avatar)){
                    $fileurl = url::$fileURL . $avatar;
                    if(file_exists($fileurl)) unlink($fileurl);
                }
                $avatar = $filename;
                $tip = '文件上传成功';
            }else{
                $tip = '上传存储失败';
            }
        }else $tip =  '文件未上传或上传失败';
        $bool = Db::table('users')->where('userId', $request->param('userId'))
            ->update(['avatar' => $avatar]);
        if($bool) return ['msg' => 'success', 'tip' => 'ok'];
        else return ['msg' => 'error', 'tip' => $tip];
    }
}