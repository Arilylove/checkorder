<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 13:34
 */
namespace app\order\controller;

use think\Lang;

/**
 * 生产负责人
 * Class Principle
 * @package app\order\controller
 */
class Principle extends Base{

    public function index(){
        $this->authVerify();
        $field = 'pid,productPrinciple,position';
        $count = $this->principles()->count('');
        $principles = $this->principles()->selectPage($field, '', $count);
        $this->page($principles);
        $this->assign('principles', $principles);
        return $this->fetch("princ/index");
    }

    /**
     * 跳转到添加页
     */
    public function aPrinc(){
        $auth = $this->auth('Principle', 'aPrinc');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        return $this->fetch('princ/add');
    }

    /**
     * 添加action
     */
    public function addPrinc(){
        $auth = $this->auth('Principle', 'aPrinc');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $principles = input('post.');
        $validate = $this->validate($principles, 'Princ');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $result = $this->principles()->add($principles, '');
        if($result < 1){
            return $this->error(Lang::get('add fail'));
        }
        return $this->success(Lang::get('add success'), 'Principle/index');
    }

    /**
     * json数据
     */
    public function principle(){
        $pid = input('param.pid');
        $field = 'pid,productPrinciple,position';
        $where = array('pid'=>$pid);
        $data = $this->principles()->select($field, $where);
        //var_dump($data);
        echo json_encode($data);
    }

    /**
     * 跳转到更新页
     * @return mixed
     */
    public function ePrinc(){
        $auth = $this->auth('Principle', 'ePrinc');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $pid = input('param.pid');
        $field = 'pid,productPrinciple,position';
        $where = array('pid'=>$pid);
        $data = $this->principles()->select($field, $where);
        $this->assign('principles', $data['0']);
        return $this->fetch('princ/update');
    }

    /**
     * 更新action
     */
    public function editPrinc(){
        $auth = $this->auth('Principle', 'ePrinc');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $pid = input('param.pid');
        $where = array('pid'=>$pid);
        $find = $this->principles()->findById($where);
        if(!$find){
            return $this->error(Lang::get('unfind principle'));
        }
        $principles = input('post.');
        $validate = $this->validate($principles, 'Princ');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $result = $this->principles()->update($principles, $where);
        if($result < 1){
            return $this->error(Lang::get('edit fail'));
        }
        return $this->success(Lang::get('edit success'), 'Principle/index');
    }

    /**
     * 跳转到删除页
     * @return mixed
     */
    public function dPrinc(){
        $auth = $this->auth('Principle', 'dPrinc');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        return $this->fetch("princ/del");
    }

    /**
     * 删除action
     */
    public function delPrinc(){
        $auth = $this->auth('Principle', 'dPrinc');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $pid = input('param.pid');
        $where = array('pid'=>$pid);
        $find = $this->principles()->findById($where);
        if(!$find){
            return $this->error(Lang::get('unfind principle'));
        }
        $result = $this->principles()->del($where);
        if($result < 1){
            return $this->error(Lang::get('del fail'));
        }
        return $this->success(Lang::get('del success'), 'Principle/index');
    }

    /**
     * 简单的模糊搜索
     * @return mixed
     */
    public function search(){
        $search = input('param.search');
        $data = $this->principles()->searchLike($search);
        $this->page($data);
        $this->assign('principles', $data);
        return $this->fetch('princ/index');
    }


}