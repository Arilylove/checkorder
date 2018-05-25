<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/5/8
 * Time: 15:36
 */
namespace app\order\controller;

use app\order\crypt\AesCrypt;
use app\order\model\Admins;
use think\Lang;

class Admin extends Base{


    /**
	*查询修改的信息
	*/
    public function one(){
        $adId = input('param.adId');
        $where = array('adId'=>$adId);
        $field = 'adId,username,surname,password,status,createTime,role_id,de_id';
        $data = $this->admins()->select($field, $where);
        echo json_encode($data);
    }

    /**
     * 用户列表
     * */
    public function index(){
        $this->authVerify();
        $field = 'username,surname,password,adId,status,createTime,role_id,de_id';
        //显示比自己权限小的用户（0管理员，1用户，2表计组）
        $status = session('orderstatus');
        if($status == 3){
            $where = '';
        }else{
            $where = array('status'=>[['>=', $status], ['<>', 3]]);
        }
        //$where = '';
        $order = 'createTime asc';
        $admin = $this->admins()->selectPage($field, $where, $order);
        $admin = $this->resetAdmins($admin);
        //var_dump($admin);exit();
        $this->page($admin);
        $this->assign('admin', $admin);
        return $this->fetch("admin/index");
    }


    /**
     * 跳转到添加页
     * @return mixed
     */
    public function add(){
        $this->authVerify();
        $this->assignDept();
        $this->assignRole();
        return $this->fetch('admin/add');
    }
    
    /**
     * 增加用户
     * */
    public function addAdmin(){
        $hex = $this->hex();
        $ad = $this->admins();
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
            return $this->error(Lang::get('existed user'));
        }
        $add = $ad->add($admin, $where);
        if (!$add){
            return $this->error(Lang::get('add fail'));
        }
        return $this->success(Lang::get('add success'), 'admin/index');
    }


    /**
     * 跳转到编辑页
     * @return mixed
     */
    public function edit(){
        $this->authVerify();
        $adId = input('param.adId');
        $where = array('adId'=>$adId);
        $field = 'adId,username,surname,password,status,createTime,role_id,de_id';
        $data = $this->admins()->findById($where);
        //var_dump($data);exit();
        $data = $this->resetAdmin($data);
        $this->assignDept();
        $this->assignRole();
        $this->assign('admin', $data);
        return $this->fetch('admin/update');
    }
    /**
     * 用户信息编辑
     * */
    public function editAdmin(){
        $auth = $this->auth('Admin', 'edit');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $ad = $this->admins();
        $time = $_SERVER['REQUEST_TIME'];         //客户端向服务端发送请求的时间
        $admin = input('post.');
        $admin['password'] = $this->hex()->encrypt($admin['password']);   //密码用AES加密；
        $admin['updateTime'] = date('Y-m-d H:i:s', $time);
        $where = array('username'=>$admin['username']);
        $findUser = $ad->findById($where);
        //var_dump($findUser);exit();
        if (!$findUser){
            return $this->error(Lang::get('unfind user'));
        }
        //var_dump($admin);exit();
        $result = $ad->update($admin, $where);
        if (!$result){
            return $this->error(Lang::get('edit fail'));
        }
        //$param = "?page=".$currentPage;
        //var_dump($param);exit();
        return $this->success(Lang::get('edit success'), 'admin/index');
    }
    /**
     * 删除用户
     * */
    public function deleteAdmin(){
        $auth = $this->auth('Admin', 'del');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $adId = input('param.adId');
        //var_dump($adId);exit();
        $admin = $this->admins();
        $where = array('adId'=>$adId);
        $user = $this->admins()->findById($where);
        $self = session('orderuser');
        //var_dump($self);exit();
        if($self == $user['username']){
            return $this->error(Lang::get('del self unallowed'));
        }
        if($user['status'] == 3){
            return $this->error(Lang::get('del root unallowed'));
        }
        $delete = $admin->del($where);
        if (!$delete){
            return $this->error(Lang::get('del fail'));
        }
        //弹出确认窗口
        return $this->success(Lang::get('del success'), 'admin/index');
    }
    /**
     * @return mixed
     * 模糊查询
     */
    public function search(){
        $search = input('param.search');
        $data = $this->admins()->searchLike($search);
        $this->page($data);
        $this->assign('admin', $data);
        return $this->fetch('admin/index');

    }

    /**
     * 用户信息重新组合
     * @param $admins
     * @return mixed
     */
    private function resetAdmins($admins){
        $len = count($admins);
        for ($i=0;$i<$len;$i++){
            $admins[$i] = $this->resetAdmin($admins[$i]);
        }
        return $admins;
    }

    /**
     * 单个用户信息重新组合
     * @param $admin
     */
    private function resetAdmin($admin){
        $len = count($admin);
        if($len >= 1){
            $de_id = $admin['de_id'];
            $role_id = $admin['role_id'];
            $findDept = $this->depts()->findById(array('de_id'=>$de_id));
            $findRole = $this->roles()->findById(array('role_id'=>$role_id));
            $admin['role_name'] = $findRole['role_name'];
            $admin['dept_name'] = $findDept['dept_name'];
        }else{
            $admin['role_name'] = '';
            $admin['dept_name'] = '';
        }
        return $admin;

    }

}