<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/6/1
 * Time: 15:44
 */
namespace app\receipt\model;

use think\Db;

class Admins{
    protected $tableName = 'admin';
    protected $adId;
    protected $username;
    protected $password;
    protected $surname;
    protected $ctreateTime;
    protected $updateTime;
    protected $status;
    protected $admin;

    public function add($admin, $where){
        $result = Db::table($this->tableName)->where($where)->insert($admin);
        return $result;
    }
    public function update($admin, $where){
        $result = Db::table($this->tableName)->where($where)->update($admin);
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
    public function selectPage($field, $where, $order){
        $num = 10;
        $count = $this->count($where);
        $admin = Db::table($this->tableName)->field($field)->where($where)->order($order)->paginate($num, $count, ['query' => request()->param()]);
        return $admin;
    }
    public function count($where){
        $count = Db::table($this->tableName)->where($where)->count();
        return $count;
    }
    /**
     * 模糊查询
     * @param $search
     * @param $tableName2
     * @param $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function searchLike($search){
        $count = Db::table($this->tableName)
            ->where('adId',  'like', "%$search%")->whereOr('username', 'like', "%$search%")->whereOr('surname', 'like', "%$search%")->count();
        $username = Db::table($this->tableName)
            ->where('adId',  'like', "%$search%")->whereOr('username', 'like', "%$search%")->whereOr('surname', 'like', "%$search%")->paginate(10, $count, ['query' => request()->param()]);
        return $username;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @return mixed
     */
    public function getAdId()
    {
        return $this->adId;
    }

    /**
     * @param mixed $adId
     */
    public function setAdId($adId)
    {
        $this->adId = $adId;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @param mixed $surname
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    /**
     * @return mixed
     */
    public function getCtreateTime()
    {
        return $this->ctreateTime;
    }

    /**
     * @param mixed $ctreateTime
     */
    public function setCtreateTime($ctreateTime)
    {
        $this->ctreateTime = $ctreateTime;
    }

    /**
     * @return mixed
     */
    public function getUpdateTime()
    {
        return $this->updateTime;
    }

    /**
     * @param mixed $updateTime
     */
    public function setUpdateTime($updateTime)
    {
        $this->updateTime = $updateTime;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
    


}