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
 * 基表型号
 * Class MeterType
 * @package app\order\controller
 */
class MeterType extends Base
{
    public function index(){
        $this->authVerify();
        $field = 'meterId,meterType,pid';
        $count = $this->meterTypes()->count('');
        $meterTypes = $this->meterTypes()->selectPage($field, '', $count);
        $meterTypes = $this->dealMeterType($meterTypes);
        $this->page($meterTypes);
        $this->assign('meterTypes', $meterTypes);
        return $this->fetch("met/index");
    }

    /**
     * 跳转到添加页
     */
    public function aMT(){
        $auth = $this->auth('MeterType', 'aMT');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        //获取所有主类
        $allPid = $this->getAllPid();
        $this->assign('allPids', $allPid);
        return $this->fetch('met/add');
    }


    /**
     * 添加action
     */
    public function addMT(){
        $auth = $this->auth('MeterType', 'aMT');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $meterTypes = input('post.');
        //空格处理
        $meterTypes['pid'] = trim($meterTypes['pid']);
        $meterTypes['meterType'] = trim($meterTypes['meterType']);
        $pid = $meterTypes['pid'];
        //先查找数据库中是否存在
        $find = $this->meterTypes()->findById(array('meterId'=>$pid));
        if(!$find){
            $parentOne['meterType'] = $pid;
            $parentOne['pid'] = 0;
            $this->meterTypes()->add($parentOne, '');
            $lastId = $this->meterTypes()->getLastId();
            if($lastId){
                $meterTypes['pid'] = $lastId;
            }else{
                return $this->error(Lang::get('add fail'));
            }
        }
        $validate = $this->validate($meterTypes, 'Met');
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
            return $this->error(Lang::get('add fail'));
        }
        return $this->success(Lang::get('add success'), 'MeterType/index');
    }

    /**
     * 跳转到批量添加页
     */
    public function batchAMT(){
        $auth = $this->auth('MeterType', 'aMT');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        //获取所有主类
        $allPid = $this->getAllPid();
        $this->assign('allPids', $allPid);
        return $this->fetch('met/batchadd');
    }

    /**
     * 批量添加action
     */
    public function batchAddMT(){
        $auth = $this->auth('MeterType', 'aMT');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $post = input('post.');
        $pid = $post['pid'];
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
            $insertAll[$i]['pid'] = $pid[$i];
        }
        //2.再批量添加
        $result = $this->meterTypes()->insertAll($insertAll);
        if($result < 1){
            return $this->error(Lang::get('add fail'));
        }
        return $this->success(Lang::get('add success'), 'MeterType/index');

    }


    /**
     * json数据
     */
    public function meterType(){
        $meterId = input('param.meterId');
        $field = 'meterId,meterType,pid';
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
        $auth = $this->auth('MeterType', 'eMT');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        //1.当前数据
        $meterId = input('param.meterId');
        $where = array('meterId'=>$meterId);
        $data = $this->meterTypes()->findById($where);
        $findPid = $data['pid'];
        $parentPid = $this->meterTypes()->findById(array('meterId'=>$findPid));
        if(!$parentPid){
            $data['parent'] = '主类';
        }else{
            $data['parent'] = $parentPid['meterType'];
        }
        $this->assign('meterTypes', $data);
        //2.所有主类
        $allPid = $this->getAllPid();
        $this->assign('allPids', $allPid);
        $page = input('param.page');
        //var_dump($query);exit();
        $this->assign('currentPage', $page);
        return $this->fetch('met/update');
    }


    /**
     * 更新action
     */
    public function editMT(){
        $auth = $this->auth('MeterType', 'eMT');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        //var_dump($page);exit();
        $meterId = input('param.meterId');
        $where = array('meterId'=>$meterId);
        $find = $this->meterTypes()->findById($where);
        if(!$find){
            return $this->error(Lang::get('unfind metertype'));
        }
        $meterTypes = input('post.');
        //空格处理
        $meterTypes['pid'] = trim($meterTypes['pid']);
        $meterTypes['meterType'] = trim($meterTypes['meterType']);
        $pid = $meterTypes['pid'];
        //先判断是否是主类
        if($pid == '主类'){
            $meterTypes['pid'] = 0;
        }else{
            //先查找数据库中是否存在
            $find = $this->meterTypes()->findById(array('meterType'=>$pid));
            //var_dump($find);exit();
            if(!$find){
                $parentOne['meterType'] = $pid;
                $parentOne['pid'] = 0;
                $this->meterTypes()->add($parentOne, '');
                $lastId = $this->meterTypes()->getLastId();
                if($lastId){
                    $meterTypes['pid'] = $lastId;
                }else{
                    return $this->error(Lang::get('add fail'));
                }
            }else{
                $meterTypes['pid'] = $find['meterId'];
            }
        }

        $validate = $this->validate($meterTypes, 'Met');
        if(true !== $validate){
            return $this->error(" $validate ");
        }

        $result = $this->meterTypes()->update($meterTypes, $where);
        if($result < 1){
            return $this->error(Lang::get('edit fail'));
        }
        $page = input('param.page');
        $data = '?page='.$page;
        $url = url('MeterType/index').$data;
        return $this->success(Lang::get('edit success'), $url);
    }

    /**
     * 跳转到删除页
     * @return mixed
     */
    public function dMT(){
        $auth = $this->auth('MeterType', 'dMT');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        return $this->fetch("met/del");
    }

    /**
     * 删除action
     */
    public function delMT(){
        $auth = $this->auth('MeterType', 'dMT');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $meterId = input('param.meterId');
        //var_dump($meterId);exit();
        $where = array('meterId'=>$meterId);
        $find = $this->meterTypes()->findById($where);
        if(!$find){
            return $this->error(Lang::get('unfind metertype'));
        }
        $result = $this->meterTypes()->del($where);
        if($result < 1){
            return $this->error(Lang::get('del fail'));
        }
        return $this->success(Lang::get('del success'), 'MeterType/index');
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

    /**
     * 获取所有主类
     * @return false|\PDOStatement|string|\think\Collection
     */
    private function getAllPid(){
        $field = 'meterId,meterType,pid';
        $where = array('pid'=>0);
        $allPid = $this->meterTypes()->select($field, $where);
        return $allPid;
    }

    /**
     * 获取每一个订单的主类名
     * @param $datas
     */
    private function dealMeterType($datas){
        $len = count($datas);
        if($len > 0){
            for($i=0;$i<$len;$i++){
                $datas[$i] = $this->dealOne($datas[$i]);
            }
        }
        return $datas;

    }

    /**
     * 获取一个订单的主类名
     * @param $data
     * @return mixed
     */
    private function dealOne($data){
        $len = count($data);
        if($len > 0){
            $pid = $data['pid'];
            if($pid == 0){
                $data['parentType'] = '主类';
            }else{
                $find = $this->meterTypes()->findById(array('meterId'=>$pid));
                $data['parentType'] = $find['meterType'];
            }
        }else{
            $data['parentType'] = '';
        }
        return $data;
    }
}