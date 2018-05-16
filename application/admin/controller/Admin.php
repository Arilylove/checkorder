<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/5/8
 * Time: 15:36
 */
namespace app\admin\controller;

use app\admin\controller\Base;
use app\admin\crypt\AesCrypt;
use app\admin\model\Admins;
use app\admin\common\Common;
use think\Db;
use think\Request;

class Admin extends Base{

    /*
    * 控制器判断
    * */
    public function isAdmin(){
        $controll = lcfirst( request()->controller() );//控制器名
        if ($controll != 'Admin'){
            return false;
        }
        return true;
    }
    /**
     * 引用其他类
     **/
    public function com(){
        $com = new Common();
        return $com;
    }
    public function base(){
        $b= new Base();
        return $b;
    }
    public function admin(){
        $ad = new Admins();
        return $ad;
    }
    public function hex(){
        $hex = new AesCrypt();
        return $hex;
    }

    /**
	*查询修改的信息
	*/
    public function one(){
        $adId = input('param.adId');
        $where = array('adId'=>$adId);
        $field = 'adId,username,surname,password,status,createTime';
        $data = $this->admin()->select($field, $where);
        echo json_encode($data);
    }

    /**
     * 用户列表
     * */
    public function index(){
        $tableName2 = '';
        $param = '';
        $html = 'admin';
        $field = 'username,surname,password,adId,status,createTime';
        $where = '';
        $destination = 'admin/index';
        $tableAdmin = 'admin';
        $order = 'createTime desc';
        $listAd = $this->base()->searchBase($tableAdmin, $tableName2, $param, $html, $field, $where, $destination, $order);
        return $listAd;
    }

    public function table(){
        $field = 'adId,username,surname,password,createTime,status';
        $where = '';
        $admin = $this->admin()->select($field, $where);
        foreach ($admin as $key=>$value){
            if($admin[$key]['status'] == '0'){
                $admin[$key]['status'] = '管理员';
            }else{
                $admin[$key]['status'] = '用户';
            }
        }
        $this->assign('admin', json_encode($admin));
        //var_dump(json_encode($admin));exit();
        return $this->fetch("admin/table");
    }

    public function add(){
        return $this->fetch('admin/add');
    }
    
    /**
     * 增加用户
     * */
    public function addAdmin(){
        $hex = $this->hex();
        $ad = $this->admin();
        $admin = input('post.');
        //$postPassword = input('param.password');    //获取的默认密码值：123456；
        $admin['password'] = $hex->encrypt($admin['password']);   //密码用AES加密；
        $time = $_SERVER['REQUEST_TIME'];
        $admin['createTime'] = date('Y-m-d H:i:s', $time);
        //var_dump($admin);exit();
        $where = array();
        //判断
        $username = $admin['username'];
        $find = $ad->findById(array('username'=>$username));
        if($find){
            return $this->error('用户已存在');
        }
        $add = $ad->add($admin, $where);
        if (!$add){
            return $this->error('添加失败');
        }
        return $this->success('添加成功', 'admin/index');
    }
    

    public function edit(){
        $adId = input('param.adId');
        $where = array('adId'=>$adId);
        $field = 'adId,username,surname,password,status,createTime';
        $data = $this->admin()->select($field, $where);
        $this->assign('admin', $data[0]);
        return $this->fetch('admin/update');
    }
    /**
     * 用户信息编辑
     * */
    public function editAdmin(){
        
        $ad = $this->admin();
        $time = $_SERVER['REQUEST_TIME'];         //客户端向服务端发送请求的时间
        $admin = input('post.');
        $admin['updateTime'] = date('Y-m-d H:i:s', $time);
        //var_dump($admin);exit();
        $where = array('username'=>$admin['username']);
        $findUser = $ad->findById($where);
        //var_dump($findUser);exit();
        if (!$findUser){
            return $this->error('未找到该用户');
        }
        $result = $ad->update($admin, $where);
        if (!$result){
            return $this->error('修改失败');
        }
        //$param = "?page=".$currentPage;
        //var_dump($param);exit();
        return $this->success('修改成功', 'admin/index');
    }
    /**
     * 删除用户
     * */
    public function deleteAdmin(){
        $adId = input('param.adId');
        //var_dump($adId);exit();
        $admin = $this->admin();
        $where = array('adId'=>$adId);
        $user = $this->admin()->findById($where);
        $self = session('username');
        //var_dump($self);exit();
        if($self == $user['username']){
            return $this->error('不能删除自己');
        }
        if($user['username'] == 'administrator'){
            return $this->error('超级管理员不可删除');
        }
        $delete = $admin->del($where);
        if (!$delete){
            return $this->error('删除失败');
        }
        //弹出确认窗口
        return $this->success('删除成功', 'admin/index');
    }
    

}