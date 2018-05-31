<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2018/5/8
 * Time: 15:36
 */
namespace app\receipt\controller;

use think\Lang;
class Admin extends Base{


    /**
	*查询修改的信息
	*/
    public function one(){
        $uid = input('param.uid');
        $where = array('uid'=>$uid);
        $field = 'uid,username,surname,password,status,create_time,sale_id';
        $data = $this->admins()->select($field, $where);
        echo json_encode($data);
    }

    /**
     * 用户列表
     * */
    public function index(){
        $field = 'username,surname,password,uid,status,create_time,sale_id';
        $where = '';
        $order = 'create_time desc';
        $admin = $this->admins()->selectPage($field, $where, $order);
        $admin = $this->resetAdmins($admin);
        $this->page($admin);
        $this->assign('admin', $admin);
        return $this->fetch("admin/index");
    }

    /**
     * 跳转到添加页
     * @return mixed
     */
    public function add(){
        $this->assignSale();
        return $this->fetch('admin/add');
    }
    
    /**
     * 增加用户
     * */
    public function save(){
        $hex = $this->hex();
        $ad = $this->admins();
        $admin = input('post.');
        //$postPassword = input('param.password');    //获取的默认密码值：123456；
        $admin['password'] = '123456';
        $admin['password'] = $hex->encrypt($admin['password']);   //密码用AES加密；
        $time = $_SERVER['REQUEST_TIME'];
        $admin['create_time'] = date('Y-m-d H:i:s', $time);
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
        $uid = input('param.uid');
        $where = array('uid'=>$uid);
        //$field = 'uid,username,surname,password,status,create_time,sale_id';
        $data = $this->admins()->findById($where);
        $data = $this->resetAdmin($data);
        $this->assign('admin', $data);
        $this->assignSale();
        return $this->fetch('admin/update');
    }
    /**
     * 用户信息编辑
     * */
    public function esave(){
        $ad = $this->admins();
        $time = $_SERVER['REQUEST_TIME'];         //客户端向服务端发送请求的时间
        $admin = input('post.');
        $admin['update_time'] = date('Y-m-d H:i:s', $time);
        //var_dump($admin);exit();
        $where = array('username'=>$admin['username']);
        $findUser = $ad->findById($where);
        //var_dump($findUser);exit();
        if (!$findUser){
            return $this->error(Lang::get('unfind user'));
        }
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
    public function del(){
        $uid = input('param.uid');
        //var_dump($uid);exit();
        $admin = $this->admins();
        $where = array('uid'=>$uid);
        $user = $this->admins()->findById($where);
        $self = session('username');
        //var_dump($self);exit();
        if($self == $user['username']){
            return $this->error(Lang::get('del self unallowed'));
        }
        if($user['username'] == 'administrator'){
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
     * 重组admin数据(二维数组)
     */
    private function resetAdmins($admins){
        $len = count($admins);
        if($len >= 1){
            for($i=0;$i<$len;$i++){
                $admins[$i] = $this->resetAdmin($admins[$i]);
            }
        }
        return $admins;
    }
    /**
     * 重组admin数据(一维数组)
     */
    private function resetAdmin($admin){
        $len = count($admin);
        if($len >= 1){
            $status = $admin['status'];
            $is_status = $this->resetStatus($status);
            $admin['is_status'] = $is_status;
            $sale_id = $admin['sale_id'];
            $findSaleDepts = $this->sales()->findById(array('sale_id'=>$sale_id));
            $admin['sale_name'] = $findSaleDepts['sale_name'];

        }else{
            $admin['is_status'] = '';
            $admin['sale_name'] = '';
        }
        return $admin;
    }

    /**
     * 用户状态值
     * @param $status
     * @return string
     */
    private function resetStatus($status){
        $is_status = '';
        switch ($status){
            case 0:
                $is_status = '管理员';
                break;
            case 1:
                $is_status = '用户';
                break;
            case 2:
                $is_status = '表计组';
                break;
            default:
                break;

        }
        return $is_status;
    }
}