<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/8
 * Time: 14:07
 */
namespace app\receipt\controller;

use think\Lang;

class Client extends Base{

    /*
     * 客户列表 search($tableName1, $tableName2, $param, $html, $field, $destination)
     * */
    public function index(){
        $where = '';
        $field = 'cid,sid,client,address,contacts,phone,email,create_time';
        $num = 10;
        $count = $this->clients()->count($where);
        $data = $this->clients()->selectPage($field, $where, $num, $count);
        $data = $this->resetClients($data);
        //分页
        $this->page($data);
        $this->assign('clients', $data);
        return $this->fetch('cli/clients');
    }

    /**
     * 跳转到添加客户页面
     * @return mixed
     */
    public function add(){
        //需要从国家列表中获取国家名称
        $this->assignState();
        return $this->fetch('cli/add');
    }
    /*
     * 增加客户
     * */
    public function save(){
        $clients = input('post.');
        //var_dump($clients);exit();
        //基本验证
        $validate = $this->validate($clients, 'Clients');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $clients['create_time'] = date('Y-m-d H:i:s', time());
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
    public function del(){
        $cid = input('param.cid');
        //var_dump($cliId);exit();
        $where = array('cid'=>$cid);
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
        $cid = input('param.cid');
        $field = 'cid,sid,client,address,contacts,phone,email,create_time';
        $where = array('cid'=>$cid);
        $data = $this->clients()->select($field, $where);
        //var_dump($data);
        echo json_encode($data);
    }
    /**
     * 跳转到客户信息编辑页面
     * @return mixed
     */
    public function edit(){
        //需要从国家列表中获取国家名称
        $this->assignState();
        $field = 'cid,sid,client,address,contacts,phone,email,create_time';
        $cid = input('param.cid');
        $where = array('cid'=>$cid);
        $client = $this->clients()->findById($where);
        $client = $this->resetClient($client);
        //var_dump($client);exit();
        $this->assign('client', $client);
        return $this->fetch('cli/update');
    }
    /*
     * 编辑客户信息
     * */
    public function esave(){
        $cid = input('param.cid');
        //var_dump($cliId);exit();
        $where = array('cid'=>$cid);
        $findCliId = $this->clients()->findById($where);
        if (!$findCliId){
            return $this->error(Lang::get('unfind client'));
        }
        $clients = input('post.');
        //是否同其他名称重复
        $client = $clients['client'];
        $this->verifyForEdit($client, $cid);
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
        $joinTable = 'states';
        $param = 'sid';
        $data = $this->clients()->searchLike($search, $joinTable, $param);
        $this->page($data);
        $this->assign('client', $data);
        return $this->fetch('cli/clients');
    }


    /**
     * 修改客户名唯一性验证
     * @param $client
     */
    private function verifyForEdit($client, $cid){
        $field = 'cid,sid,client,address,contacts,phone,email,create_time';
        $where = array('client'=>$client,'cid'=>['<>',$cid]);
        $find = $this->clients()->findById($where);
        if($find){
            return $this->error(Lang::get('existed client'));
        }
    }
    /**
     * 重组客户（二维数组）
     * @param $clients
     */
    private function resetClients($clients){
        $len = count($clients);
        if($len >= 1){
            for($i=0;$i<$len;$i++){
                $clients[$i] = $this->resetClient($clients[$i]);
            }
        }
        return $clients;
    }
    /**
     * 重组客户（一维数组）
     * @param $client
     */
    private function resetClient($client){
        $len = count($client);
        if($len >= 1){
            $sid = $client['sid'];
            $findState = $this->state()->findById(array('sid'=>$sid));
            $client['state'] = $findState['state'];
        }else{
            $client['state'] = '';
        }
        return $client;
    }

}