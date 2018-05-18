<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/6/1
 * Time: 15:43
 */
namespace app\admin\model;

use think\Db;

class Clients{
    protected $tableName = 'client';
    protected $cliId;
    protected $stId;
    protected $company;
    protected $contacts;
    protected $email;
    protected $phone;
    protected $app;

    public function add($client, $where){
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
    public function select($field, $where){
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
    public function joinSelect($tableName2, $param, $where, $field){
        $client = Db::table($this->tableName)->join($tableName2, $this->tableName.'.'.$param.'='.$tableName2.'.'.$param)->field($field)->where($where)->select();
        return $client;
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
    public function getStId()
    {
        return $this->stId;
    }

    /**
     * @param mixed $stId
     */
    public function setStId($stId)
    {
        $this->stId = $stId;
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param mixed $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return mixed
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param mixed $contacts
     */
    public function setContacts($contacts)
    {
        $this->contacts = $contacts;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param mixed $app
     */
    public function setApp($app)
    {
        $this->app = $app;
    }
    
    /*
    * 公共类（Common）
    * */
    public function com(){
        $com = new Common();
        return $com;
    }
    /*
     * base类（Base）
     * */
    public function base(){
        $base = new Base();
        return $base;
    }
    
    /*
     * 客户列表 search($tableName1, $tableName2, $param, $html, $field, $destination)
     * */
    public function listClient(){
        $param = 'stId';
        $html = 'client';
        $where = '';
        $field = 'cliId,state,contacts,phone,company,email,app';
        //需要从国家列表中获取国家名称
        $where = '';
        $states = $this->com()->select($this->tableStates, $where);
        $destination = 'user/listClient';
        $this->assign('states', $states);
        //当前国家$tableName1, $tableName2 = '', $param, $html, $field, $where, $destination
        $search = $this->base()->search($this->tableClient, $this->tableStates, $param, $html, $field, $where, $destination);
        //var_dump($search);
        return $search;
    }

    /*
     *查询修改客户的信息
     */
    public function client(){
        $cliId = input('param.cliId');
        $where = array('cliId', $cliId);
        $data = $this->com()->select($this->tableClient, $where);
        echo json_encode($data);
    }

    /*
     * 增加客户
     * */
    public function add1(){
        //$cliId = mt_rand(0, 9999);
        //以国家名称获取国家编号
        $state = input('param.state');
        //var_dump($state);exit();
        $field = 'stId';
        $where = array('state'=>$state);
        $stId = $this->com()->findParam($where, $this->tableStates);
        //echo $stId->getLastSql();exit();
        //var_dump($stId);exit();
        $cliId = input('param.cliId');
        //var_dump($stId['stId']);exit();
        $company = input('param.company');
        $contacts = input('param.contacts');
        $phone = input('param.phone');
        $email = input('param.email');
        $app = input('param.app');
        $client = array(
            'cliId'=> $cliId,
            'stId'=> $stId['stId'],
            'company'=> $company,
            'contacts'=> $contacts,
            'phone'=> $phone,
            'email'=> $email,
            'app'=> $app
        );
        //var_dump($client);exit();
        $cliId = $this->com()->find('cliId', array('cliId'=>$cliId), $this->tableClient);
        if (!$cliId){
            return $this->error('该客户编号已存在');
        }
        $validate = $this->validate($client, 'Client');
        if (true !== $validate){
            //return $this->error(" $validate ", $_SERVER['HTTP_REFERER']);
            return $this->error(" $validate ");
        }
        $result = $this->com()->add($client, $this->tableClient);
        if (!$result){
            return $this->error('添加失败');
        }
        return $this->success('添加成功', 'user/listClient');
    }
    /*
     * 删除客户
     * */
    public function delete(){

        
    }

    /*
     * 客户信息编辑
     * */
    public function edit(){

        $cliId = input('param.cliId');
        $find = $this->com()->find('cliId', array('cliId'=>$cliId), $this->tableClient);
        if ($find){
            return $this->error('未找到该客户');
        }
        $state = input('param.state');
        $stId = Db::table($this->tableStates)->where('state', $state)->find();
        //var_dump($stId);exit();
        $company = input('param.company');
        $contacts = input('param.contacts');
        $phone = input('param.phone');
        $email = input('param.email');
        $app = input('param.app');
        $client = array(
            'stId' => $stId,
            'company' => $company,
            'contacts' => $contacts,
            'phone' => $phone,
            'email' => $email,
            'app' => $app
        );
        //var_dump($client);exit();
        $validate = $this->validate($client, 'Client');
        if (true !== $validate){
            return $this->error(" $validate ");
        }
        $result = Db::table($this->tableClient)->where('cliId', $cliId)->update($client);
        //var_dump($result);exit();
        return $result;
    }

}