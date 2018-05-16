<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/7
 * Time: 17:25
 */
namespace app\record\controller;

use app\record\model\Admins;
use app\record\model\Classifies;
use app\record\model\Solutions;
use think\Controller;
use think\Db;
use app\record\crypt\AesCrypt;
use app\record\model\Records;
use app\record\model\States;
use app\record\model\Clients;

class Base extends Controller{
    public function _initialize(){
        $username = session('username');
        $status = session('status');
        $a = is_null($username);
        //var_dump($a);exit();
        //判断用户是否已经登录
        if ($a) {
            $this->error('对不起,您还没有登录!请先登录', 'Login/index');
        }
        $this->assign("username", $username);
        $this->assign("status", $status);
        return true;
    }
    /**
     * model类
     * @return Records
     */
    protected function records(){
        $records = new Records();
        return $records;
    }
    protected function state(){
        $state = new States();
        return $state;
    }
    protected function clients(){
        $client = new Clients();
        return $client;
    }
    protected function classifies(){
        $classify = new Classifies();
        return $classify;
    }
    protected function admin(){
        $admin = new Admins();
        return $admin;
    }
    /**
     * 分页
     * @param $table
     */
    public function page($table){
        $page = $table->render();
        $currentPage = $table->currentPage();
        $this->assign('currentPage', $currentPage);
        $this->assign('pageOrder', ($currentPage-1)*10);
        $this->assign('page', $page);
    }

    /*
      *修改密码
      * */
    public function update(){
        $username = session('username');
        $this->assign('username', $username);
        return $this->fetch('lic/upPass');
    }

    public function updatePassword(){
        $username = session('username');
        //$this->assign('username', $username);
        $where = array('username'=>$username);
        $admin = Db::table('admin')->where($where)->find();
        if (!$admin){
            return $this->error('该用户不存在');
        }
        //var_dump($admin['password']);exit();
        $this->assign('adId', $admin['adId']);
        $string = new AesCrypt();
        //解密
        $password = $admin['password'];
        //var_dump($password);exit();
        $inputPassword = $string->encrypt(input('param.password'));
        $update = $string->encrypt(input('param.update'));
        $confirm = $string->encrypt(input('param.confirm'));
        if($password != $inputPassword){
            return $this->error('密码输入错误');
        }
        if ($update == $password){
            return $this->error('修改密码同原始密码相同');
        }
        if ($update == ''){
            return $this->error('密码不能为空');
        }
        if ($update != $confirm){
            return $this->error('两次输入密码不相同');
        }
        $result = Db::table('admin')->where('username', $username)->update(['password'=>$update]);
        //var_dump($result);exit();
        if (!$result){
            return $this->error('修改失败');
        }
        session('username', null);
        return $this->success('修改成功,返回登录界面', 'Login/index');

    }
    protected function assignClient(){
        $joinTable = 'state';
        $param = 'sid';
        $where = '';
        $field = 'cid,state,client';
        $client = $this->clients()->joinSelect($joinTable, $param, $where);
        //var_dump($client);exit();
        //如果一个国家下面有好几个客户
        $this->assign('clients', $client);
    }
    protected function assignCla(){
        $field = 'pcId,classify';
        $where = '';
        $classify = $this->classifies()->select($field, $where);
        $this->assign("classifies", $classify);
    }
    protected function assignAdmin(){
        $field = 'adId,username,surname';
        $where = '';
        $data = $this->admin()->select($field, $where);
        $this->assign('admins', $data);
    }


}