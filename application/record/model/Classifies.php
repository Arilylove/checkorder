<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/14
 * Time: 15:22
 */
namespace app\record\model;

use think\Db;

class Classifies
{
    protected $tableName = 'problem_classify';
    protected $pcId;
    protected $classify;

    public function add($classify, $where = '')
    {
        $result = Db::table($this->tableName)->where($where)->insert($classify);
        return $result;
    }

    public function update($classify, $where)
    {
        $result = Db::table($this->tableName)->where($where)->update($classify);
        return $result;
    }

    public function del($where)
    {
        $result = Db::table($this->tableName)->where($where)->delete();
        return $result;
    }

    public function findById($where)
    {
        $result = Db::table($this->tableName)->where($where)->find();
        return $result;
    }

    public function insertAll($data){
        $result = Db::table($this->tableName)->insertAll($data);
        return $result;
    }
    /**
     * or查询
     * @param $where
     * @param $or
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function find($where, $or = '')
    {
        $result = Db::table($this->tableName)->where($where)->whereOr($or)->find();
        return $result;
    }

    public function select($field, $where = '')
    {
        $result = Db::table($this->tableName)->field($field)->where($where)->select();
        return $result;
    }

    public function count($where)
    {
        $count = Db::table($this->tableName)->where($where)->count();
        return $count;
    }

    public function selectPage($field, $where, $num, $count)
    {
        $state = Db::table($this->tableName)->field($field)->where($where)->paginate($num, $count, ['query' => request()->param()]);
        return $state;
    }

    /**
     * 模糊查询
     */
    public function searchLike($search, $num)
    {
        $count = Db::table($this->tableName)->where("pcId", 'like', "%$search%")->whereOr('classify', 'like', "%$search%")->count();
        $classify = Db::table($this->tableName)->where("pcId", 'like', "%$search%")->whereOr('classify', 'like', "%$search%")
            ->paginate($num, $count, ['query' => request()->param()]);
        return $classify;
    }

    /**
     * @return mixed
     */
    public function getPcId()
    {
        return $this->pcId;
    }

    /**
     * @param mixed $pcId
     */
    public function setPcId($pcId)
    {
        $this->pcId = $pcId;
    }

    /**
     * @return mixed
     */
    public function getClassify()
    {
        return $this->classify;
    }

    /**
     * @param mixed $classify
     */
    public function setClassify($classify)
    {
        $this->classify = $classify;
    }


}