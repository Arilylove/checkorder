<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/5/8
 * Time: 15:36
 */
namespace app\record\controller;

use app\record\crypt\AesCrypt;
use app\record\model\Admins;

class Admin extends Base{


    /**
     * 引用其他类
     **/
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
        $field = 'username,surname,password,adId,status,createTime';
        $where = '';
        $order = 'createTime desc';
        $admin = $this->admin()->selectPage($field, $where, $order);
        $this->page($admin);
        $this->assign('admin', $admin);
        return $this->fetch("admin/index");
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

    /**
     * 跳转到添加页
     * @return mixed
     */
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
        $admin['password'] = '123456';
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


    /**
     * 跳转到编辑页
     * @return mixed
     */
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
        $admin['password'] = $this->hex()->encrypt($admin['password']);   //密码用AES加密；
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
    /**
     * @return mixed
     * 模糊查询
     */
    public function search(){
        $search = input('param.search');
        $data = $this->admin()->searchLike($search);
        $this->page($data);
        $this->assign('admin', $data);
        return $this->fetch('admin/index');

    }

}