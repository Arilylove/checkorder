<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/21
 * Time: 14:57
 */
namespace app\order\controller;

use app\order\model\Ways;

class Way extends Base{
    public function ways(){
        $ways = new Ways();
        return $ways;
    }
    /**
     * 列表页
     * @return mixed
     */
    public function index(){
        $field = 'wid,w_name,pid,w_control,w_way,url,create_time,status';
        $where = '';
        $order = 'create_time desc';
        $data = $this->ways()->page($field, $where, $order);

        //var_dump($data);exit();
        $this->page($data);
        $this->assign('ways', $data);
        return $this->fetch('way/index');
    }

    /**
     * 跳转到添加页
     */
    public function add(){
        $field = 'wid,w_name,pid,w_control,w_way,url,create_time,status';
        $where = '';
        $data = $this->ways()->select($field, $where);
        $this->assign('ways', $data);
        return $this->fetch('way/add');
    }

    /**
     * 添加action
     */
    public function save(){
        $post = input('post.');
        $data = array(
            'w_name'=>$post['w_name'],
            'pid'=>$post['pid'],
            'w_control'=>$post['w_control'],
            'w_way'=>$post['w_way'],
            'create_time'=>date('Y-m-d H:i:s', time()),
            'status'=>$post['status'],
            'url'=>$post['w_control'].'/'.$post['w_way']
        );
        //基本验证
        $validate = $this->validate($data, 'Ways');
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $add = $this->ways()->add($data, '');
        if(!$add){
            return $this->error('添加失败');
        }
        return $this->success("添加成功", 'Way/index');

    }

    /**
     * 跳转到修改页
     * @return mixed
     */
    public function edit(){
        $id = input('param.wid');
        $where = array('wid'=>$id);
        $find = $this->ways()->findById($where);
        $this->assign('way', $find);
        return $this->fetch('way/update');
    }

    /**
     * 修改action
     */
    public function eSave(){
        $id = input('param.wid');
        $where = array('wid'=>$id);
        $find = $this->ways()->findById($where);
        if(!$find){
            return $this->error('方法不存在');
        }
        $post = input('post.');
        $data = array(
            'w_name'=>$post['w_name'],
            'w_control'=>$post['w_control'],
            'w_way'=>$post['w_way'],
            'status'=>$post['status'],
            'url'=>$post['w_control'].'/'.$post['w_way']
        );
        //基本验证
        $validate = $this->validate($data, 'Ways');
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $update = $this->ways()->update($data, $where);
        if(!$update){
            return $this->error('修改失败');
        }
        return $this->success("修改成功", 'Way/index');

    }

    /**
     * 删除action
     */
    public function del(){
        $id = input('param.wid');
        $where = array('wid'=>$id);
        $find = $this->ways()->findById($where);
        if(!$find){
            return $this->error('方法不存在');
        }
        $del = $this->ways()->del($where);
        if(!$del){
            return $this->error('删除失败');
        }
        return $this->success("删除成功", 'Way/index');
    }


}