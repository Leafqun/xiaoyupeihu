<?php
/**
 * Created by PhpStorm.
 * User: Leafqun
 * Date: 2017/10/9
 * Time: 23:19
 */

namespace app\index\model;


use think\Model;

class Group extends Model
{
    protected $table = 'groups';

    protected $pk = 'groupid';

    public function users(){
        return $this->belongsToMany('User', 'user_group', 'groupid', 'groupid');
    }
}