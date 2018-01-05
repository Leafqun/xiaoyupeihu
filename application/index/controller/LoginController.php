<?php
/**
 * Created by PhpStorm.
 * User: Leafqun
 * Date: 2017/10/5
 * Time: 16:54
 */

namespace app\index\controller;


use think\Controller;
use think\Db;
use think\Request;

class LoginController extends Controller
{
    public function _initialize()
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    }
    public function login(Request $request)
    {
        if ($request->isGet() || $request->isPost()) {
            $userName = $request->param('userName');
            $userPwd = $request->param('userPwd');
            $user = Db::table('manager')->where('manager_name', $userName)->find();
            if ($user) {
                if ($userPwd === $user['password']) {
                    return array('msg' => 'success', 'loginId' => $user['managerid']);
                } else {
                    return array('msg' => '密码错误');
                }
            } else {
                return array('msg' => '用户名不存在');
            }
        }
    }
    public function getLoginInfo(Request $request){
        $loginId = $request->param('loginId');
        if (empty($loginId)) return ['msg' => 'error'];
        $login = Db::table('manager')->where('managerid', $loginId)->find();
        return ['login' => $login];
    }
    public function updateName(Request $request) {
        $loginId = $request->param('loginId');
        $name = $request->param('name');
        if (empty($name) || empty($loginId)) return ['msg' => 'error'];
        $is_update_name = Db::table('manager')->where('managerid', $loginId)->update(['manager_name' => $name]);
        if ($is_update_name) return ['msg' => 'success'];
        else return ['msg' => 'error2'];
    }
    public function updatePwd(Request $request) {
        $loginId= $request->param('loginId');
        $oldPwd = $request->param('oldPwd');
        $newPwd = $request->param('newPwd');
        if(empty($loginId) || empty($oldPwd) || empty($newPwd)) return ['msg' => '请求参数不全'];
        $pwd = Db::table('manager')->where('managerid', $loginId)->column('password');
        if($pwd[0] != $oldPwd) return ['msg' => '原密码错误'];
        $is_update = Db::table('manager')->where('managerid', $loginId)->update(['password' => $newPwd]);
        if($is_update) return ['msg' => 'success'];
        else return ['msg' => 'error'];
    }
}