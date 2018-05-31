<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/30
 * Time: 18:09
 */
namespace app\receipt\controller;
use think\Lang;

/**
 * 注意事项
 * Class Note
 * @package app\receipt\controller
 */
class Note extends Base{

    public function index(){
        $field = 'nid,note,create_time';
        $where = '';
        $data = $this->notes()->selectPage($field, $where);
        $this->page($data);
        $this->assign("notes", $data);
        return $this->fetch('note/index');
    }

    public function one(){
        $id = input('param.nid');
        $where = array('nid'=>$id);
        $field = 'nid,note,create_time';
        $data = $this->notes()->select($field, $where);
        echo json_encode($data);
    }
    public function add(){
        return $this->fetch('note/add');
    }

    public function save(){
        $data = input('post.');
        $data['create_time'] = date('Y-m-d H:i:s', time());
        $result = $this->notes()->add($data, '');
        if(!$result){
            return $this->error(Lang::get('add fail'));
        }
        return $this->success(Lang::get('add success'), 'Note/index');

    }

    /**
     * 批量添加
     * @return mixed
     */
    public function batchadd(){
        return $this->fetch('note/batchadd');
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
        $batchadd = $this->notes()->insertAll($notes, '');
        if(!$batchadd){
            return $this->error(Lang::get('add fail'));
        }
        return $this->success(Lang::get('add success'), 'Note/index');
    }
    public function edit(){
        $id = input('param.nid');
        $where = array('nid'=>$id);
        $find = $this->notes()->findById($where);
        if(!$find){
            return $this->error(Lang::get('unfind note'));
        }
        $this->assign('note', $find);
        return $this->fetch('note/update');
    }

    public function esave(){
        $id = input('param.nid');
        $where = array('nid'=>$id);
        $data = input('post.');
        $edit = $this->notes()->update($data, $where);
        if(!$edit){
            return $this->error(Lang::get('edit fail'));
        }
        return $this->success(Lang::get('edit success'), 'Note/index');
    }

    public function del(){
        $id = input('param.nid');
        //var_dump($id);exit();
        $where = array('nid'=>$id);
        $find = $this->notes()->findById($where);
        if(!$find){
            return $this->error(Lang::get('unfind note'));
        }
        $del = $this->notes()->del($where);
        if(!$del){
            return $this->error(Lang::get('del fail'));
        }
        return $this->success(Lang::get('del success'), 'Note/index');
    }

    /**
     * 模糊查询
     * @return mixed
     */
    public function search(){
        $search = input('param.search');
        $field = 'nid,note,create_time';
        $where = array('note'=>['like', "%$search%"]);
        $data = $this->notes()->selectPage($field, $where);
        $this->page($data);
        $this->assign("notes", $data);
        return $this->fetch('note/index');
    }




}