<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/6/1
 * Time: 15:44
 */
namespace app\admin\model;

use think\Db;

class Codes{
    protected $tableName = 'code';
    protected $coId;
    protected $cliId;
    protected $mac;
    protected $creator;
    protected $createTime;
    protected $expireTime;
    protected $code;
    protected $other;

    public function add($code, $where){
        $result = Db::table($this->tableName)->where($where)->insert($code);
        return $result;
    }
    public function update($code, $where){
        $result = Db::table($this->tableName)->where($where)->update($code);
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
    public function updateExecute($code, $where){
        Db::table($this->tableName)->where($where)->update($code);
    }
    public function count($where){
        $count = Db::table($this->tableName)->where($where)->count();
        return $count;
    }
    public function selectPage($tableName2, $param, $field, $where, $num, $count){
        $code = Db::table($this->tableName)->join($tableName2, $this->tableName.'.'.$param.'='.$tableName2.'.'.$param)
            ->field($field)->where($where)->paginate($num, $count, ['query' => request()->param()]);
        return $code;
    }
    /**
     * @param $field
     * @param $where
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function select($field, $where){
        $result = Db::table($this->tableName)->field($field)->where($where)->select();
        return $result;
    }

    /**
     * @param $join
     * @param $where
     * @param $field
     * @return false|mixed|\PDOStatement|string|\think\Collection
     * @Description 联合查询
     */
    public function joinSelect($tableName2, $param, $where, $field){
        $code = Db::table($this->tableName)->join($tableName2, $this->tableName.'.'.$param.'='.$tableName2.'.'.$param)->field($field)->where($where)->select();
        return $code;
    }

    /**
     * @return mixed
     */
    public function getCoId()
    {
        return $this->coId;
    }

    /**
     * @param mixed $coId
     */
    public function setCoId($coId)
    {
        $this->coId = $coId;
    }

    /**
     * @return mixed
     */
    public function getCliId()
    {
        return $this->cliId;
    }

    /**
     * @param mixed $cliId
     */
    public function setCliId($cliId)
    {
        $this->cliId = $cliId;
    }

    /**
     * @return mixed
     */
    public function getMac()
    {
        return $this->mac;
    }

    /**
     * @param mixed $mac
     */
    public function setMac($mac)
    {
        $this->mac = $mac;
    }

    /**
     * @return mixed
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param mixed $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * @return mixed
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * @param mixed $createTime
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;
    }

    /**
     * @return mixed
     */
    public function getExpireTime()
    {
        return $this->expireTime;
    }

    /**
     * @param mixed $expireTime
     */
    public function setExpireTime($expireTime)
    {
        $this->expireTime = $expireTime;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getOther()
    {
        return $this->other;
    }

    /**
     * @param mixed $other
     */
    public function setOther($other)
    {
        $this->other = $other;
    }
    
    
}