<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 13:34
 */
namespace app\order\controller;

class MeterType extends Base
{
    public function index(){
        $this->authVerify();
        $field = 'meterId,meterType';
        $count = $this->meterTypes()->count('');
        $meterTypes = $this->meterTypes()->selectPage($field, '', $count);
        $this->page($meterTypes);
        $this->assign('meterTypes', $meterTypes);
        return $this->fetch("met/index");
    }

    /**
     * 跳转到添加页
     */
    public function aMT(){
        $this->authVerify();
        return $this->fetch('met/add');
    }

    /**
     * 添加action
     */
    public function addMT(){
        $auth = $this->auth('MeterType', 'aMT');
        if(!$auth){
            return $this->error("对不起,没有权限");
        }
        $meterTypes = input('post.');
        $validate = $this->validate($meterTypes, 'Met');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        return $this->addOne($meterTypes);
    }

    /**
     * 单个的添加
     * @param $meterTypes
     */
    private function addOne($meterTypes){
        $result = $this->meterTypes()->add($meterTypes, '');
        if($result < 1){
            return $this->error("添加失败");
        }
        return $this->success("添加成功", 'MeterType/index');
    }

    /**
     * 跳转到批量添加页
     */
    public function batchAMT(){
        $auth = $this->auth('MeterType', 'aMT');
        if(!$auth){
            return $this->error("对不起,没有权限");
        }
        return $this->fetch('met/batchadd');
    }

    /**
     * 批量添加action
     */
    public function batchAddMT(){
        $auth = $this->auth('MeterType', 'aMT');
        if(!$auth){
            return $this->error("对不起,没有权限");
        }
        $post = input('post.');
        $meterType = $post['meterType'];
        //1.先验证都通过了
        for ($i=0;$i<count($meterType);$i++){
            $meterTypes['meterType'] = $meterType[$i];
            $validate = $this->validate($meterTypes, 'Met');
            //var_dump($validate);exit();
            if(true !== $validate){
                return $this->error(" $validate ");
            }
            $insertAll[$i]['meterType'] = $meterType[$i];
        }
        //2.再批量添加
        $result = $this->meterTypes()->insertAll($insertAll);
        if($result < 1){
            return $this->error("添加失败");
        }
        return $this->success("添加成功", 'MeterType/index');

    }


    /**
     * json数据
     */
    public function meterType(){
        $meterId = input('param.meterId');
        $field = 'meterId,meterType';
        $where = array('meterId'=>$meterId);
        $data = $this->meterTypes()->select($field, $where);
        //var_dump($data);
        echo json_encode($data);
    }

    /**
     * 跳转到更新页
     * @return mixed
     */
    public function eMT(){
        $this->authVerify();
        $meterId = input('param.meterId');
        $field = 'meterId,meterType';
        $where = array('meterId'=>$meterId);
        $data = $this->meterTypes()->select($field, $where);
        $this->assign('meterTypes', $data['0']);
        return $this->fetch('met/update');
    }

    /**
     * 更新action
     */
    public function editMT(){
        $auth = $this->auth('MeterType', 'eMT');
        if(!$auth){
            return $this->error("对不起,没有权限");
        }
        $meterId = input('param.meterId');
        $where = array('meterId'=>$meterId);
        $find = $this->meterTypes()->findById($where);
        if(!$find){
            return $this->error('未找到该信息');
        }
        $meterTypes = input('post.');
        $validate = $this->validate($meterTypes, 'Met');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $result = $this->meterTypes()->update($meterTypes, $where);
        if($result < 1){
            return $this->error("修改失败");
        }
        return $this->success("修改成功", 'MeterType/index');
    }

    /**
     * 跳转到删除页
     * @return mixed
     */
    public function dMT(){
        return $this->fetch("met/del");
    }

    /**
     * 删除action
     */
    public function delMT(){
        $auth = $this->auth('MeterType', 'dMT');
        if(!$auth){
            return $this->error("对不起,没有权限");
        }
        $meterId = input('param.meterId');
        //var_dump($meterId);exit();
        $where = array('meterId'=>$meterId);
        $find = $this->meterTypes()->findById($where);
        if(!$find){
            return $this->error('未找到该信息');
        }
        $result = $this->meterTypes()->del($where);
        if($result < 1){
            return $this->error("删除失败");
        }
        return $this->success("删除成功", 'MeterType/index');
    }

    /**
     * 简单的模糊搜索
     * @return mixed
     */
    public function search(){
        $search = input('param.search');
        $data = $this->meterTypes()->searchLike($search);
        $this->page($data);
        $this->assign('meterTypes', $data);
        return $this->fetch('met/index');
    }



}