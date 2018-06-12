<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/30
 * Time: 18:26
 */
namespace app\receipt\controller;

use think\Lang;
/**
 * 数据模板
 * Class Datas
 * @package app\receipt\controller
 */
class Datas extends Base{

    public function index(){
        $field = 'dm_id,type,specification,unit,img,price,create_time';
        $where = '';
        $data = $this->datas()->selectPage($field, $where);
        $this->page($data);
        $this->assign("datas", $data);
        return $this->fetch('data/index');
    }

    public function one(){
        $id = input('param.dm_id');
        $where = array('dm_id'=>$id);
        $field = 'dm_id,type,specification,unit,img,create_time,price';
        $data = $this->datas()->select($field, $where);
        echo json_encode($data);
    }
    public function add(){
        return $this->fetch('data/add');
    }

    public function save(){
        $data = input('post.');
        //不要图片
        /*$path = ROOT_PATH.DS.'public'.DS.'datamodel';
        $data['img'] = $this->getImgFile($path);*/
        $data['create_time'] = date('Y-m-d H:i:s', time());
        //基本验证
        $validate = $this->validate($data, 'DataModels');
        if(true !== $validate){
            return $this->error("$validate");
        }

        $result = $this->datas()->add($data, '');
        if(!$result){
            return $this->error(Lang::get('add fail'));
        }
        return $this->success(Lang::get('add success'), 'Datas/index');

    }

    /**
     * 批量添加
     * @return mixed
     */
    public function batchadd(){
        return $this->fetch('data/batchadd');
    }
    public function batchsave(){
        $note = input('post.');
        //var_dump($note);exit();
        $len = count($note['note']);
        $notes = array([]);
        for($i=0;$i<$len;$i++){
            $notes[$i]['note'] = $note['note'][$i];
            $notes[$i]['create_time'] = date('Y-m-d H:i:s', time());
        }
        $batchadd = $this->datas()->insertAll($notes, '');
        if(!$batchadd){
            return $this->error(Lang::get('add fail'));
        }
        return $this->success(Lang::get('add success'), 'Datas/index');
    }
    public function edit(){
        $id = input('param.dm_id');
        $where = array('dm_id'=>$id);
        $find = $this->datas()->findById($where);
        if(!$find){
            return $this->error(Lang::get('unfind note'));
        }
        $this->assign('data', $find);
        return $this->fetch('data/update');
    }

    public function esave(){
        $id = input('param.dm_id');
        $where = array('dm_id'=>$id);
        $data = input('post.');
        $path = ROOT_PATH.DS.'public'.DS.'datamodel';
        $data['img'] = $this->getImgFile($path);
        $edit = $this->datas()->update($data, $where);
        if(!$edit){
            return $this->error(Lang::get('edit fail'));
        }
        return $this->success(Lang::get('edit success'), 'Datas/index');
    }

    public function del(){
        $id = input('param.dm_id');
        //var_dump($id);exit();
        $where = array('dm_id'=>$id);
        $find = $this->datas()->findById($where);
        if(!$find){
            return $this->error(Lang::get('unfind Datas'));
        }
        $del = $this->datas()->del($where);
        if(!$del){
            return $this->error(Lang::get('del fail'));
        }
        return $this->success(Lang::get('del success'), 'Datas/index');
    }

    /**
     * 模糊查询
     * @return mixed
     */
    public function search(){
        $search = input('param.search');
        $field = 'dm_id,type,specification,unit,img,price,create_time';
        $where = array('note'=>['like', "%$search%"]);
        $data = $this->datas()->selectPage($field, $where);
        $this->page($data);
        $this->assign("datas", $data);
        return $this->fetch('data/index');
    }


}