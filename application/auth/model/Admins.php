<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/18
 * Time: 17:53
 */
namespace app\auth\model;

use think\Db;
class Admins{
    protected $tableName = 'admins';
    protected $uid;
    protected $username;
    protected $password;
    protected $create_time;
    protected $email;
    protected $phone;
    /**
     * @var 部门id
     */
    protected $de_id;
    /**
     * @var 职位id
     */
    protected $po_id;

    /**
     * @var 角色联合表id
     */
    protected $role_id;
    protected $status;

    public function add($roles, $where){
        $result = Db::table($this->tableName)->where($where)->insert($roles);
        return $result;
    }
    public function update($roles, $where){
        $result = Db::table($this->tableName)->where($where)->update($roles);
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
    public function search($where){
        $search = Db::table($this->tableName)->where($where)->select();
        return $search;
    }

    public function count($where){
        $count = Db::table($this->tableName)->where($where)->count();
        return $count;
    }
    public function page($field, $where, $order){
        $num = 10;
        $count = $this->count($where);
        $data = Db::table($this->tableName)->field($field)->where($where)->order($order)->paginate($num, $count, ['query' => request()->param()]);
        return $data;
    }

}