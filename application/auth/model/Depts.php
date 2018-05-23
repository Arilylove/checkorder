<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/21
 * Time: 11:29
 */
namespace app\auth\model;

use think\Db;
class Depts{
    private $tableName = 'depts';
    private $dept_name;
    private $description;
    private $create_time;

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
    /**
     * 查询部门下是否存在用户
     */
    public function uidExist($tableAdmin){
        $find = Db::table($this->tableName)->join($tableAdmin, $tableAdmin.'.de_id='.$this->tableName.'.de_id')->find();
        return $find;
    }

}