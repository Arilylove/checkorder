<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/25
 * Time: 14:40
 */
namespace app\receipt\controller;

class SaleDept extends Base{

    /**
     * 列表页
     * @return mixed
     */
    public function index(){
        $field = 'sale_id,sale_name,remark,create_time,status';
        $where = '';
        $data = $this->sales()->selectPage($field, $where);
        $this->page($data);
        $this->assign('saledepts', $data);
        return $this->fetch('sale/index');
    }

    /**
     * 跳转到添加页
     */
    public function add(){
        return $this->fetch('sale/add');
    }

    /**
     * 添加action
     */
    public function save(){
        $post = input('post.');
        $data = array(
            'sale_name'=>$post['dept_name'],
            'remark'=>$post['description'],
            'create_time'=>date('Y-m-d H:i:s', time()),
            'status'=>$post['status']
        );
        //基本验证
        /*$validate = $this->validate($data, 'SalesDepts');
        if(true !== $validate){
            return $this->error(" $validate ");
        }*/
        $add = $this->sales()->add($data, '');
        if(!$add){
            return $this->error(Lang::get('add fail'));
        }
        return $this->success(Lang::get('add success'), 'SaleDept/index');

    }

    /**
     * json数据
     */
    public function saleJson(){
        $id = input('param.sale_id');
        $field = 'sale_id,sale_name,remark,create_time,status';
        $where = array('sale_id'=>$id);
        $data = $this->sales()->select($field, $where);
        //var_dump($data);exit();
        echo json_encode($data);
    }

    /**
     * 跳转到修改页
     * @return mixed
     */
    public function edit(){
        $id = input('param.sale_id');
        $where = array('sale_id'=>$id);
        $find = $this->sales()->findById($where);
        $this->assign('saledept', $find);
        return $this->fetch('sale/update');
    }

    /**
     * 修改action
     */
    public function eSave(){
        $id = input('param.sale_id');
        $where = array('sale_id'=>$id);
        $find = $this->sales()->findById($where);
        if(!$find){
            return $this->error(Lang::get('unfind dept'));
        }
        $post = input('post.');
        $data = array(
            'sale_name'=>$post['sale_name'],
            'remark'=>$post['remark'],
            'status'=>$post['status']
        );

        $update = $this->sales()->update($data, $where);
        if(!$update){
            return $this->error(Lang::get('edit fail'));
        }
        return $this->success(Lang::get('edit success'), 'SaleDept/index');

    }

    /**
     * 删除action
     */
    public function del(){
        $id = input('param.sale_id');
        //var_dump($id);exit();
        $where = array('sale_id'=>$id);
        $find = $this->sales()->findById($where);
        if(!$find){
            return $this->error(Lang::get('unfind dept'));
        }
        //如果该部门下有用户，则不允许删除
        $isExist = $this->admins()->findById(array('sale_id'=>$id));
        //var_dump($isExist);exit();
        if($isExist){
            return $this->error(Lang::get('undel as exist user'));
        }
        $del = $this->sales()->del($where);
        if(!$del){
            return $this->error(Lang::get('del fail'));
        }
        return $this->success(Lang::get('del success'), 'SaleDept/index');
    }

    public function search(){
        $search = input('param.search');
        $data = $this->sales()->searchLike($search);
        //var_dump($data);exit();
        $this->page($data);
        $this->assign('saledepts', $data);
        return $this->fetch('SaleDept/index');
    }

}