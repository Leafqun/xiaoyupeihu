<?php
/**
 * Created by PhpStorm.
 * User: Leafqun
 * Date: 2017/10/8
 * Time: 18:23
 */

namespace app\index\controller;


use app\index\model\Group;
use think\Controller;
use think\Db;
use think\Request;

class GroupController extends Controller
{
    public function _initialize()
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    }
    public function registerGroup(Request $request){
        $userid = $request->param('id');
        $group_name = $request->param('group_name');
        if($userid){
            if($group_name){
                $create_time = date('Y-m-d H:i:s',time());
                $bool = Db::table('groups')->insert(['group_name' => $group_name, 'create_time' => $create_time]);
                if($bool){
                    $auth = 2;
                    $group = Db::table('groups')->where('group_name', $group_name)->field('groupid')->find();
                    if($group['groupid']){
                        $bol = Db::table('user_group')->insert(['id' => $userid, 'groupid' => $group['groupid'], 'auth' => $auth]);
                        if($bol) return ['msg' => 'success'];
                        else return ['msg' => '设置用户为群主失败'];
                    }
                }
                return ['msg' => '群组插入失败'];
            }else return ['msg' => '请求参数group_name不存在'];
        }else return ['msg' => '请求参数id不存在'];
    }
    public function joinGroup(Request $request){
        $devid = $request->param('devid');
        $id = $request->param('id');
        if(empty($devid) && empty($id)) return ['msg' => '请求参数不全'];
        // 判断所加群组是否存在
        $group = Db::table('devs')->where('devid', $devid)->field('groupid')->find();
        if(empty($group)) return ['msg' => 'group不存在'];

        $groupid = $group['groupid'];
        $is_join = Db::table('user_group')->insert(['id' => $id, 'groupid' => $groupid]);
        if($is_join) {
            $is_add_1 = Db::table('groups')->where('groupid', $groupid)->setInc('total');
            if($is_add_1) return ['msg' => 'success'];
            else return ['msg' => '群组人数未更新'];
        }
        else return ['msg' => '加入失败'];
    }
    public function getGroupInfo(Request $request){
        $groupId = $request->param('groupid');
        if(!empty($groupId)){
            // 获取数据库中的group信息
            $group = Db::table('groups')->where('groupid', $groupId)->find();
            return ['group' => $group];
        }
        return 'error';
    }
    // 获取某一分页所有group信息
    public function getGroupList(Request $request){
        $currentPage = $request->param('currentPage');
        if(empty($currentPage)) return ['msg' => '页码参数为空'];
        $devid = $request->param('devid');
        $group_name = $request->param('group_name');
        $groupid = $request->param('groupid');
        if($devid) {
            $map['devid'] = $devid;
        } else if($groupid) {
            $map['groupid'] = $groupid;
        } else if ($group_name) {
            $map['group_name'] = $group_name;
        } else {
            $map = '1 = 1';
        }
        $groupList = Db::table('groups')->alias('g')->join('devs d', 'g.groupid = d.groupid')
            ->where($map)->field('d.devid, g.*')->order('groupid', 'desc')
            ->paginate(15, false, [
                'page' => $currentPage,
                'type'     => 'bootstrap',
                'var_page' => 'page'
            ]);
        return ['groupList' => $groupList];
    }
    // 获取某一群组的所有用户
    public function getAllUserFromGroup(Request $request){
        $groupid = $request->param('groupid');
        if($groupid){
            $userList = Db::table('user_group')->alias('ug')->join('users','users.id = ug.id')
                ->join('groups','groups.groupid = ug.groupid')->where('ug.groupid', $groupid)
                ->field('users.id, users.userid, users.name, ug.auth')->select();
            return ['userList' => $userList];
        }
    }
}