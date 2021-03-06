<?php
namespace app\receipt\controller;

use app\receipt\model\Admins;
use think\Controller;
use think\Db;
use org\Verify;
use app\receipt\crypt\AesCrypt;
use think\Lang;

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
            return $this->error(Lang::get('no user exist'));
        }

        if ($admin['password'] != $hasAdmin['password']){
            return $this->error(Lang::get('pass wrong'));
        }
        $code = input("post.code");
        //var_dump($username);exit;
        $verify = new Verify();
        $check = $verify->check($code);
        if (!$check){
            return $this->error(Lang::get('verify code wrong'));
        }
        //var_dump($admin['username']);exit();
        session('receiptuser', $admin['username']);
        session('receipt_status', $hasAdmin['status']); //保存用户权限，判断是管理员还是用户。
        session('receipt_surname', $hasAdmin['surname']);
        //var_dump($hasAdmin['status']);exit;
        if ($hasAdmin['status'] == 0){
            return $this->redirect('Admin/index');
        }
        return $this->redirect('State/index');

    }
    //验证码
    public function verify(){
        $verify = new Verify();
        $verify->imageH = 36;
        $verify->imageW = 140;
        $verify->length = 4;
        $verify->useNoise = false;
        $verify->fontSize = 18;
        $verify->fontttf = '4.ttf';
        return $verify->entry();
    }

    public function out(){
        if (true){
            session('receiptuser', null);
            session('surname', null);
            return $this->fetch('login/index');
        }
        return false;
    }




    
}