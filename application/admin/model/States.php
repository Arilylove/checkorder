<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/6/1
 * Time: 15:44
 */
namespace app\admin\model;

use think\Db;

class States{
    protected $tableName = 'states';
    protected $stId;
    protected $state;

    public function add($states, $where){
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
    public function select($field, $where){
        $result = Db::table($this->tableName)->field($field)->where($where)->select();
        return $result;
    }
    public function count($where){
        $count = Db::table($this->tableName)->where($where)->count();
        return $count;
    }
    public function selectPage($field, $where, $num, $count){
        $state = Db::table($this->tableName)->field($field)->where($where)->paginate($num, $count, ['query' => request()->param()]);
        return $state;
    }

    /**
     * @return mixed
     */
    public function getStId()
    {
        return $this->stId;
    }

    /**
     * @param mixed $stId
     */
    public function setStId($stId)
    {
        $this->stId = $stId;
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