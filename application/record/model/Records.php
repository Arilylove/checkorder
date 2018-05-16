<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/7
 * Time: 17:30
 */
namespace app\record\model;

use think\Db;

class Records{
    protected $tableName = 'record';
    /**
     * @var 记录编号
     */
    protected $rid;
    /**
     * @var 问题来源
     */
    protected $source;
    /**
     * @var 问题描述
     */
    protected $description;
    /**
     * @var 解决时间
     */
    protected $solveTime;
    /**
     * @var 是否解决
     */
    protected $solved;

    public function add($record, $where = array()){
        $result = Db::table($this->tableName)->where($where)->insert($record);
        return $result;
    }
    public function update($record, $where){
        $result = Db::table($this->tableName)->where($where)->update($record);
        return $result;
    }
    public function del($where){
        $result = Db::table($this->tableName)->where($where)->delete();
        return $result;
    }

    /**
     * id查找
     * @param $where
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function findById($where){
        $result = Db::table($this->tableName)->where($where)->find();
        return $result;
    }
    public function select($field = '*', $where = array()){
        $result = Db::table($this->tableName)->field($field)->where($where)->select();
        return $result;
    }

    /**
     * 分页
     * @param string $field
     * @param $where
     * @param $num
     * @param $count
     * @return \think\paginator\Collection
     */
    public function selectPage($count){
        $num = 10;
        $data = Db::table($this->tableName)->paginate($num, $count);
        return $data;
    }
    public function pageWhere($count, $where){
        $num = 10;
        $data = Db::table($this->tableName)->where($where)->paginate($num, $count);
        return $data;
    }
    public function count($where){
        $count = Db::table($this->tableName)->where($where)->count();
        return $count;
    }

    /**
     * 模糊查询
     * @param $search
     * @return \think\paginator\Collection
     */
    public function searchLike($search){
        $num = 10;
        $count = Db::table($this->tableName)->join('client', $this->tableName.'.cid=client.cid')
            ->join('state', $this->tableName.'.sid=state.sid')
            ->join('problem_classify', $this->tableName.'.pcId=problem_classify.pcId')
            ->where("recorder", 'like', "%$search%")->whereOr('solution', 'like', "%$search%")
            ->whereOr("rid", 'like', "%$search%")->whereOr('classify', 'like', "%$search%")
            ->whereOr("description", 'like', "%$search%")->whereOr('solveCycle', 'like', "%$search%")
            ->whereOr('solved', 'like', "%$search%")->count();
        $record = Db::table($this->tableName)->join('client', $this->tableName.'.cid=client.cid')
            ->join('state', $this->tableName.'.sid=state.sid')
            ->join('problem_classify', $this->tableName.'.pcId=problem_classify.pcId')
            ->where("recorder", 'like', "%$search%")->whereOr('solution', 'like', "%$search%")
            ->whereOr("rid", 'like', "%$search%")->whereOr('classify', 'like', "%$search%")
            ->whereOr("description", 'like', "%$search%")->whereOr('solveCycle', 'like', "%$search%")
            ->whereOr('solved', 'like', "%$search%")->paginate($num, $count);
        return $record;
    }
    /**
     * 联合查询，分页
     * @param $join
     * @param $where
     * @param $field
     * @return false|mixed|\PDOStatement|string|\think\Collection
     * @Description 联合查询
     */
    public function joinSelect($joinTable, $param, $joinState, $param2, $joinClassify, $param3, $where, $field){
        $num = 10;
        $count = Db::table($this->tableName)->join($joinTable, $this->tableName.'.'.$param.'='.$joinTable.'.'.$param)
            ->join($joinState, $this->tableName.'.'.$param2.'='.$joinState.'.'.$param2)
            ->join($joinClassify, $this->tableName.'.'.$param3.'='.$joinClassify.'.'.$param3)
            ->field($field)->where($where)->count();
        $record = Db::table($this->tableName)->join($joinTable, $this->tableName.'.'.$param.'='.$joinTable.'.'.$param)
            ->join($joinState, $this->tableName.'.'.$param2.'='.$joinState.'.'.$param2)
            ->join($joinClassify, $this->tableName.'.'.$param3.'='.$joinClassify.'.'.$param3)
            ->field($field)->where($where)->paginate($num, $count);
        return $record;
    }

    /**
     * 联合查询，不分页
     * @param $joinTable
     * @param $param
     * @param $joinState
     * @param $param2
     * @param $where
     * @param $field
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function join($joinTable, $param, $joinState, $param2, $joinClassify, $param3, $where, $field){
        $record = Db::table($this->tableName)->join($joinTable, $this->tableName.'.'.$param.'='.$joinTable.'.'.$param)
            ->join($joinState, $this->tableName.'.'.$param2.'='.$joinState.'.'.$param2)
            ->join($joinClassify, $this->tableName.'.'.$param3.'='.$joinClassify.'.'.$param3)
            ->field($field)->where($where)->select();
        return $record;
    }
    /**
     * @return 记录编号
     */
    public function getRid()
    {
        return $this->rid;
    }

    /**
     * @param 记录编号 $rid
     */
    public function setRid($rid)
    {
        $this->rid = $rid;
    }

    /**
     * @return 问题来源
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param 问题来源 $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return 问题描述
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param 问题描述 $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return 解决时间
     */
    public function getSolveTime()
    {
        return $this->solveTime;
    }

    /**
     * @param 解决时间 $solveTime
     */
    public function setSolveTime($solveTime)
    {
        $this->solveTime = $solveTime;
    }

    /**
     * @return 是否解决
     */
    public function getSolved()
    {
        return $this->solved;
    }

    /**
     * @param 是否解决 $solved
     */
    public function setSolved($solved)
    {
        $this->solved = $solved;
    }





}