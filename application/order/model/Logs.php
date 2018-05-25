<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/24
 * Time: 17:19
 */
namespace app\order\model;

use think\Db;
class Logs{
    protected $tableName = "user_logs";
    protected $lid;
    protected $uid;
    protected $behavior;
    protected $create_time;

    public function add($admin, $where){
        $result = Db::table($this->tableName)->where($where)->insert($admin);
        return $result;
    }
    public function update($admin, $where){
        $result = Db::table($this->tableName)->where($where)->update($admin);
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
    public function select($field = '*', $where){
        $result = Db::table($this->tableName)->field($field)->where($where)->select();
        return $result;
    }
    public function selectPage($field, $where, $order){
        $num = 10;
        $count = $this->count($where);
        $admin = Db::table($this->tableName)->field($field)->where($where)->order($order)->paginate($num, $count, ['query' => request()->param()]);
        return $admin;
    }
    public function count($where){
        $count = Db::table($this->tableName)->where($where)->count();
        return $count;
    }




}