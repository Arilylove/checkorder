<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/8
 * Time: 14:07
 */
namespace app\record\controller;

use app\record\model\Clients;
use app\record\model\States;

class Client extends Base{


    /*
     * 客户列表 search($tableName1, $tableName2, $param, $html, $field, $destination)
     * */
    public function listClient(){
        return $this->fetch("client/clients");
    }
    public function cliLi(){
        $where = '';
        $field = 'cid,state,client';
        $joinTable = 'state';
        $param = 'sid';
        $num = 10;
        $count = $this->clients()->count($where);
        $data = $this->clients()->selectPage($joinTable, $param, $where, $num, $count);
        //分页
        $this->page($data);
        $this->assign('client', $data);
        return $this->fetch('client/clients');
    }

    /**
     * 跳转到添加客户页面
     * @return mixed
     */
    public function aCli(){
        //需要从国家列表中获取国家名称
        $states = $this->state()->select('sid,state', '');
        $this->assign('states', $states);
        return $this->fetch('client/add');
    }
    /*
     * 增加客户
     * */
    public function addClient(){
        $cid = input("param.cid");
        $state = input('param.state');
        $client = input('param.client');
        /*$where = array('client'=>$client);
        $find = $this->clients()->findById($where);
        if($find){
            return $this->error('该客户已存在');
        }*/
        $sid = $this->state()->select('sid,state', array('state'=>$state));
        $clients = array(
            'cid'=>$cid,
            'client'=>$client,
            'sid'=>$sid['0']['sid']
        );
        //var_dump($clients);exit();
        //信息验证
        $validate = $this->validate($clients, 'clients');
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $result = $this->clients()->add($clients);
        if (!$result){
            return $this->error('添加失败');
        }
        return $this->success('添加成功', 'Client/cliLi');
    }
    /*
     * 删除客户
     * */
    public function delClient(){
        $cliId = input('param.cid');
        $where = array('cid'=>$cliId);
        $findCliId = $this->clients()->findById($where);
        if (!$findCliId){
            return $this->error('未找到该客户', 'Client/cliLi');
        }
        $delete = $this->clients()->del($where);
        if (!$delete){
            return $this->error('删除失败', 'Client/cliLi');
        }
        return $this->success('删除成功', 'Client/cliLi');

    }

    /*
     *查询修改客户的信息，json数据
     */
    public function client(){
        $cliId = input('param.cid');
        $field = 'cid,sid,client';
        $where = array('cid'=>$cliId);
        $data = $this->clients()->select($field, $where);
        //var_dump($data);
        echo json_encode($data);
    }
    /**
     * 跳转到客户信息编辑页面
     * @return mixed
     */
    public function eCli(){
        //需要从国家列表中获取国家名称
        $states = $this->state()->select('sid,state', '');
        $this->assign('states', $states);

        $cliId = input('param.cid');
        $where = array('cid'=>$cliId);
        $field = 'cid,state,client';
        $tableName2 = 'state';
        $param = 'sid';
        $client = $this->clients()->joinSelect($tableName2, $param, $where, $field);
        //var_dump($client);exit();
        $this->assign('client', $client['0']);
        return $this->fetch('client/update');
    }
    /*
     * 编辑客户信息
     * */
    public function editClient(){
        $cliId = input('param.cid');
        //var_dump($cliId);exit();
        $where = array('cid'=>$cliId);
        $findCliId = $this->clients()->findById($where);
        if (!$findCliId){
            return $this->error('未找到该客户');
        }
        $state = input('param.state');
        $client = input('param.client');
        $sid = $this->state()->select('sid,state', array('state'=>$state));
        $clients = array(
            'cid'=>$cliId,
            'client'=>$client,
            'sid'=>$sid['0']['sid']
        );
        //信息验证
        $validate = $this->validate($clients, 'clients');
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $result = $this->clients()->update($clients, $where);
        if (!$result){
            return $this->error('修改失败');
        }
        return $this->success('修改成功', 'Client/cliLi');
    }


    /**
     * @return mixed
     * 模糊查询
     */
    public function search(){
        $search = input('param.search');
        //var_dump($search);exit();
        $joinTable = 'state';
        $param = 'sid';
        $data = $this->clients()->searchLike($search, $joinTable, $param);
        $this->page($data);
        $this->assign('client', $data);
        return $this->fetch('client/clients');
    }

}