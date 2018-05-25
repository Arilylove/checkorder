<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/8
 * Time: 14:07
 */
namespace app\order\controller;

use think\Lang;

class Client extends Base{

    /*
     * 客户列表 search($tableName1, $tableName2, $param, $html, $field, $destination)
     * */
    public function listClient(){
        return $this->fetch("cli/clients");
    }
    public function index(){
        $this->authVerify();
        $where = '';
        $field = 'cid,state,client';
        $joinTable = 'state';
        $param = 'sid';
        $num = 10;
        $count = $this->clients()->count($where);
        $data = $this->clients()->selectPage($joinTable, $param, $field, $where, $num, $count);
        //分页
        $this->page($data);
        $this->assign('client', $data);
        return $this->fetch('cli/clients');
    }

    /**
     * 跳转到添加客户页面
     * @return mixed
     */
    public function aCli(){
        $auth = $this->auth('Client', 'aCli');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        //需要从国家列表中获取国家名称
        $states = $this->state()->select('sid,state', '');
        $this->assign('states', $states);
        return $this->fetch('cli/add');
    }
    /*
     * 增加客户
     * */
    public function addClient(){
        $auth = $this->auth('Client', 'aCli');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $cid = input("param.cid");
        $state = input('param.state');
        $client = input('param.client');
        $sid = $this->state()->select('sid,state', array('state'=>$state));
        $clients = array(
            'cid'=>$cid,
            'client'=>$client,
            'sid'=>$sid['0']['sid']
        );
        $validate = $this->validate($clients, 'Clients');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        //var_dump($clients);exit();
        $result = $this->clients()->add($clients);
        if (!$result){
            return $this->error(Lang::get('add fail'));
        }
        return $this->success(Lang::get('add success'), 'Client/index');
    }
    /*
     * 删除客户
     * */
    public function delClient(){
        $auth = $this->auth('Client', 'delClient');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $cliId = input('param.cid');
        //var_dump($cliId);exit();
        $where = array('cid'=>$cliId);
        $findCliId = $this->clients()->findById($where);
        if (!$findCliId){
            return $this->error(Lang::get('unfind client'), 'Client/index');
        }
        $delete = $this->clients()->del($where);
        if (!$delete){
            return $this->error(Lang::get('del fail'), 'Client/index');
        }
        return $this->success(Lang::get('del success'), 'Client/index');

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
        $auth = $this->auth('Client', 'eCli');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
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
        return $this->fetch('cli/update');
    }
    /*
     * 编辑客户信息
     * */
    public function editClient(){
        $auth = $this->auth('Client', 'eCli');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $cliId = input('param.cid');
        //var_dump($cliId);exit();
        $where = array('cid'=>$cliId);
        $findCliId = $this->clients()->findById($where);
        if (!$findCliId){
            return $this->error(Lang::get('unfind client'));
        }
        $state = input('param.state');
        $client = input('param.client');

        $sid = $this->state()->select('sid,state', array('state'=>$state));
        $clients = array(
            'cid'=>$cliId,
            'client'=>$client,
            'sid'=>$sid['0']['sid']
        );
        $validate = $this->validate($clients, 'Clients');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $result = $this->clients()->update($clients, $where);
        if (!$result){
            return $this->error(Lang::get('edit fail'));
        }
        return $this->success(Lang::get('edit success'), 'Client/index');
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
        return $this->fetch('cli/clients');
    }

}