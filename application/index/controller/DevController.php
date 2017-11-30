<?php
/**
 * Created by PhpStorm.
 * User: Leafqun
 * Date: 2017/11/28
 * Time: 13:24
 */

namespace app\index\controller;


use think\Controller;
use think\Db;
use think\Request;

class DevController extends Controller
{
    public function _initialize()
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    }
    public function addDev(Request $request) {
        $devid = $request->param('devid');
        if(empty($devid)) return ['msg' => 'devid参数为空'];

        // 判断设备是否已经注册
        $dev = Db::table('devs')->where('devid', $devid)->find();
        if($dev) return ['msg' => 'dev已注册'];

        // 判断用户是否已经注册
        $devUser = Db::table('users')->where('userid', $devid)->find();
        if($devUser) return ['msg' => 'dev用户已注册'];

        // 用户添加
        $id = Db::table('users')->insertGetId(['userid' => $devid, 'name' => $devid, 'password' => 123456]);
        if(empty($id)) return ['msg' => 'dev用户注册失败'];

        // 群组注册
        $create_time = date('Y-m-d H:i:s',time());
        $groupid = Db::table('groups')->insertGetId(['group_name' => $devid, 'create_time' => $create_time, 'total' => 1]);
        if(empty($groupid)) return ['dev群组创建失败'];

        // dev用户加入dev群组，并设为群主
        $isAddGroup = Db::table('user_group')->insert(['groupid' => $groupid, 'id' => $id, 'auth' => 2]);
        if(empty($isAddGroup)) return ['msg' => '设置dev用户加入群组失败'];

        // dev添加
        $isCreateDev = Db::table('devs')->insert(['devid' => $devid, 'groupid' => $groupid, 'create_time' => $create_time]);
        if($isCreateDev) return ['msg' => 'success'];
        else return ['msg' => 'dev添加失败'];
    }
    public function updateDevInfo(Request $request) {
        $info = $request->param();
        if(empty($info)) return ['msg' => 'dev信息为空'];
        $did = $info['did'];

        if(!empty($info['devid'])) {
            $devid = $info['devid'];
            // 获取原devid
            $original_dev= Db::table('devs')->where('did', $did)->field('devid')->find();
            $original_dev_id = $original_dev['devid'];
            if($devid === $original_dev_id) return ['msg' => '更改后的devid与更改前相同'];
            // 更改用户userid
            $isUpdateUser = Db::table('users')->where('userid', $original_dev_id)->update(['userid' => $devid]);
            if(empty($isUpdateUser)) return ['msg' => '更新dev用户失败'];
            //更改dev
            $isUpdateDev = Db::table('devs')->where('did', $did)->update(['devid' => $devid]);
            if($isUpdateDev) return ['msg' => 'success'];
            else return ['msg' => 'devid更改失败'];
        }

        if(!empty($info['max'])) {
            $max = $info['max'];
            $isUpdateDev = Db::table('devs')->where('did', $did)->update(['max' => $max]);
            if($isUpdateDev) return ['msg' => 'success'];
            else return ['msg' => 'max更改失败'];
        }
    }
    public function deleteDev(Request $request) {
        $did = $request->param()['did'];
        if(!is_array($did)) $did = array($did);
        if(empty($did)) return ['msg' => '请求参数为空'];
        // 删除dev
        foreach ($did as $id) {
            $dev = Db::table('devs')->where('did', $id)->field('devid, groupid')->find();
            $groupid = $dev['groupid'];
            $devid = $dev['devid'];
            // 删除dev对应的group,因为dev的外键为groupid，所以删除group表数据，即可删除对应dev表外键依赖的数据
            if($groupid) {
               $is_delete_group = Db::table('groups')->delete($groupid);
               if(empty($is_delete_group)) return ['msg' => '删除group和dev失败'];
            }
            // 删除dev对应的user
            if($devid) {
                $is_delete_user = Db::table('users')->where('userid', $devid)->delete();
                if(empty($is_delete_user)) return ['msg' => '删除user失败'];
            }
        }
        return ['msg' => 'success'];
    }
    public function getDevList(Request $request) {
        $currentPage = $request->param('currentPage');
        if(empty($currentPage)) return ['msg' => '请求页数未知'];
        // 查询条件devid
        $devid = $request->param('devid');
        if($devid) {
            $map['devid'] = $devid;
        }else {
            $map = '1 = 1';
        }
        $devList = Db::table('devs')->where($map)->order('did', 'desc')
            ->paginate(10, false, [
                'page' => $currentPage,
                'type'     => 'bootstrap',
                'var_page' => 'page'
            ]);
        return ['devList' => $devList];
    }
    public function getDevInfo(Request $request) {
        $devid = $request->param('devid');
        $did = $request->param('did');
        if(empty($did) && empty($devid)) return ['msg' => '请求参数为空'];
        if($devid) {
            $map['devid'] = $devid;
        }
        if($did) {
            $map['did'] = $did;
        }
        $dev = Db::table('devs')->join('groups g', 'g.groupid = devs.groupid')->where($map)
            ->field('devs.*, g.groupid, g.group_name')->find();
        return ['dev' => $dev];
    }
}