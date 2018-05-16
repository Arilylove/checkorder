<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 13:27
 */
namespace app\order\model;

use think\Db;
/**
 * 制造商
 * Class Manufacturer
 * @package app\order\model
 */
class Manufacturer{
    protected $tableName = 'manu';

    protected $mfId;
    protected $manufacturer;

    public function add($manu, $where){
        $result = Db::table($this->tableName)->where($where)->insert($manu);
        return $result;
    }
    public function update($manu, $where){
        $result = Db::table($this->tableName)->where($where)->update($manu);
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

    /**
     * 模糊查询
     * @return \think\paginator\Collection
     */
    public function searchLike($search){
        $count = Db::table($this->tableName)->where('mfId', 'like', "%$search%")
            ->whereOr('manufacturer', 'like', "%$search%")->whereOr('state', 'like', "%$search%")->count();
        $manus = Db::table($this->tableName)->where('mfId', 'like', "%$search%")
            ->whereOr('manufacturer', 'like', "%$search%")->whereOr('state', 'like', "%$search%")->paginate(10, $count);
        return $manus;
    }

    /**
     * @return mixed
     */
    public function getMfId()
    {
        return $this->mfId;
    }

    /**
     * @param mixed $mfId
     */
    public function setMfId($mfId)
    {
        $this->mfId = $mfId;
    }

    /**
     * @return mixed
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * @param mixed $manufacturer
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }



}