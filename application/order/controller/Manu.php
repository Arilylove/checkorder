<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 13:33
 */
namespace app\order\controller;

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
            return $this->error("对不起,没有权限");
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
            return $this->error("对不起,没有权限");
        }
        $manus = input('post.');
        $validate = $this->validate($manus, 'Manus');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $result = $this->manus()->add($manus, '');
        if($result < 1){
            return $this->error("添加失败");
        }
        return $this->success("添加成功", 'Manu/index');
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
            return $this->error("对不起,没有权限");
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
            return $this->error("对不起,没有权限");
        }
        $mfId = input('param.mfId');
        $field = 'mfId,manufacturer';
        $where = array('mfId'=>$mfId);
        $find = $this->manus()->findById($where);
        if(!$find){
            return $this->error('未找到该信息');
        }
        $manus = input('post.');
        $validate = $this->validate($manus, 'Manus');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $result = $this->manus()->update($manus, $where);
        if($result < 1){
            return $this->error("修改失败");
        }
        return $this->success("修改成功", 'Manu/index');
    }

    /**
     * 跳转到删除页
     * @return mixed
     */
    public function dManu(){
        $auth = $this->auth('Manu', 'dManu');
        if(!$auth){
            return $this->error("对不起,没有权限");
        }
        return $this->fetch("manu/del");
    }
    public function delManu(){
        $auth = $this->auth('Manu', 'dManu');
        if(!$auth){
            return $this->error("对不起,没有权限");
        }
        $mfId = input('param.mfId');
        $where = array('mfId'=>$mfId);
        $find = $this->manus()->findById($where);
        if(!$find){
            return $this->error('未找到该信息');
        }
        $result = $this->manus()->del($where);
        if($result < 1){
            return $this->error("删除失败");
        }
        return $this->success("删除成功", 'Manu/index');
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