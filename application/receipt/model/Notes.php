<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/30
 * Time: 18:10
 */
namespace app\receipt\model;

use think\Db;

class Notes{
    protected $tableName = 'notes';

    public function add($data, $where){
        $result = Db::table($this->tableName)->where($where)->insert($data);
        return $result;
    }
    public function update($data, $where){
        $result = Db::table($this->tableName)->where($where)->update($data);
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
    public function selectPage($field, $where){
        $num = 10;
        $count = $this->count($where);
        $admin = Db::table($this->tableName)->field($field)->where($where)->paginate($num, $count);
        return $admin;
    }
    public function count($where){
        $count = Db::table($this->tableName)->where($where)->count();
        return $count;
    }
    public function insertAll($data, $where){
        $result = Db::table($this->tableName)->where($where)->insertAll($data);
        return $result;
    }



}