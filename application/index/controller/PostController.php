<?php
/**
 * Created by PhpStorm.
 * User: Leafqun
 * Date: 2017/10/14
 * Time: 14:31
 */

namespace app\index\controller;


use app\index\common\url;
use think\Controller;
use think\Db;
use think\Request;
use app\index\common\uuid;

class PostController extends Controller
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    }
    public function getPostList(Request $request){
        $currentPage = $request->param('currentPage');
        if(empty($currentPage)) return ['msg' => '当前页数的参数为空'];
        $userName = $request->param('userName');
        $userid = $request->param('userid');
        $tel_num = $request->param('tel_num');
        $startTime = $request->param('startTime');
        $endTime = $request->param('endTime');
        $a = $b = false;
        if ($userName) {
            $id = Db::table('users')->where('nickname', $userName)->field('id')->find();
            $map['posts.id'] = $id['id'];
        } else if ($tel_num) {
            $id = Db::table('users')->where('name', $tel_num)->field('id')->find();
            $map['posts.id'] = $id['id'];
        }else if ($userid) {
            $id = Db::table('users')->where('userid', $userid)->field('id')->find();
            $map['posts.id'] = $id['id'];
        } else {
            $a = true;
        }
        if ($startTime && $endTime) {
            $map['posts.create_time'] = ['between time', [$startTime, $endTime]];
        } else if ($startTime) {
            $map['posts.create_time'] = ['>= time', $startTime];
        } else if ($endTime) {
            $map['posts.create_time'] = ['<= time', $endTime];
        } else {
            $b = true;
        }
        if ($a && $b) $map = '1=1';
        $postList = Db::table('posts')->where($map)->join('users u', 'u.id = posts.id')
            ->field('users.nickname, posts.*, users.userid, users.avatar')->order('create_time', 'desc')
            ->paginate(5, false, [
                'page' => $currentPage,
                'type' => 'bootstrap',
                'var_page' => 'page'
            ])->each(function ($item, $key) {
                $picList = Db::table('post_pic')->where('post_pic.postid', $item['postid'])->select();
                $item['post_pic'] = $picList;
                $comment_num = Db::table('comments')->where('comments.postid', $item['postid'])->count();
                $item['comment_num'] = $comment_num;
                return $item;
            });

        return ['postList' => $postList];
    }
    public function getPostListFromUser(Request $request){
        $userid = $request->param('id');
        $currentPage = $request->param('currentPage');
        if($userid){
            $postList =  Db::table('posts')->where('id', $userid)->order('create_time','desc')->paginate(5, false, [
                'page' => $currentPage,
                'type'     => 'bootstrap',
                'var_page' => 'page'
            ])->each(function($item, $key){
                $picList = Db::table('post_pic')->where('post_pic.postid', $item['postid'])->select();
                $item['post_pic'] = $picList;
                $comment_num = Db::table('comments')->where('comments.postid', $item['postid'])->count();
                $item['comment_num'] = $comment_num;
                return $item;
            });
            return ['postList' => $postList];
        }else return['msg' => '用户id为空'];
    }
    public function getPostListFromFriends(Request $request){
        $userid = $request->param('id');
        $currentPage = $request->param('currentPage');
        if($userid){
           $postList = Db::table('friends')->alias('f')
               ->join('users u', 'f.use_id = u.id')
               ->join('posts p', 'p.id = u.id')
               ->field('u.id, u.userid, u.nickname, u.avatar, p.*')
               ->where('f.id', $userid)->order('create_time', 'desc')
               ->paginate(5, false, [
                   'page' => $currentPage,
                   'type'     => 'bootstrap',
                   'var_page' => 'page'
               ])->each(function($item, $key){
                   $picList = Db::table('post_pic')->where('post_pic.postid', $item['postid'])->select();
                   $item['post_pic'] = $picList;
                   return $item;
               });
           return ['postList' => $postList];
        }
    }
    public function getPostListFromGroup(Request $request) {
        $id = $request->param('id');
        $currentPage = $request->param('currentPage');
        if(empty($id)) return ['msg' => 'id参数为空'];
        if(empty($currentPage)) return ['msg' => 'currentPage参数为空'];
        $groupid = Db::table('user_group')->where('id', $id)->column('groupid');
        if(empty($groupid)) return ;
        $user_id = Db::table('user_group')->where('groupid', 'in', $groupid)->column('id');
        if(empty($user_id)) return ;
        $postList = Db::table('posts')->join('users u', 'u.id = posts.id')->order('create_time','desc')
            ->where('posts.id', 'in', $user_id)->field('u.id, u.userid, u.nickname, u.avatar, posts.*')
            ->paginate(5, false, [
            'page' => $currentPage,
            'type'     => 'bootstrap',
            'var_page' => 'page'
        ])->each(function($item, $key){
            $picList = Db::table('post_pic')->where('post_pic.postid', $item['postid'])->select();
            $item['post_pic'] = $picList;
            $comment_num = Db::table('comments')->where('comments.postid', $item['postid'])->count();
            $item['comment_num'] = $comment_num;
            return $item;
        });
        return ['msg' => 'success', 'postList' => $postList];
    }
    public function getPostInfo(Request $request){
        $postid = $request->param('postid');
        if(empty($postid)) return ['msg' => '请求参数为空'];
        $post = Db::table('posts')->join('users', 'users.id = posts.id')
            ->where('posts.postid', $postid)->field('posts.*, users.nickname, users.avatar')->find();
        if ($post) return ['post' => $post];
        else return ['msg' => '数据库获取数据失败'];

    }
    public function insertPost(Request $request){
        $post = $request->param();
        $picfiles = $request->file()['file'];
        if(is_array($picfiles)) $pics = $picfiles;
        else $pics = array($picfiles);
        if(!empty($post)){
            $create_time = date('Y-m-d H:i:s',time());
            $post['create_time'] = $create_time;
            $post['like_num'] = 0;
            $bool = Db::table('posts')->insert($post);
            if($bool) {
                foreach($pics as $pic){
                    if($pic){
                        $info = $pic->move(url::$fileURL . $post['id'], md5( date('YmdHis') . $pic->getInfo()['name']));
                        if($info){
                            $filename = $info->getFilename();
                            if($filename){
                                $map['id'] = $post['id'];
                                $map['create_time'] = $create_time;
                                $post_data = Db::table('posts')->where($map)->find();
                                if($post_data){
                                    Db::table('post_pic')->insert(['postid' => $post_data['postid'], 'pic_name' => $filename]);
                                }
                            }
                        }
                    }
                }
                return ['msg' => 'success'];
            }else return ['msg' => '数据库插入失败'];
        }else return ['msg' => '请求参数不存在'];
    }
    public function deletePost(Request $request){
        $params = $request->param()['postid'];
        if(is_array($params)) $postids = $params;
        else $postids = array($params);
        if(!empty($postids)){
            foreach ($postids as $postid){
                $post_pics = Db::table('post_pic')->where('postid', $postid)->field('pic_name')->select();
                $id = Db::table('posts')->where('postid', $postid)->column('id');
                foreach ($post_pics as $post_pic){
                    $fileurl = url::$fileURL . $id[0] . DS . $post_pic['pic_name'];
                    if (file_exists($fileurl)) unlink($fileurl);
                }
                Db::table('posts')->where('postid', $postid)->delete();
            }
            return ['msg' => 'success'];
        }
    }
    public function addLikes(Request $request) {
        $postid = $request->param('postid');
        if(empty($postid)) return ['msg' => '请求参数为空'];
        $is_like_add_1 = Db::table('posts')->where('postid', $postid)->setInc('like_num');
        if($is_like_add_1) return ['msg' => 'success'];
        else return ['msg' => '点赞失败'];
    }
    public function getMonthPostTotalNum(Request $request) {
        $total = Db::table('posts')->whereTime('create_time','month')->count();
        return ['total' => $total];
    }
    public function getTodayPostNum(Request $request) {
        $postNum = Db::table('posts')->whereTime('create_time', 'today')->count();
        return ['postNum' => $postNum];
    }
    public function getLastWeekPosts(Request $request) {
        $startDate = $request->param('startDate');
        $endDate = $request->request('endDate');
        $startdate = null;
        if (empty($startDate) || empty($endDate)) {
            $posts = Db::table('posts')->whereTime('create_time', '-15 days')->column('create_time');
            $startdate = time();
            $days = 30;
            $msg = '未选择日期';
        }
        else {
            $posts = Db::table('posts')->whereTime('create_time', 'between', [$startDate, $endDate])->column('create_time');
            $startdate = strtotime($endDate);
            $days = floor((strtotime($endDate)-strtotime($startDate))/86400);
            $msg = '已选择日期';
        }
        foreach ($posts as $post) {
            $date = substr($post, 0, 10);
            if (empty($data[$date])) $data[$date] = 0;
            $data[$date] = $data[$date] + 1;
        };
        for ($i = 0; $i < $days; $i++) {
            $enddate = date('Y-m-d',strtotime("-" . ($i + 1) ."day",$startdate));
            if (empty($data[$enddate])) $data[$enddate] = 0;
            $postList[$i]['name'] = $enddate;
            $postList[$i]['value'] = $data[$enddate];
        }
        return ['postNum' => array_reverse($postList), 'msg' => $msg];
    }
    public function getPostNumByMonth(Request $request) {
        $startMonth = $request->param('startMonth');
        $endMonth = $request->param('endMonth');
        if (empty($startMonth) || empty($endMonth)) {
            $posts = Db::table('posts')->whereTime('create_time', '-10 months')->column('create_time');
            $months = 10;
            $msg = '未选择日期';
            $startmonth = time();
        } else {
            $posts = Db::table('posts')->whereTime('create_time', 'between', [$startMonth, $endMonth])->column('create_time');
            $startmonth = strtotime($endMonth);
            $months = floor((strtotime($endMonth)-strtotime($startMonth))/(86400*30));
            $msg = '已选择日期';
        }
        foreach ($posts as $post) {
            $date = substr($post, 0, 7);
            if (empty($data[$date])) $data[$date] = 0;
            $data[$date] = $data[$date] + 1;
        };
        for ($i = 0; $i < $months; $i++) {
            $endmonth = date('Y-m',strtotime("-" . ($i + 1) ."months",$startmonth));
            if (empty($data[$endmonth])) $data[$endmonth] = 0;
            $postList[$i]['name'] = $endmonth;
            $postList[$i]['value'] = $data[$endmonth];
        }
        return ['postNum' => array_reverse($postList), 'msg' => $msg];
    }
    public function get7DaysWebsiteFlow(Request $request) {
        $startdate = time();
        $output = [];
        for ($i = 0; $i < 30; $i++) {
            $enddate = date('Ymd',strtotime("-" . $i ."day",$startdate));
            // $filePath = "C:\\Users\\Leafqun\\Desktop\\access_" . $enddate  . '.log';
            $filePath = "/usr/local/apache/logs/access_" . $enddate  . '.log';
            if (!file_exists($filePath)) {
                $output[$enddate] = 0;
                continue;
            }
            $myfile = fopen($filePath, "r") or die("Unable to open file!");
            $t = 0;
            while(!feof($myfile)) {
                $data = strstr(fgets($myfile), ' 200 ');
                if ($data) {
                    $lastIndex = strpos($data," \"");
                    $flow = trim(substr($data, 4, $lastIndex - 4));
                    if (is_numeric($flow)){
                        $t = $t + $flow;
                    }
                }
            }
            fclose($myfile);
            $output[$enddate] = $t;
        }
        return ['flow' => $output];
    }
}