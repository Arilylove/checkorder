<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 13:35
 */
namespace app\order\model;

use think\Db;
class Meters{
    protected $tableName = 'meter';

    protected $mid;
    protected $meterNum;
    protected $oid;

    public function add($meters, $where){
        $result = Db::table($this->tableName)->where($where)->insert($meters);
        return $result;
    }
    public function update($meters, $where){
        $result = Db::table($this->tableName)->where($where)->update($meters);
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
        $result = Db::table($this->tableName)->field($field)->where($where)->order('meterNum asc')->select();
        return $result;
    }
    public function selectPage($field, $where, $count){
        $num = 10;
        $admin = Db::table($this->tableName)->field($field)->where($where)->paginate($num, $count);
        return $admin;
    }
    public function count($where){
        $count = Db::table($this->tableName)->where($where)->count();
        return $count;
    }
    public function findExist($where, $oid){
        $result = Db::table($this->tableName)->where($where)->where('oid', '<>', $oid)->find();
        return $result;
    }
    /**
     * 分页联合查询
     * @param $field
     * @param $joinTable
     * @param $param
     * @param $where
     * @param $count
     * @return \think\paginator\Collection
     */
    public function join($joinTable, $param, $where){
        $num = 10;
        $count = Db::table($this->tableName)->join($joinTable, $this->tableName.'.'.$param.'='.$joinTable.'.'.$param)
            ->where($where)->count();
        $orders = Db::table($this->tableName)->join($joinTable, $this->tableName.'.'.$param.'='.$joinTable.'.'.$param)
            ->where($where)->paginate($num, $count);
        return $orders;
    }

    /**
     * 根据提供的mid字符串查找对应的表号数组-》二维数组
     * @param $mid
     * @return mixed
     */
    public function getMeterList($mid){
        $midArr = explode(",", $mid);
        for ($i=0;$i<count($midArr);$i++){
            $where = array('mid'=>$midArr[$i]);
            $meter[$i] = $this->findById($where);
        }
        return $meter;
    }



    /**
     * @return mixed
     */
    public function getMid()
    {
        return $this->mid;
    }

    /**
     * @param mixed $mid
     */
    public function setMid($mid)
    {
        $this->mid = $mid;
    }

    /**
     * @return mixed
     */
    public function getMeterNum()
    {
        return $this->meterNum;
    }

    /**
     * @param mixed $meterNum
     */
    public function setMeterNum($meterNum)
    {
        $this->meterNum = $meterNum;
    }

    /**
     * @return mixed
     */
    public function getOid()
    {
        return $this->oid;
    }

    /**
     * @param mixed $oid
     */
    public function setOid($oid)
    {
        $this->oid = $oid;
    }



}