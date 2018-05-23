<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/11/15
 * Time: 16:57
 */
namespace app\auth\model;

use think\Db;

class Roles
{
    private $tableName = 'roles';
    private $role_id;
    private $remark;
    private $role_name;
    private $create_time;
    /**
     * @var 方法id组合
     */
    private $wid;
    private $status;

    public function add($roles, $where){
        $result = Db::table($this->tableName)->where($where)->insert($roles);
        return $result;
    }
    public function update($roles, $where){
        $result = Db::table($this->tableName)->where($where)->update($roles);
        return $result;
    }
    public function del($where){
        $result = Db::table($this->tableName)->where($where)->delete();
        return $result;
    }
    public function findById($where){
        $result = Db::table($this->tableName)->where($where)->find();
        return $result;
    }
    public function select($field, $where){
        $result = Db::table($this->tableName)->field($field)->where($where)->select();
        return $result;
    }
    public function search($where){
        $search = Db::table($this->tableName)->where($where)->select();
        return $search;
    }

    public function count($where){
        $count = Db::table($this->tableName)->where($where)->count();
        return $count;
    }
    public function page($field, $where, $order){
        $num = 10;
        $count = $this->count($where);
        $data = Db::table($this->tableName)->field($field)->where($where)->order($order)->paginate($num, $count, ['query' => request()->param()]);
        return $data;
    }


}