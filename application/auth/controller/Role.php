<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/21
 * Time: 13:41
 */
namespace app\auth\controller;

use app\auth\model\Roles;

class Role extends Base{

    public function roles(){
        $roles = new Roles();
        return $roles;
    }

    /**
     * 列表页
     * @return mixed
     */
    public function index(){
        $field = 'role_id,role_name,remark,create_time,wid,status';
        $where = '';
        $order = 'create_time desc';
        $data = $this->roles()->page($field, $where, $order);
        $this->assignPage($data);
        $this->assignWays('1');
        $this->assign('roles', $data);
        return $this->fetch('role/index');
    }

    /**
     * 跳转到添加页
     */
    public function add(){
        $field = 'wid,w_name,pid,w_control,w_way,url,create_time,status';
        $where = '';
        $data = $this->ways()->select($field, $where);
        $jsonData = json_encode($data);
        //var_dump($jsonData);exit();
        $this->assign('jsonways', $jsonData);
        $this->assign('ways', $data);
        $this->assignWays('1');
        return $this->fetch('role/add');
    }

    /**
     * 添加action
     */
    public function save(){
        $post = input('post.');
        $roles = array(
          'role_name'=>$post['role_name'],
          'remark'=>$post['remark'],
          'create_time'=>date('Y-m-d H:i:s', time()),
           'wid'=>$post['wid'],
           'status'=>$post['status']
        );
        $add = $this->roles()->add($roles, '');
        if(!$add){
            return $this->error('添加失败');
        }
        return $this->success("添加成功", 'Role/index');

    }

    /**
     * 跳转到修改页
     * @return mixed
     */
    public function edit(){
        $id = input('param.role_id');
        $where = array('role_id'=>$id);
        $find = $this->roles()->findById($where);

        $field = 'wid,w_name,pid,w_control,w_way,url,create_time,status';
        $where = '';
        $data = $this->ways()->select($field, $where);
        $jsonData = json_encode($data);
        $roleJson = json_encode($find);
        //var_dump($jsonData);exit();
        $this->assign('jsonways', $jsonData);
        $this->assign('roleJson', $roleJson);
        $this->assign('role', $find);
        return $this->fetch('role/update');
    }

    /**
     * 修改action
     */
    public function eSave(){
        $id = input('param.role_id');
        $where = array('role_id'=>$id);
        $find = $this->roles()->findById($where);
        if(!$find){
            return $this->error('角色不存在');
        }
        $post = input('post.');
        $roles = array(
            'role_name'=>$post['role_name'],
            'remark'=>$post['remark'],
            'status'=>$post['status']
        );
        $update = $this->roles()->update($roles, $where);
        if(!$update){
            return $this->error('修改失败');
        }
        return $this->success("修改成功", 'Role/index');

    }

    /**
     * 删除action
     */
    public function del(){
        $id = input('param.role_id');
        $where = array('role_id'=>$id);
        $find = $this->roles()->findById($where);
        if(!$find){
            return $this->error('角色不存在');
        }
        $del = $this->roles()->del($where);
        if(!$del){
            return $this->error('删除失败');
        }
        return $this->success("删除成功", 'Role/index');
    }





}