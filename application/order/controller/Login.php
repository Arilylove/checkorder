<?php
namespace app\order\controller;

use app\order\model\Admins;
use think\Controller;
use think\Db;
use org\Verify;
use app\order\crypt\AesCrypt;
use think\Lang;

/*
*登录控制器
*/
class Login extends Controller {

    public function admin(){
        $a = new Admins();
        return $a;
    }
    public function index(){
        return $this->fetch('login/index');
    }
    public function base(){
        $base = new Base();
        return $base;
    }
    public function logs(){
        $logs = new UserLog();
        return $logs;
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
            return $this->error('验证码错误');
        }
        //var_dump($hasAdmin);exit();
        session('orderuser', $admin['username']);
        session('surname', $hasAdmin['surname']);
        session('orderstatus', $hasAdmin['status']);     //保存用户权限，判断是管理员还是用户。
        session('sale_id', $hasAdmin['sale_id']);

        //获取用户的权限
        $ways = $this->base()->getWaysByUid($admin['username']);
        //1.如果没有权限
        if($ways == null){
            return $this->error(Lang::get('no authority'));
        }
        //登录日志添加
       /* $userLog = new UserLog();
        $userLog->setLog($admin['username'].'登录');*/

        //2.有权限，跳转到第一个权限页面
        $control = ucfirst($ways['w_control']['0']);
        $url = $control.'/index';
        //表计组
        if($hasAdmin['status'] == 2){
            $url = 'MeterOrder/index';
        }
        return $this->redirect($url);


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
            session('orderuser', null);
            session('surname', null);
            session('orderstatus', null);
            return $this->redirect('Login/index');
        }
        return false;
    }




    
}