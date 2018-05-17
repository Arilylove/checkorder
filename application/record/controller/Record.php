<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/7
 * Time: 17:24
 */
namespace app\record\controller;

use app\record\model\Clients;
use app\record\model\Records;
use app\record\model\States;

class Record extends Base{

    /**
     * json数据
     */
    public function record(){
        $rid = input('param.rid');
        $field = 'rid,cid,sid,recorder,solveCycle,solved,description,pcId,solution,recordDate';
        $where = array('rid'=>$rid);
        $data = $this->records()->select($field, $where);
        //var_dump($data);
        echo json_encode($data);
    }
    /**
     * 记录列表
     * @return mixed
     */
    public function listRecord(){
        $where = array();
        $count = $this->records()->count($where);
        $field = 'rid,cid,sid,recorder,solveCycle,solved,description,pcId,solution,recordDate';
        $dataAll = $this->records()->select($field, '');
        //总记录解决时长
        $summery = 0.0;
        foreach ($dataAll as $value){
            $summery += $value['solveCycle'];
        }
        //var_dump($summery);exit();
        $records = $this->records()->selectPage($count);
        //var_dump($records);exit();
        $len = count($records);
        //分页记录解决时长
        $sum = 0.0;
        if($len >= 1){
            for($i=0;$i<$len;$i++){
                $records[$i] = $this->joinRecord($records[$i]);
                $sum += $records[$i]['solveCycle'];
            }
        }
        //var_dump($records);exit();
        $this->page($records);
        $this->assignClient();
        $this->assignAdmin();
        $this->assignCla();
        $this->assign("record", $records);
        $this->assign('sum', $sum);
        $this->assign('summery', $summery);
        return $this->fetch("record/index");
    }


    /**
     * 跳转到添加记录页
     * @return mixed
     */
    public function aRe(){
        $this->assignClient();
        $this->assignCla();
        return $this->fetch("record/add");
    }
    /**
     * 添加记录
     */
    public function addRecord(){

        $records = input("post.");
        //0表示已解决
        if($records['solved'] != 0){
            $records['solveCycle'] = 0;
        }
        //var_dump($records['solveCycle']);exit();
        if(!is_numeric($records['solveCycle']) || ($records['solved'] == 0 && empty($records['solveCycle']))){
            return $this->error("请输入>0的解决时长");
        }

        $stateClient = $records['state'];
        $array = explode(",", $stateClient);
        $sid= $array['0'];
        $cid = $array['1'];
        //$sidArray = $this->state()->findById(array('state'=>$state));
        //$cidArray = $this->clients()->findById(array('client'=>$client));
        $records['sid'] = $sid;
        $records['cid'] = $cid;
        //session('username', 'test');
        //记录人是当前登录用户
        $records['recorder'] = session('username');
        $records['recordDate'] = date('Y-m-d H:i:s', time());
        unset($records['state']);
        //var_dump($records);exit();
        //信息验证
        $add = $this->records()->add($records);
        if($add < 1){
            return $this->error("添加失败");
        }
        return $this->success("添加成功", "Record/listRecord");


    }

    /**
     * 跳转到修改页
     */
    public function eRe(){
        //可选国家客户
        $this->assignClient();
        $this->assignCla();
        //记录详情
        $rid = input('param.rid');
        $where = array('rid'=>$rid);
        //$field = "rid,recorder,state,client,classify,description,solveCycle,solved";
        $records = $this->records()->findById($where);
        //var_dump($records);exit();
        $records = $this->joinRecord($records);
        //var_dump($records);exit();
        $this->assign('record', $records);
        return $this->fetch("record/update");
    }

    /**
     * 修改记录
     */
    public function editRecord(){
        $rid = input("param.rid");
        $where = array('rid'=>$rid);
        $find = $this->records()->findById($where);
        if(!$find){
            return $this->error("该记录不存在");
        }
        $records = input("post.");
        //var_dump($records);exit();
        $stateClient = $records['state'];
        $array = explode(",", $stateClient);
        $sid= $array['0'];
        $cid = $array['1'];
        //$sidArray = $this->state()->findById(array('state'=>$state));
        //$cidArray = $this->clients()->findById(array('client'=>$client));
        $records['sid'] = $sid;
        $records['cid'] = $cid;
        //0表示已解决
        if($records['solved'] != 0){
            $records['solveCycle'] = 0;
        }
        //var_dump($records['solveCycle']);exit();
        if(!is_numeric($records['solveCycle']) || ($records['solved'] == 0 && $records['solveCycle'] == 0)){
            return $this->error("请输入>0的解决时长");
        }
        //var_dump($records);exit();
        unset($records['state']);
        //var_dump($records);exit();
        //信息验证
        $edit = $this->records()->update($records, '');
        if($edit < 1){
            return $this->error("修改失败");
        }
        return $this->success("修改成功", "Record/listRecord");
    }

    /**
     * 删除记录
     */
    public function delRecord(){
        $rid = input("param.rid");
        //var_dump($rid);exit();
        $where = array('rid'=>$rid);
        $find = $this->records()->findById($where);
        //var_dump($find);exit();
        if(!$find){
            return $this->error("该记录不存在");
        }
        //自己只能删除自己创建的记录
        $username = session('username');
        $recorder = $find['recorder'];
        if($recorder != $username){
            return $this->error("不能删除其他人创建的记录");
        }
        //var_dump($recorder);exit();
        $del = $this->records()->del($where);
        if($del < 1){
            return $this->error("删除失败");
        }
        return $this->success("删除成功", "Record/listRecord");
    }

    /**
     * 查看一条记录
     */
    public function view(){
        //记录详情
        $rid = input('param.rid');
        $where = array('rid'=>$rid);
        $records = $this->records()->findById($where);
        $records = $this->joinRecord($records);
        $this->assign('record', $records);
        return $this->fetch('record/view');
    }
    /**
     * 搜索记录
     */
    public function search(){
        //以条件查询
        $search = input('post.');
        $recorder = $search['recorder'];
        $pcId = $search['pcId'];
        $cid = $search['cid'];
        $solved = $search['solved'];
        //不分页下总的解决时长
        $summery = $this->records()->sumSolveCycle($search);
        $records = $this->records()->searchCondition($search);
        //var_dump($search);exit();
        $len = count($records);
        //分页，每一页的解决时长
        $sum = 0.0;
        if($len >= 1){
            for($i=0;$i<$len;$i++){
                $records[$i] = $this->joinRecord($records[$i]);
                $sum += $records[$i]['solveCycle'];
            }
        }
        //var_dump($records);exit();
        $this->page($records);
        $this->assignClient();
        $this->assignCla();
        $this->assignAdmin();
        $this->assign('record', $records);
        $this->assign('sum', $sum);
        $this->assign('summery', $summery);
        return $this->fetch('record/index');
    }

    /**
     * 由联合表id获取名称
     * @param $records
     * @return mixed
     */
    private function joinRecord($records){
        if(count($records) >= 1){
            $state = $this->state()->findById(array('sid'=>$records['sid']));
            $client = $this->clients()->findById(array('cid'=>$records['cid']));
            $classify = $this->classifies()->findById(array('pcId'=>$records['pcId']));
            $records['state'] = $state['state'];
            $records['client'] = $client['client'];
            $records['classify'] = $classify['classify'];
        }else{
            $records['state'] = '';
            $records['client'] = '';
            $records['classify'] = '';
        }
        return $records;
    }

}