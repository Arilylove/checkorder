<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/25
 * Time: 14:41
 */
namespace app\order\model;

use think\Db;
class SaleDepts{
    protected $tableName = 'sale_depts';


    public function add($order, $where){
        $result = Db::table($this->tableName)->where($where)->insert($order);
        return $result;
    }
    public function update($order, $where){
        $result = Db::table($this->tableName)->where($where)->update($order);
        return $result;
    }
    public function del($where){
        $result = Db::table($this->tableName)->where($where)->delete();
        return $result;
    }

    /**
     * 获取上次添加的id
     * @return string
     */
    public function lastSql(){
        $lastId = Db::table($this->tableName)->getLastInsID();
        return $lastId;
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
    public function select($field, $where){
        $result = Db::table($this->tableName)->field($field)->where($where)->select();
        return $result;
    }
    public function selectAll(){
        $result = Db::table($this->tableName)->select();
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
    public function selectPage($where, $count){
        $num = 10;
        $admin = Db::table($this->tableName)->where($where)->paginate($num, $count, ['query' => request()->param()]);
        return $admin;
    }

    public function count($where){
        $count = Db::table($this->tableName)->where($where)->count();
        return $count;
    }
    public function searchLike($search){
        $num = 10;
        $count = Db::table($this->tableName)->where('sale_name', 'like', "%$search%")->whereOr('remark', 'like', "%$search%")
            ->count();
        $data = Db::table($this->tableName)->where('sale_name', 'like', "%$search%")->whereOr('remark', 'like', "%$search%")
            ->paginate($num, $count, ['query' => request()->param()]);
        return $data;
    }

}