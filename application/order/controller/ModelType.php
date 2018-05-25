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
 * 电子模块类型
 * Class ModelType
 * @package app\order\controller
 */
class ModelType extends Base{

    public function index(){
        $this->authVerify();
        $field = 'modelId,modelType';
        $count = $this->modelTypes()->count('');
        $modelTypes = $this->modelTypes()->selectPage($field, '', $count);
        $this->page($modelTypes);
        $this->assign('modelTypes', $modelTypes);
        return $this->fetch("mot/index");
    }

    /**
     * 跳转到添加页
     */
    public function aMT(){
        $auth = $this->auth('ModelType', 'aMT');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        return $this->fetch('mot/add');
    }

    /**
     * 添加action
     */
    public function addMT(){
        $auth = $this->auth('ModelType', 'aMT');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $modelTypes = input('post.');
        $validate = $this->validate($modelTypes, 'Mot');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $result = $this->modelTypes()->add($modelTypes, '');
        if($result < 1){
            return $this->error(Lang::get('add fail'));
        }
        return $this->success(Lang::get('add success'), 'ModelType/index');
    }
    /**
     * 跳转到批量添加页
     */
    public function batchAMT(){
        $auth = $this->auth('ModelType', 'aMT');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        return $this->fetch('mot/batchadd');
    }

    /**
     * 批量添加action
     */
    public function batchAddMT(){
        $auth = $this->auth('ModelType', 'aMT');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $post = input('post.');
        //var_dump($post);exit();
        $modelType = $post['modelType'];
        //var_dump($modelType);exit();
        //1.先验证都通过了
        for ($i=0;$i<count($modelType);$i++){
            $modelTypes['modelType'] = $modelType[$i];
            $validate = $this->validate($modelTypes, 'Mot');
            //var_dump($validate);exit();
            if(true !== $validate){
                return $this->error(" $validate ");
            }
            $insertAll[$i]['modelType'] = $modelType[$i];
        }
        //var_dump($insertAll);exit();
        //2.再批量添加
        $result = $this->modelTypes()->insertAll($insertAll);
        //var_dump($result);exit();
        if($result < 1){
            return $this->error(Lang::get('add fail'));
        }
        return $this->success(Lang::get('add success'), 'ModelType/index');

    }

    /**
     * json数据
     */
    public function modelType(){
        $modelId = input('param.modelId');
        $field = 'modelId,modelType';
        $where = array('modelId'=>$modelId);
        $data = $this->modelTypes()->select($field, $where);
        //var_dump($data);
        echo json_encode($data);
    }

    /**
     * 跳转到更新页
     * @return mixed
     */
    public function eMT(){
        $auth = $this->auth('ModelType', 'eMT');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $modelId = input('param.modelId');
        $field = 'modelId,modelType';
        $where = array('modelId'=>$modelId);
        $data = $this->modelTypes()->select($field, $where);
        $this->assign('modelTypes', $data['0']);
        return $this->fetch('mot/update');
    }

    /**
     * 更新action
     */
    public function editMT(){
        $auth = $this->auth('ModelType', 'eMT');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $modelId = input('param.modelId');
        $where = array('modelId'=>$modelId);
        $find = $this->modelTypes()->findById($where);
        if(!$find){
            return $this->error(Lang::get('unfind modeltype'));
        }
        $modelTypes = input('post.');
        $validate = $this->validate($modelTypes, 'Mot');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $result = $this->modelTypes()->update($modelTypes, $where);
        if($result < 1){
            return $this->error(Lang::get('edit fail'));
        }
        return $this->success(Lang::get('edit success'), 'ModelType/index');
    }

    /**
     * 跳转到删除页
     * @return mixed
     */
    public function dMT(){
        $auth = $this->auth('ModelType', 'dMT');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        return $this->fetch("mot/del");
    }

    /**
     * 删除action
     */
    public function delMT(){
        $auth = $this->auth('ModelType', 'dMT');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $modelId = input('param.modelId');
        $where = array('modelId'=>$modelId);
        $find = $this->modelTypes()->findById($where);
        if(!$find){
            return $this->error(Lang::get('unfind modeltype'));
        }
        $result = $this->modelTypes()->del($where);
        if($result < 1){
            return $this->error(Lang::get('del fail'));
        }
        return $this->success(Lang::get('del success'), 'ModelType/index');
    }
    /**
     * 简单的模糊搜索
     * @return mixed
     */
    public function search(){
        $search = input('param.search');
        $data = $this->modelTypes()->searchLike($search);
        $this->page($data);
        $this->assign('modelTypes', $data);
        return $this->fetch('mot/index');
    }



}