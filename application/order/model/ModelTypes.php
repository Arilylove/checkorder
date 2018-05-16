<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 12:28
 */
namespace app\order\model;

use think\Db;
/**
 * 电子模块类型
 * Class ModelType
 * @package app\order\model
 */
class ModelTypes{
    protected $tableName = 'model_type';

    protected $modelId;
    protected $modelType;


    public function add($modelTypes, $where){
        $result = Db::table($this->tableName)->where($where)->insert($modelTypes);
        return $result;
    }
    public function update($modelTypes, $where){
        $result = Db::table($this->tableName)->where($where)->update($modelTypes);
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
        $count = Db::table($this->tableName)->where('modelId', 'like', "%$search%")
            ->whereOr('modelType', 'like', "%$search%")->count();
        $modelTypes = Db::table($this->tableName)->where('modelId', 'like', "%$search%")
            ->whereOr('modelType', 'like', "%$search%")->paginate(10, $count);
        return $modelTypes;
    }
    /**
     * @return mixed
     */
    public function getModelId()
    {
        return $this->modelId;
    }

    /**
     * @param mixed $modelId
     */
    public function setModelId($modelId)
    {
        $this->modelId = $modelId;
    }

    /**
     * @return mixed
     */
    public function getModelType()
    {
        return $this->modelType;
    }

    /**
     * @param mixed $modelType
     */
    public function setModelType($modelType)
    {
        $this->modelType = $modelType;
    }


}