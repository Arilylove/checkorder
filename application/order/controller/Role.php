<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/21
 * Time: 13:41
 */
namespace app\order\controller;

use app\order\model\Roles;

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
        $order = 'create_time asc';
        $data = $this->roles()->page($field, $where, $order);
        $this->page($data);
        $this->assign('roles', $data);
        return $this->fetch('role/index');
    }

    /**
     * 跳转到添加页
     */
    public function add(){
        //获取所有权限操作
        $field = 'wid,w_name,pid,w_control,w_way,url,create_time,status';
        $where = '';
        $data = $this->ways()->select($field, $where);
        $jsonData = json_encode($data);
        //var_dump($jsonData);exit();
        $this->assign('jsonways', $jsonData);
        $this->assign('ways', $data);
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
        //基本验证
        $validate = $this->validate($roles, 'Roles');
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        //var_dump($roles);exit();
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
        if(!$find){
            return $this->error("角色不存在");
        }
        $field = 'wid,w_name,pid,w_control,w_way,url,create_time,status';
        $where = '';
        $data = $this->ways()->select($field, $where);
        $jsonData = json_encode($data);
        $roleJson = json_encode($find);
        //var_dump($roleJson);exit();
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
            'wid'=>$post['wid'],
            'status'=>$post['status']
        );
        //基本验证
        $validate = $this->validate($roles, 'Roles');
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        //var_dump($roles);exit();
        $update = $this->roles()->update($roles, $where);
        if(!$update){
            return $this->error('修改失败');
        }
        return $this->success("修改成功", 'Role/index');

    }
    /**
     * json数据
     */
    public function orderJson(){
        $id = input('param.role_id');
        $where = array('role_id'=>$id);
        $field = 'role_id,role_name,remark,create_time,wid,status';
        $data = $this->roles()->select($field, $where);
        echo json_encode($data);
    }

    /**
     * 删除action
     */
    public function del(){
        $id = input('param.role_id');
        $where = array('role_id'=>$id);
        //不可删除有用户的角色
        $findOwnRole = $this->admins()->findById($where);
        if($findOwnRole){
            return $this->error("不可删除存在用户的角色");
        }
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

    public function search(){
        $search = input('param.search');
        $data = $this->roles()->searchLike($search);
        //var_dump($data);exit();
        $this->page($data);
        $this->assign('roles', $data);
        return $this->fetch('Role/index');

    }



}