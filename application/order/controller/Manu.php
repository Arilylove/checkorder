<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 13:33
 */
namespace app\order\controller;

use think\Lang;

class Manu extends Base{


    public function index(){
        $this->authVerify();
        $field = 'mfId,manufacturer';
        $count = $this->manus()->count('');
        $manus = $this->manus()->selectPage($field, '', $count);
        $this->page($manus);
        $this->assign('manus', $manus);
        return $this->fetch("manu/index");
    }

    /**
     * 跳转到添加页
     */
    public function aManu(){
        $auth = $this->auth('Manu', 'aManu');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $this->assignState();
        return $this->fetch('manu/add');
    }

    /**
     * 添加action
     */
    public function addManu(){
        $auth = $this->auth('Manu', 'aManu');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $manus = input('post.');
        $validate = $this->validate($manus, 'Manus');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $result = $this->manus()->add($manus, '');
        if($result < 1){
            return $this->error(Lang::get('add fail'));
        }
        return $this->success(Lang::get('add success'), 'Manu/index');
    }

    /**
     * json数据
     */
    public function manu(){
        $mfId = input('param.mfId');
        $field = 'mfId,manufacturer';
        $where = array('mfId'=>$mfId);
        $data = $this->manus()->select($field, $where);
        //var_dump($data);
        echo json_encode($data);
    }

    /**
     * 跳转到更新页
     * @return mixed
     */
    public function eManu(){
        $auth = $this->auth('Manu', 'eManu');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $mfId = input('param.mfId');
        $where = array('mfId'=>$mfId);
        $data = $this->manus()->findById($where);
        $this->assign('manus', $data);
        $this->assignState();
        return $this->fetch('manu/update');
    }

    /**
     * 更新action
     */
    public function editManu(){
        $auth = $this->auth('Manu', 'eManu');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $mfId = input('param.mfId');
        $field = 'mfId,manufacturer';
        $where = array('mfId'=>$mfId);
        $find = $this->manus()->findById($where);
        if(!$find){
            return $this->error(Lang::get('unfind manufacturer'));
        }
        $manus = input('post.');
        $validate = $this->validate($manus, 'Manus');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $result = $this->manus()->update($manus, $where);
        if($result < 1){
            return $this->error(Lang::get('edit fail'));
        }
        return $this->success(Lang::get('edit success'), 'Manu/index');
    }

    /**
     * 跳转到删除页
     * @return mixed
     */
    public function dManu(){
        $auth = $this->auth('Manu', 'dManu');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        return $this->fetch("manu/del");
    }
    public function delManu(){
        $auth = $this->auth('Manu', 'dManu');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $mfId = input('param.mfId');
        $where = array('mfId'=>$mfId);
        $find = $this->manus()->findById($where);
        if(!$find){
            return $this->error(Lang::get('unfind manufacturer'));
        }
        $result = $this->manus()->del($where);
        if($result < 1){
            return $this->error(Lang::get('del fail'));
        }
        return $this->success(Lang::get('del success'), 'Manu/index');
    }

    /**
     * 简单的模糊搜索
     * @return mixed
     */
    public function search(){
        $search = input('param.search');
        $data = $this->manus()->searchLike($search);
        $this->page($data);
        $this->assign('manus', $data);
        return $this->fetch('manu/index');
    }





}