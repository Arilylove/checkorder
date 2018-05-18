<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/6/1
 * Time: 15:43
 */
namespace app\order\model;

use think\Db;

class Clients{
    protected $tableName = 'client';
    protected $cid;
    protected $sid;
    protected $client;

    public function add($client, $where = ''){
        $result = Db::table($this->tableName)->where($where)->insert($client);
        return $result;
    }
    public function update($client, $where){
        $result = Db::table($this->tableName)->where($where)->update($client);
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
    public function select($field, $where = ''){
        $result = Db::table($this->tableName)->field($field)->where($where)->select();
        return $result;
    }
    public function selectDistinct($field, $where){
        $result = Db::table($this->tableName)->field($field)->where($where)->distinct(true)->select();
        return $result;
    }
    public function count($where){
        $count = Db::table($this->tableName)->where($where)->count();
        return $count;
    }
    public function selectPage($tableName2, $param, $field, $where, $num, $count){
        $client = Db::table($this->tableName)->join($tableName2, $this->tableName.'.'.$param.'='.$tableName2.'.'.$param)
            ->field($field)->where($where)->paginate($num, $count, ['query' => request()->param()]);
        return $client;
    }
    /**
     * @param $join
     * @param $where
     * @param $field
     * @return false|mixed|\PDOStatement|string|\think\Collection
     * @Description 联合查询
     */
    public function joinSelect($joinTable, $param, $where, $field){
        $client = Db::table($this->tableName)->join($joinTable, $this->tableName.'.'.$param.'='.$joinTable.'.'.$param)->field($field)->where($where)->select();
        return $client;
    }

    /**
     * 模糊查询
     * @param $search
     * @param $tableName2
     * @param $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function searchLike($search, $tableName2, $param){
        $count = Db::table($this->tableName)->join($tableName2, $this->tableName.'.'.$param.'='.$tableName2.'.'.$param)
            ->where('cid',  'like', "%$search%")->whereOr('client', 'like', "%$search%")->whereOr('state', 'like', "%$search%")->count();
        $client = Db::table($this->tableName)->join($tableName2, $this->tableName.'.'.$param.'='.$tableName2.'.'.$param)
            ->where('cid',  'like', "%$search%")->whereOr('client', 'like', "%$search%")->whereOr('state', 'like', "%$search%")->paginate(10, $count, ['query' => request()->param()]);
        return $client;
    }

    /**
     * 以客户名模糊查询
     * @param $search
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function search($search){
        $client = Db::table($this->tableName)->where('client',  'like', "%$search%")->select();
        return $client;
    }
    /**
     * @return mixed
     */
    public function getCid()
    {
        return $this->cid;
    }

    /**
     * @param mixed $cid
     */
    public function setCid($cid)
    {
        $this->cid = $cid;
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
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }


}