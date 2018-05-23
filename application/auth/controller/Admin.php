<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/21
 * Time: 16:20
 */
namespace app\auth\controller;

class Admin extends Base{
    //允许删除用户，但是不可删除自己
    //允许修改用户的角色类型，但是不允许修改自己的角色类型
    /**
     * 列表页
     * @return mixed
     */
    public function index(){
        $field = 'uid,username,password,create_time,email,phone,de_id,role_id,po_id,status';
        $where = '';
        $order = 'create_time desc';
        $data = $this->admins()->page($field, $where, $order);
        $this->assignPage($data);
        $this->assign('admins', $data);
        return $this->fetch('admin/index');
    }

    /**
     * 跳转到添加页
     */
    public function add(){
        return $this->fetch('admin/add');
    }

    /**
     * 添加action
     */
    public function save(){
        $post = input('post.');
        $roles = array(
            'username'=>$post['username'],
            'remark'=>$post['remark'],
            'create_time'=>date('Y-m-d H:i:s', time()),
            'status'=>$post['status']
        );
        $add = $this->admins()->add($roles, '');
        if(!$add){
            return $this->error('添加失败');
        }
        return $this->success("添加成功", 'Admin/index');

    }

    /**
     * 跳转到修改页
     * @return mixed
     */
    public function edit(){
        $id = input('param.role_id');
        $where = array('role_id'=>$id);
        $find = $this->admins()->findById($where);
        $this->assign('admin', $find);
        return $this->fetch('admin/update');
    }

    /**
     * 修改action
     */
    public function eSave(){
        $id = input('param.role_id');
        $where = array('role_id'=>$id);
        $find = $this->admins()->findById($where);
        if(!$find){
            return $this->error('角色不存在');
        }
        $post = input('post.');
        $roles = array(
            'role_name'=>$post['role_name'],
            'remark'=>$post['remark'],
            'create_time'=>$post['create_time'],
            'status'=>$post['status']
        );
        $update = $this->admins()->update($roles, $where);
        if(!$update){
            return $this->error('修改失败');
        }
        return $this->success("修改成功", 'Admin/index');

    }

    /**
     * 删除action
     */
    public function del(){
        $id = input('param.role_id');
        $where = array('role_id'=>$id);
        $find = $this->admins()->findById($where);
        if(!$find){
            return $this->error('角色不存在');
        }
        $del = $this->admins()->del($where);
        if(!$del){
            return $this->error('删除失败');
        }
        return $this->success("删除成功", 'Admin/index');
    }


}