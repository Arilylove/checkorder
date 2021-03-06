<?php
namespace app\admin\controller;

use app\admin\model\Admins;
use think\Controller;
use think\Db;
use org\Verify;
use app\admin\crypt\AesCrypt;

/*
*登录控制器
*/
class Login extends Controller{

    public function admin(){
        $a = new Admins();
        return $a;
    }
    public function index(){
        return $this->fetch('login/index');
    }

    //后台登录
    public function login(){
        $admin['username'] = input('param.username');
        $admin['password'] = input('param.password');
        $hex = new AesCrypt();
        $admin['password'] = $hex->encrypt($admin['password']);

        $hasAdmin = Db::table('admin')->where('username', $admin['username'])->find();
        //var_dump($hasAdmin['password']);exit();
        if (empty($hasAdmin)){
            return $this->error('用户不存在', 'login/index');
        }

        if ($admin['password'] != $hasAdmin['password']){
            return $this->error('密码错误', 'login/index');
        }
        //$login = $this->admin()->findById(array('username'=>$admin['username']));
        //var_dump($login);exit();
        //$this->assign('surname', $login['surname']);
        //session('surname', $login['surname']); //保存到session中判断是否登录
        session('username', $admin['username']);
        session('status', $hasAdmin['status']); //保存用户权限，判断是管理员还是用户。
        //var_dump($hasAdmin['status']);exit;
        if ($hasAdmin['status'] == 0){
            return $this->success('登录成功', 'Admin/index');
        }
        return $this->success('登录成功', 'User/stLi');

    }
    //验证码
    public function verify(){
        $verify = new Verify();
        $verify->imageH = 36;
        $verify->imageW = 140;
        $verify->length = 4;
        $verify->useNoise = false;
        $verify->fontSize = 18;
        return $verify->entry();
    }

    public function out(){
        if (true){
            session('username', null);
            session('surname', null);
            session('status', null);
            return $this->fetch('login/index');
        }
        return false;
    }




    
}