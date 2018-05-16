<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 13:29
 */
namespace app\order\model;

use think\Db;
/**
 * 生产负责人
 * Class ProductPrinciple
 * @package app\order\model
 */
class ProductPrinciple{
    protected $tableName = 'product_principle';

    protected $pid;
    protected $productPrinciple;
    protected $position;

    public function add($principle, $where){
        $result = Db::table($this->tableName)->where($where)->insert($principle);
        return $result;
    }
    public function update($principle, $where){
        $result = Db::table($this->tableName)->where($where)->update($principle);
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
        $count = Db::table($this->tableName)->where('pid', 'like', "%$search%")
            ->whereOr('productPrinciple', 'like', "%$search%")
            ->whereOr('position', 'like', "%$search%")
            ->count();
        $principles = Db::table($this->tableName)->where('pid', 'like', "%$search%")
            ->whereOr('productPrinciple', 'like', "%$search%")
            ->whereOr('position', 'like', "%$search%")
            ->paginate(10, $count);
        return $principles;
    }

    /**
     * @return mixed
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @param mixed $pid
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
    }

    /**
     * @return mixed
     */
    public function getProductPrinciple()
    {
        return $this->productPrinciple;
    }

    /**
     * @param mixed $productPrinciple
     */
    public function setProductPrinciple($productPrinciple)
    {
        $this->productPrinciple = $productPrinciple;
    }


    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }




}