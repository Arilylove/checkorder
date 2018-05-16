<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 12:26
 */
namespace app\order\model;

use think\Db;
/**
 * 基表型号
 * Class MeterTypes
 * @package app\order\model
 */
class MeterTypes{
    protected $tableName = 'meter_type';

    protected $meterId;
    protected $meterType;

    public function add($meterTypes, $where){
        $result = Db::table($this->tableName)->where($where)->insert($meterTypes);
        return $result;
    }
    public function update($meterTypes, $where){
        $result = Db::table($this->tableName)->where($where)->update($meterTypes);
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
    public function selectPage($field, $where, $count){
        $num = 10;
        $admin = Db::table($this->tableName)->field($field)->where($where)->paginate($num, $count);
        return $admin;
    }
    public function count($where){
        $count = Db::table($this->tableName)->where($where)->count();
        return $count;
    }
    public function insertAll($data){
        $insertAll = Db::table($this->tableName)->insertAll($data);
        return $insertAll;
    }
    /**
     * 模糊查询
     * @return \think\paginator\Collection
     */
    public function searchLike($search){
        $count = Db::table($this->tableName)->where('meterId', 'like', "%$search%")->whereOr('meterType', 'like', "%$search%")->count();
        $meterTypes = Db::table($this->tableName)->where('meterId', 'like', "%$search%")->whereOr('meterType', 'like', "%$search%")->paginate(10, $count);
        return $meterTypes;
    }

    /**
     * @return mixed
     */
    public function getMeterId()
    {
        return $this->meterId;
    }

    /**
     * @param mixed $meterId
     */
    public function setMeterId($meterId)
    {
        $this->meterId = $meterId;
    }

    /**
     * @return mixed
     */
    public function getMeterType()
    {
        return $this->meterType;
    }

    /**
     * @param mixed $meterType
     */
    public function setMeterType($meterType)
    {
        $this->meterType = $meterType;
    }




}