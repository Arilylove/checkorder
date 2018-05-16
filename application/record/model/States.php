<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/6/1
 * Time: 15:44
 */
namespace app\record\model;

use think\Db;

class States{
    protected $tableName = 'state';
    protected $sid;
    protected $state;

    public function add($states, $where = ''){
        $result = Db::table($this->tableName)->where($where)->insert($states);
        return $result;
    }
    public function update($states, $where){
        $result = Db::table($this->tableName)->where($where)->update($states);
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

    /**
     * or查询
     * @param $where
     * @param $or
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function find($where, $or = ''){
        $result = Db::table($this->tableName)->where($where)->whereOr($or)->find();
        return $result;
    }
    public function select($field, $where = ''){
        $result = Db::table($this->tableName)->field($field)->where($where)->select();
        return $result;
    }
    public function count($where){
        $count = Db::table($this->tableName)->where($where)->count();
        return $count;
    }
    public function selectPage($field, $where, $num, $count){
        $state = Db::table($this->tableName)->field($field)->where($where)->paginate($num, $count);
        return $state;
    }
    public function insertAll($data){
        $state = Db::table($this->tableName)->insertAll($data);
        return $state;
    }

    /**
     * 模糊查询
     */
    public function searchLike($search, $num){
        $count = Db::table($this->tableName)->where("sid", 'like', "%$search%")->whereOr('state', 'like', "%$search%")->count();
        $state = Db::table($this->tableName)->where("sid", 'like', "%$search%")->whereOr('state', 'like', "%$search%")->paginate($num, $count);
        return $state;
    }
    /**
     * @return mixed
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * @param mixed $sid
     */
    public function setSid($sid)
    {
        $this->sid = $sid;
    }



    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }


}