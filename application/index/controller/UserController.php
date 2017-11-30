<?php
/**
 * Created by PhpStorm.
 * User: Leafqun
 * Date: 2017/9/18
 * Time: 23:40
 */

namespace app\index\controller;


use app\index\common\url;

use app\index\common\uuid;
use Firebase\JWT\JWT;
use think\Controller;
use think\Db;
use think\Request;

class UserController extends Controller
{
    /*protected $beforeActionList = [
        'cors'
    ];
    protected function cors(){
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    }*/
    public function _initialize()
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    }
    public function login(Request $request){
        //获取请求中的用户名和密码
        $name = $request->param('username');
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
        $user = Db::table('users')->where('name', $name)->find();
        if(empty($user)) $user = Db::table('users')->where('tel_num', $name)->find();
        //登录判断
        if($user){
            if(strcmp($user['password'], $password) === 0){
                $msg = 'success';
                //创建token
                $jwt = JWT::encode($token, $key);
                return ['msg' => $msg, 'token' => $jwt, 'userid' => $user['userid'], 'id' => $user['id']];
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
        return ['msg' => '成功注销', 'token' => null, 'userid' => null];
    }
    public function register(Request $request){
        //获取请求中的用户名和密码
        $username = $request->param('username');
        $password = $request->param('password');
        // 根据请求用户名获取数据库中的用户信息，有则返回用户信息，否则返回Null
        if($username) {
            $user1 = Db::table('users')->where('tel_num', $username)->field('tel_num')->find();
        }else $user1 = null;
        $msg = null;
        //判断用户名是否已经注册
        if($user1) $msg = '手机号已注册';
        else{
            //添加用户
            $bool = Db::table('users')
                ->insert(['name' => $username, 'password' => $password,
                    'tel_num' => $username, 'userid' => uuid::uuid(), 'phone_num' => '1', 'admin' => '45']);
            if($bool) {
                $msg = '注册成功';
                $id = Db::table('users')->field('id')->where('tel_num', $username)->find();
                Db::table('friends')->insert(['id' => $id['id'], 'use_id' => $id['id']]);
            }
            else $msg = '注册失败';
        }
        return ['msg' => $msg];
    }
    public function getUserInfo(Request $request){
        $userid = $request->param('userid');
        if($userid){
            $user = Db::table('users')->where('userid', $userid)
                ->field('id,userid,name,password,avatar,tel_num,is_login')->find();
            if($user) return ['msg' => 'success', 'user' => $user];
            else return ['msg' => '获取失败'];
        }else return ['msg' => '请求参数userid不存在'];

    }
    public function updateUserName(Request $request) {
        $userid = $request->request('userid');
        $userName = $request->param('name');
        if ($userName) {
            Db::table('users')->where('userid', $userid)->update(['name' => $userName]);
            return ['msg' => 'success'];
        }
    }
    public function updateUserPwd(Request $request){
        $password = $request->param('password');
        if($password){
            $bool = Db::table('users')->where('userid', $request->param('userid'))
                ->update(['password' => $password]);
            if($bool) return ['msg' => 'success'];
            else return ['msg' => '密码更新失败'];
        }else return ['msg' => '密码参数不存在'];
    }
    public function updateUserPhone(Request $request){
        $tel_num = $request->param('tel_num');
        if($tel_num){
            $bool = Db::table('users')->where('userid', $request->param('userid'))
                ->update(['tel_num' => $tel_num]);
            if($bool) return ['msg' => 'success'];
            else return ['msg' => '更新失败'];
        }else return ['msg' => '请求参数不存在'];
    }
    public function updateUserPic(Request $request){
        $avatar = $request->param('avatar');
        $pic = $request->file('pic');
        $tip = null;
        if($pic) {
            $info = $pic->rule('uniqid')->move(url::$fileURL);
            if ($info) {
                $filename = $info->getFilename();
                //删除旧文件
                if(!empty($avatar)){
                    $fileurl = url::$fileURL . $avatar;
                    if(file_exists($fileurl)) unlink($fileurl);
                }
                $avatar = $filename;
                $tip = '文件存储成功';
            }else{
                $tip = '上传存储失败';
            }
        }else $tip =  '文件未上传或上传失败';
        if($tip === '文件存储成功'){
            $bool = Db::table('users')->where('userid', $request->param('userid'))
                ->update(['avatar' => $avatar]);
            if($bool) return ['msg' => 'success', 'tip' => 'ok', 'avatar' => $avatar];
            else $tip = '数据库更新失败';
        }
        return ['msg' => 'error', 'tip' => $tip];
    }
    // 获取某一页所有用户信息
    public function getAllUsers(Request $request){
        $currentPage = $request->param('currentPage');// 获取请求请求参数：当前选择页
        if(empty($currentPage)) return ['msg' => '页码参数为空'];
        $tel_num = $request->param('tel_num');
        $userid = $request->param('userid');
        $name = $request->param('name');
        if($tel_num) {
            $map['tel_num'] = $tel_num;
        } else if($userid) {
            $map['userid'] = $userid;
        } else if($name) {
            $map['name'] = $name;
        } else {
            $map = '1 = 1';
        }
        $userList = Db::table('users')->field('id, userid, name, password, tel_num, is_login')
            ->order('id', 'desc')->where($map)
            ->paginate(15, false, [
                'page' => $currentPage,
                'type'     => 'bootstrap',
                'var_page' => 'page'
            ]);
        return ['userList' => $userList];
    }
    // 获取某一用户的所有group
    public function getAllGroupFromUser(Request $request){
        $userid = $request->param('id');
        if($userid){
            $groupList = Db::table('user_group')->alias('ug')->join('users','users.id = ug.id')
                ->join('groups','groups.groupid = ug.groupid')->where('ug.id', $userid)
                ->field('groups.groupid, groups.group_name, ug.auth')->select();
            return ['groupList' => $groupList];
        }
    }
    // 获取某一用户的所有好友
    public function getAllFriendFromUser(Request $request){
        $userid = $request->param('id');
        if($userid){
            $user = Db::table('friends')->alias('f')->join('users u1','u1.id = friends.id')
                ->join('users u2','u2.id = friends.use_id')
                ->where('f.id', $userid)
                ->where('f.use_id','<>',$userid)
                ->field('u2.id, u2.userid, u2.name, u2.tel_num')->select();
            return ['userList' => $user];
        }
    }
    // 添加好友
    public function addFriend(Request $request){
        $id = $request->param('id');
        $add_id = $request->param('add_id');
        if($id && $add_id){
            $bool = Db::table('friends')->insert(['id' => $id, 'use_id' => $add_id]);
            if($bool) return ['msg' => 'success'];
            else return ['msg' => '插入数据库失败'];
        }else return ['msg' => '请求参数id或者add_id不存在'];
    }
    // 删除好友
    public function deleteFriend(Request $request){
        $id = $request->param('id');
        $delete_id = $request->param('delete_id');
        if($id && $delete_id){
            $map['id'] = $id;
            $map['use_id'] = $delete_id;
            $bool = Db::table('friends')->where($map)->delete();
            if($bool) return ['msg' => 'success'];
            else return ['msg' => '数据删除失败'];
        }else return ['msg' => '请求参数id或者delete_id不存在'];
    }
}