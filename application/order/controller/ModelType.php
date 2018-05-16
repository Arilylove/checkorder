<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 13:34
 */
namespace app\order\controller;

class ModelType extends Base{

    public function index(){
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
        return $this->fetch('mot/add');
    }

    /**
     * 添加action
     */
    public function addMT(){
        $modelTypes = input('post.');
        $validate = $this->validate($modelTypes, 'Mot');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $result = $this->modelTypes()->add($modelTypes, '');
        if($result < 1){
            return $this->error("添加失败");
        }
        return $this->success("添加成功", 'ModelType/index');
    }
    /**
     * 跳转到批量添加页
     */
    public function batchAMT(){
        return $this->fetch('mot/batchadd');
    }

    /**
     * 批量添加action
     */
    public function batchAddMT(){
        $post = input('post.');
        //var_dump($post);exit();
        $modelType = $post['modelType'];
        //var_dump($modelType);exit();
        //1.先验证都通过了
        for ($i=0;$i<count($modelType);$i++){
            if($modelType[$i] == ''){
                continue;
            }
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
            return $this->error("添加失败");
        }
        return $this->success("添加成功", 'ModelType/index');

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
        $modelId = input('param.modelId');
        $where = array('modelId'=>$modelId);
        $find = $this->modelTypes()->findById($where);
        if(!$find){
            return $this->error('未找到该信息');
        }
        $modelTypes = input('post.');
        $validate = $this->validate($modelTypes, 'Mot');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $result = $this->modelTypes()->update($modelTypes, $where);
        if($result < 1){
            return $this->error("修改失败");
        }
        return $this->success("修改成功", 'ModelType/index');
    }

    /**
     * 跳转到删除页
     * @return mixed
     */
    public function dMT(){
        return $this->fetch("mot/del");
    }

    /**
     * 删除action
     */
    public function delMT(){
        $modelId = input('param.modelId');
        $where = array('modelId'=>$modelId);
        $find = $this->modelTypes()->findById($where);
        if(!$find){
            return $this->error('未找到该信息');
        }
        $result = $this->modelTypes()->del($where);
        if($result < 1){
            return $this->error("删除失败");
        }
        return $this->success("删除成功", 'ModelType/index');
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