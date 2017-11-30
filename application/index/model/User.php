<?php
/**
 * Created by PhpStorm.
 * User: Leafqun
 * Date: 2017/10/9
 * Time: 23:16
 */

namespace app\index\model;


use think\Model;

class User extends Model
{
    protected $table = 'users';

    protected $pk = 'id';

    public function groups()
    {
        return $this->belongsToMany('Group', '', 'id', 'id');
    }
}