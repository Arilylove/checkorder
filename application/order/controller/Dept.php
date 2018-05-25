<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/21
 * Time: 15:03
 */
namespace app\order\controller;

use app\order\model\Depts;
use think\Lang;

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
        $this->authVerify();
        $field = 'de_id,dept_name,description,create_time';
        $where = '';
        $order = 'create_time asc';
        $data = $this->depts()->page($field, $where, $order);
        $this->page($data);
        $this->assign('depts', $data);
        return $this->fetch('dept/index');
    }

    /**
     * 跳转到添加页
     */
    public function add(){
        $this->authVerify();
        return $this->fetch('dept/add');
    }

    /**
     * 添加action
     */
    public function save(){
        $auth = $this->auth('Dept', 'add');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $post = input('post.');
        $data = array(
            'dept_name'=>$post['dept_name'],
            'description'=>$post['description'],
            'create_time'=>date('Y-m-d H:i:s', time())
        );
        //基本验证
        $validate = $this->validate($data, 'Depts');
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $add = $this->depts()->add($data, '');
        if(!$add){
            return $this->error(Lang::get('add fail'));
        }
        return $this->success(Lang::get('add success'), 'Dept/index');

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
        $this->authVerify();
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
        $auth = $this->auth('Dept', 'edit');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $id = input('param.de_id');
        $where = array('de_id'=>$id);
        $find = $this->depts()->findById($where);
        if(!$find){
            return $this->error(Lang::get('unfind dept'));
        }
        $post = input('post.');
        $data = array(
            'dept_name'=>$post['dept_name'],
            'description'=>$post['description']
        );

        $update = $this->depts()->update($data, $where);
        if(!$update){
            return $this->error(Lang::get('edit fail'));
        }
        return $this->success(Lang::get('edit success'), 'Dept/index');

    }

    /**
     * 删除action
     */
    public function del(){
        $this->authVerify();
        $id = input('param.de_id');
        //var_dump($id);exit();
        $where = array('de_id'=>$id);
        $find = $this->depts()->findById($where);
        if(!$find){
            return $this->error(Lang::get('unfind dept'));
        }
        //如果该部门下有用户，则不允许删除
        $isExist = $this->admins()->findById(array('de_id'=>$id));
        //var_dump($isExist);exit();
        if($isExist){
            return $this->error(Lang::get('undel as exist user'));
        }
        $del = $this->depts()->del($where);
        if(!$del){
            return $this->error(Lang::get('del fail'));
        }
        return $this->success(Lang::get('del success'), 'Dept/index');
    }

    public function search(){
        $search = input('param.search');
        $data = $this->depts()->searchLike($search);
        //var_dump($data);exit();
        $this->page($data);
        $this->assign('depts', $data);
        return $this->fetch('Dept/index');
    }


}