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
                    return array('msg' => 'success');
                } else {
                    return array('msg' => '密码错误');
                }
            } else {
                return array('msg' => '用户名不存在');
            }
        }
    }
}