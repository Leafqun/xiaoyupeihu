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
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;
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
        $type = $request->param('type');
        if(empty($devid)) return ['msg' => 'devid参数为空'];

        // 判断设备是否已经注册
        $dev = Db::table('devs')->where('devid', $devid)->find();
        if($dev) return ['msg' => 'dev已注册'];

        $create_time = date('Y-m-d H:i:s',time());

        // dev添加
        $isCreateDev = Db::table('devs')->insert(['devid' => $devid, 'create_time' => $create_time, 'type' => $type, 'max' => 10]);
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
    public function bindDev(Request $request) {
        $id = $request->param('id');
        $devid = $request->param('devid');
        if (empty($id) || empty($devid)) return ['msg' => '请求参数为空'];
        // bind
        try {
            $dev = Db::table('devs')->where('devid', $devid)->find();
            if ($dev['id']) return ['msg' => '已绑定'];
            //绑定设备
            $isBind = Db::table('devs')->where('devid', $devid)->update(['id' => $id]);
            if (empty($isBind)) return ['msg' => '设备绑定用户失败'];
            // 创建群组
            $create_time = date('Y-m-d H:i:s', time());
            $groupid = Db::table('groups')->insertGetId(['did' => $dev['did'], 'create_time' => $create_time, 'group_name' => $devid, 'total' => 1]);
            if (empty($groupid)) return ['msg' => '群组创建失败'];
            // 群组与设备绑定
            $dev_is_update = Db::table('devs')->where('devid', $devid)->update(['groupid' => $groupid]);
            if (empty($dev_is_update)) return ['msg' => '群组与设备绑定失败'];
            // 用户加入群组，并设为群主
            $is_user_add_group = Db::table('user_group')->insert(['id' => $id, 'groupid' => $groupid, 'auth' => 2]);
            if ($is_user_add_group) return ['msg' => 'success'];
            else return ['msg' => '用户加入群组失败'];
        } catch (PDOException $e) {

        } catch (Exception $e) {

        }
    }
    public function getDevList(Request $request) {
        $currentPage = $request->param('currentPage');
        if(empty($currentPage)) return ['msg' => '请求页数未知'];
        // 查询条件devid
        $devid = $request->param('devid');
        $type = $request->param('type');
        $isBind = $request->param('isBind');
        $orderByMax = $request->param('orderByMax');
        $orderByCreateTime = $request->param('orderByCreateTime');
        $startTime = $request->param('startTime');
        $endTime = $request->param('endTime');
        $isFilter = true;
        if (empty($devid) && empty($type) && empty($isBind) && empty($orderByMaxe) && empty($startTime) && empty($endTime)) $isFilter = false;
        if($devid) {
            $map['devid'] = $devid;
        }
        if ($type) $map['type'] = $type;
        if ($orderByMax === 'descend') $order = 'max desc';
        else if ($orderByMax === 'ascend') $order = 'max asc';
        else if ($orderByCreateTime === 'ascend') $order = 'create_time asc';
        else $order = 'create_time desc';
        if ($isBind === '1') $map['id'] = ['exp', 'is not null'];
        if ($isBind === '2') $map['id'] = null;
        if ($startTime && $endTime) {
            $map['create_time'] = ['between time', [$startTime, $endTime]];
        }
        if (!$isFilter) {
            $map = '1 = 1';
        }
        try {
            $devList = Db::table('devs')->where($map)->order($order)
                ->paginate(15, false, [
                    'page' => $currentPage,
                    'type' => 'bootstrap',
                    'var_page' => 'page'
                ]);
            return ['devList' => $devList];
        } catch (DbException $e) {
        }
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
        $user = $group = [];
        $dev = Db::table('devs')->where($map)->find();
        if ($dev['id']) $user = Db::table('users')->where('id', $dev['id'])->field('userid, name')->find();
        if ($dev['groupid']) $group = Db::table('groups')->where('groupid', $dev['groupid'])->field('groupid, group_name')->find();
        $dev = $dev + $user + $group;
        return ['dev' => $dev];
    }
    public function isDevBinded(Request $request) {
        $devid = $request->param('devid');
        if (empty($devid)) return ['msg' => '请求参数为空'];
        $dev = Db::table('devs')->where('devid', $devid)->find();
        if (empty($dev['id'])) return ['msg' => '未绑定'];
        else return ['msg' => '已绑定'];
    }
    public function getDevTotalNum(Request $request) {
        $total = Db::table('devs')->count();
        return ['total' => $total];
    }
}