<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/21
 * Time: 15:08
 */
namespace app\auth\controller;

use app\auth\model\Positions;

class Position extends Base{
    public function positions(){
        $positions = new Positions();
        return $positions;
    }

    /**
     * 列表页
     * @return mixed
     */
    public function index(){
        $field = 'po_id,po_name,role_id,create_time';
        $where = '';
        $order = 'create_time desc';
        $data = $this->positions()->page($field, $where, $order);
        $this->assignPage($data);
        $this->assign('positions', $data);
        return $this->fetch('position/index');
    }

    /**
     * 跳转到添加页
     */
    public function add(){
        return $this->fetch('position/add');
    }

    /**
     * 添加action
     */
    public function save(){
        $post = input('post.');
        $data = array(
            'po_name'=>$post['po_name'],
            'role_id'=>$post['role_id'],
            'create_time'=>date('Y-m-d H:i:s', time())
        );
        $add = $this->positions()->add($data, '');
        if(!$add){
            return $this->error('添加失败');
        }
        return $this->success("添加成功", 'Position/index');

    }

    /**
     * 跳转到修改页
     * @return mixed
     */
    public function edit(){
        $id = input('param.po_id');
        $where = array('po_id'=>$id);
        $find = $this->positions()->findById($where);
        $this->assign('position', $find);
        return $this->fetch('position/update');
    }

    /**
     * 修改action
     */
    public function eSave(){
        $id = input('param.po_id');
        $where = array('po_id'=>$id);
        $find = $this->positions()->findById($where);
        if(!$find){
            return $this->error('职位不存在');
        }
        $post = input('post.');
        $data = array(
            'po_name'=>$post['po_name'],
            'role_id'=>$post['role_id'],
            'create_time'=>$post['create_time']
        );
        $update = $this->positions()->update($data, $where);
        if(!$update){
            return $this->error('修改失败');
        }
        return $this->success("修改成功", 'Position/index');

    }

    /**
     * 删除action
     */
    public function del(){
        $id = input('param.po_id');
        $where = array('po_id'=>$id);
        $find = $this->positions()->findById($where);
        if(!$find){
            return $this->error('职位不存在');
        }
        $del = $this->positions()->del($where);
        if(!$del){
            return $this->error('删除失败');
        }
        return $this->success("删除成功", 'Position/index');
    }

}