<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/21
 * Time: 15:03
 */
namespace app\order\controller;

use app\order\model\Depts;

class Dept extends Base{
    public function depts(){
        $depts = new Depts();
        return $depts;
    }
    /**
     * 列表页
     * @return mixed
     */
    public function index(){
        $field = 'de_id,dept_name,description,create_time';
        $where = '';
        $order = 'create_time desc';
        $data = $this->depts()->page($field, $where, $order);
        $this->page($data);
        $this->assign('depts', $data);
        return $this->fetch('dept/index');
    }

    /**
     * 跳转到添加页
     */
    public function add(){
        return $this->fetch('dept/add');
    }

    /**
     * 添加action
     */
    public function save(){
        $post = input('post.');
        $data = array(
            'dept_name'=>$post['dept_name'],
            'description'=>$post['description'],
            'create_time'=>date('Y-m-d H:i:s', time())
        );
        $add = $this->depts()->add($data, '');
        if(!$add){
            return $this->error('添加失败');
        }
        return $this->success("添加成功", 'Dept/index');

    }

    /**
     * json数据
     */
    public function deptJson(){
        $id = input('param.de_id');
        $field = 'de_id,dept_name,description,create_time';
        $where = array('de_id'=>$id);
        $data = $this->depts()->select($field, $where);
        //var_dump($data);exit();
        echo json_encode($data);
    }

    /**
     * 跳转到修改页
     * @return mixed
     */
    public function edit(){
        $id = input('param.de_id');
        $where = array('de_id'=>$id);
        $find = $this->depts()->findById($where);
        $this->assign('dept', $find);
        return $this->fetch('dept/update');
    }

    /**
     * 修改action
     */
    public function eSave(){
        $id = input('param.de_id');
        $where = array('de_id'=>$id);
        $find = $this->depts()->findById($where);
        if(!$find){
            return $this->error('部门不存在');
        }
        $post = input('post.');
        $data = array(
            'dept_name'=>$post['dept_name'],
            'description'=>$post['description']
        );
        $update = $this->depts()->update($data, $where);
        if(!$update){
            return $this->error('修改失败');
        }
        return $this->success("修改成功", 'Dept/index');

    }

    /**
     * 删除action
     */
    public function del(){
        $id = input('param.de_id');
        //var_dump($id);exit();
        $where = array('de_id'=>$id);
        $find = $this->depts()->findById($where);
        if(!$find){
            return $this->error('部门不存在');
        }
        //如果该部门下有用户，则不允许删除
        $isExist = $this->admins()->findById(array('de_id'=>$id));
        //var_dump($isExist);exit();
        if($isExist){
            return $this->error('存在用户,不允许删除');
        }
        $del = $this->depts()->del($where);
        if(!$del){
            return $this->error('删除失败');
        }
        return $this->success("删除成功", 'Dept/index');
    }


}