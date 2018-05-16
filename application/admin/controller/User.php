<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/5/8
 * Time: 15:37
 */
namespace app\admin\controller;

use app\admin\controller\Base;
use app\admin\getcode\GetCode;
use app\admin\model\Clients;
use app\admin\model\Codes;
use app\admin\model\States;
use think\Db;
use think\Request;
use app\admin\common\Common;

class User extends Base{
    /*
     * 控制器判断
     * */
    public function isUser(){
        $controll = lcfirst( request()->controller() );//控制器名
        if ($controll != 'User'){
            return false;
        }
        return true;
    }

    /**
     * 使用类
     * */
    public function base(){
        $base = new Base();
        return $base;
    }
    public function clients(){
        $c = new Clients();
        return $c;
    }
    public function codes(){
        $co = new Codes();
        return $co;
    }
    public function state(){
        $st = new States();
        return $st;
    }

    /*
     * 客户列表 search($tableName1, $tableName2, $param, $html, $field, $destination)
     * */
    public function listClient(){
        return $this->fetch("user/clients");
    }

    public function cliLi(){
        $param = 'stId';
        $html = 'client';
        $where = '';
        $field = 'cliId,state,contacts,phone,company,email,app';
        //需要从国家列表中获取国家名称
        $states = $this->state()->select('stId,state', $where);
        $destination = 'user/clients';
        $this->assign('states', $states);
        $tableClient = 'client';
        $tableStates = 'states';
        $order = 'cliId';
        //当前国家$tableName1, $tableName2 = '', $param, $html, $field, $where, $destination
        $search = $this->base()->searchBase($tableClient, $tableStates, $param, $html, $field, $where, $destination, $order);
        //var_dump($search);
        return $search;
    }
    /*
     *查询修改客户的信息
     */
    public function client(){
        $cliId = input('param.cliId');
        $field = 'cliId,contacts,phone,company,email,app';
        $where = array('cliId'=>$cliId);
        $data = $this->clients()->select($field, $where);
        //var_dump($data);
        echo json_encode($data);
    }

    public function aCli(){
        //需要从国家列表中获取国家名称
        $states = $this->state()->select('stId,state', '');
        $this->assign('states', $states);
        return $this->fetch('user/client/add');
    }
    /*
     * 增加客户
     * */
    public function addClient(){
        //随机编号
        $cliId = mt_rand(1,999);
        $cli = $this->clients();
        $cli->setCliId($cliId);
        $client = $this->getClients();
        $client['cliId'] = $cliId;
        unset($client['state']);
        //var_dump($client);exit();
        $where = array('cliId'=>$cliId);
        $findCliId = $cli->findById($where);
        if ($findCliId){
            return $this->error('该客户编号已存在', 'User/cliLi');
        }
        $result = $cli->add($client, '');
        if (!$result){
            return $this->error('添加失败', 'User/cliLi');
        }
        return $this->success('添加成功', 'User/cliLi');
    }
    /*
     * 删除客户
     * */
    public function deleteClient(){
        $cliId = input('param.cliId');
        $where = array('cliId'=>$cliId);
        $findCliId = $this->clients()->findById($where);
        if (!$findCliId){
            return $this->error('未找到该客户', 'User/cliLi');
        }
        $delete = $this->clients()->del($where);
        if (!$delete){
            return $this->error('删除失败', 'User/cliLi');
        }
        return $this->success('删除成功', 'User/cliLi');

    }

    public function eCli(){
        //需要从国家列表中获取国家名称
        $states = $this->state()->select('stId,state', '');
        $this->assign('states', $states);

        $cliId = input('param.cliId');
        $where = array('cliId'=>$cliId);
        $field = 'cliId,state,contacts,phone,company,email,app';
        $tableName2 = 'states';
        $param = 'stId';
        $client = $this->clients()->joinSelect($tableName2, $param, $where, $field);
        //var_dump($client);exit();
        $this->assign('client', $client['0']);
        return $this->fetch('user/client/update');
    }
    /*
     * 客户信息编辑
     * */
    public function editClient(){
        $cliId = input('param.cliId');
        $where = array('cliId'=>$cliId);
        $findCliId = $this->clients()->findById($where);
        if (!$findCliId){
            return $this->error('未找到该客户', 'User/cliLi');
        }
        $client = $this->getClients();
        unset($client['state']);
        $client['cliId'] = $cliId;
        $result = $this->clients()->update($client, $where);
        if (!$result){
            return $this->error('修改失败，您未做任何修改', 'User/cliLi');
        }
        return $this->success('修改成功', 'User/cliLi');
    }

    /**
     * @return mixed
     * 全部注册码
     */
    public function listCode(){
        return $this->fetch('user/codes');
    }
    public function coLi1(){
        //获取不重复的公司名
        $client = $this->clients()->selectDistinct('company,cliId', '');
        $this->assign('client', $client);
        $where = '';
        $field = 'coId,company,mac,creator,createTime,expireTime,code,other';
        $tableName2 = 'client';
        $param = 'cliId';
        $num = 10;
        $count = $this->codes()->count($where);
        $data = $this->codes()->selectPage($tableName2, $param, $field, $where, $num, $count);
        $this->base()->page($data);
        $this->assign('code', $data);
        return $this->fetch('user/codes');
    }
    public function coLi(){
        $where = '';
        $param = 'cliId';
        $html = 'code';
        $field = 'coId,company,mac,creator,createTime,expireTime,code,other';
        $destination = 'user/codes';
        $tableClient = 'client';
        $tableCode = 'code';
        //获取不重复的公司名
        $client = $this->clients()->selectDistinct('company,cliId', $where);
        $this->assign('client', $client);
        $this->assign('condition', 'all');
        $order = 'createTime desc';
        $search = $this->base()->searchBase($tableCode, $tableClient, $param, $html, $field, $where, $destination, $order);
        return $search;
    }
    /*
     * 查询即将失效注册码,失效日期在当前日期30以内
     * */
    public function soCodes(){
        return $this->fetch("user/soCodes");
    }
    public function soLi1(){
        //获取不重复的公司名
        $client = $this->clients()->selectDistinct('company,cliId', '');
        $this->assign('client', $client);
        //只显示即将失效的注册码，已经失效的则不显示
        $soonTime = date('Ymd', strtotime('+1 month'));
        $expire = date('Ymd', strtotime('-1 minute'));
        //条件很重要！！！！！！！！！！！！！！！！！！！！！！！
        $where = array('expireTime'=>['between', [$expire, $soonTime]]);
        $field = 'coId,company,mac,creator,createTime,expireTime,code,other';
        $tableName2 = 'client';
        $param = 'cliId';
        $num = 10;
        $count = $this->codes()->count($where);
        $data = $this->codes()->selectPage($tableName2, $param, $field, $where, $num, $count);
        $this->base()->page($data);
        $this->assign('code',$data);
        return $this->fetch('user/soCodes');
    }
    public function soLi(){
        //只显示即将失效的注册码，已经失效的则不显示
        $soonTime = date('Ymd', strtotime('+1 month'));
        $expire = date('Ymd', strtotime('-1 minute'));
        //var_dump($expire);exit();
        $destination = 'user/soCodes';
        $param = 'cliId';
        $html = 'code';
        $tableClient = 'client';
        $tableCode = 'code';
        //条件很重要！！！！！！！！！！！！！！！！！！！！！！！
        $where = array('expireTime'=>['between', [$expire, $soonTime]]);
        $field = 'coId,company,mac,creator,createTime,expireTime,code,other';
        //获取不重复的公司名
        $client = $this->clients()->selectDistinct('company,cliId', '');
        $this->assign('client', $client);
        $this->assign('condition', 'soonExpire');
        $order = 'createTime desc';
        $search = $this->base()->searchBase($tableCode, $tableClient, $param, $html, $field, $where, $destination, $order);
        return $search;
    }

    /** 
     * 已经失效的注册码
     * @return mixed
     */
    public function exCodes(){
        return $this->fetch("user/exCodes");
    }
    public function exLi1()
    {
        //获取不重复的公司名
        $client = $this->clients()->selectDistinct('company,cliId', '');
        $this->assign('client', $client);

        //只显示即将失效的注册码，已经失效的则不显示
        $now = date('Ymd', time());
        //条件很重要！！！！！！！！！！！！！！！！！！！！！！！
        $where = array('expireTime'=>['between', ['0', $now]]);
        $field = 'coId,company,mac,creator,createTime,expireTime,code,other';
        $tableName2 = 'client';
        $param = 'cliId';
        $num = 10;
        $count = $this->codes()->count($where);
        $data = $this->codes()->selectPage($tableName2, $param, $field, $where, $num, $count);
        $this->base()->page($data);
        $this->assign('code', $data);
        return $this->fetch('user/exCodes');
    }
    public function exLi()
    {
        //只显示即将失效的注册码，已经失效的则不显示
        $now = date('Ymd', time());
        $destination = 'user/exCodes';
        $param = 'cliId';
        $html = 'code';
        $tableClient = 'client';
        $tableCode = 'code';
        //条件很重要！！！！！！！！！！！！！！！！！！！！！！！
        $where = array('expireTime'=>['between', ['0', $now]]);
        $field = 'coId,company,mac,creator,createTime,expireTime,code,other';
        //获取不重复的公司名
        $client = $this->clients()->selectDistinct('company,cliId', '');
        $this->assign('client', $client);
        $this->assign('condition', 'expire');
        $order = 'createTime desc';
        $search = $this->base()->searchBase($tableCode, $tableClient, $param, $html, $field, $where, $destination, $order);
        return $search;
    }
    /*
     * 申请注册码
     * */
    public function aCode(){
        $client = $this->clients()->selectDistinct('company', '');
        //var_dump($client);exit();
        $this->assign('client', $client);

        return $this->fetch('user/code/add');
    }
    public function addCode(){
        $nowTime = time();
        $coId = mt_rand(0,9999);
        $co = $this->codes();
        $code = $this->getCode();
        $code['coId'] = $coId;
        unset($code['company']);
        /**
         * 请求注册码接口
         */
        $operatetype = 'generatelapisregkey';
        $operatoruid = 'hhj';
        $operatorpwd = '4BCBBBC00B0341B4B413CFFC9EB02208';
        $companycode = '0';
        $mac = $code['mac'];
        $expirydate = $code['expireTime'];
        $requestid = $code['coId'];
        /*
         * operatetype=generatelapisregkey&operatoruid=hhj&operatorpwd=4BCBBBC00B0341B4B413CFFC9EB02208&companycode=0&mac=123456789562&expirydate=20170630&requestid=12354
         * */
        $getcode = new GetCode();
        $response = $getcode->getCode($operatetype, $operatoruid, $operatorpwd, $companycode, $mac, $expirydate, $requestid);
        set_time_limit(20);
        if(empty($response)){
            return $this->error('未连接，注册码申请失败');
        }
        $regstring = substr($response, strrpos($response, '&') + 1);
        $code['code'] = substr($regstring, strrpos($regstring, '=') + 1);
        //var_dump($code);exit();
        if (strlen($code['code']) == 35){
            $result = $co->add($code, '');
            if (!$result){
                return $this->error('申请失败，请重新申请');
            }
            session('coId', $requestid);
            session('cliId',$code['cliId']);
            return $this->success('申请成功', 'User/code');
        }
        return $this->error('未连接，注册码申请失败');

    }
    /*
   *查询申请注册码的信息
   */
    public function code(){
        $coId = session('coId');
        $tableClient = 'client';
        //$tableCode = 'code';
        $param = 'cliId';
        $field = 'coId,company,mac,creator,expireTime,createTime,code,other';
        $where = array('coId'=>$coId);
        $code = $this->codes()->joinSelect($tableClient, $param, $where, $field);
        //var_dump($code);exit();
        $this->assign('code', $code['0']);
        return $this->fetch('user/code/code');
        session('coId', null);
        session('cliId',null);
    }
    /*
      *查询更新注册码的信息
      */
    public function getUpdateCode(){
        $coId = session('coId');
        $tableClient = 'client';
        //$tableCode = 'code';
        $param = 'cliId';
        $field = 'coId,company,mac,creator,expireTime,createTime,code,other';
        $where = array('coId'=>$coId);
        $code = $this->codes()->joinSelect($tableClient, $param, $where, $field);
        $this->assign('code', $code);
        $this->assign('condition', 'soonExpire');
        return $this->fetch('user/code/code');
        session('coId', null);
        session('cliId',null);
    }
    public function eCo(){
        $client = $this->clients()->selectDistinct('company', '');
        //var_dump($client);exit();
        $this->assign('client', $client);
        $coId = input('param.coId');
        $field = 'coId,company,mac,creator,createTime,expireTime,code,other';
        $tableName2 = 'client';
        $param = 'cliId';
        $where = array('coId'=>$coId);
        $code = $this->codes()->joinSelect($tableName2, $param, $where, $field);
        $y = substr($code['0']['expireTime'],0,4);
        $m = substr($code['0']['expireTime'],4,2);
        $d = substr($code['0']['expireTime'],6,2);
        $code['0']['expireTime'] = $y."-".$m."-".$d;
        $this->assign('code', $code['0']);
        return $this->fetch('user/code/update');
    }
    //for upadte
    public function getCodes(){
        $coId = input("post.coId");
        $tableClient = 'client';
        //$tableCode = 'code';
        $param = 'cliId';
        $field = 'coId,company,mac,creator,expireTime,createTime,code,other';
        $where = array('coId'=>$coId);
        $code = $this->codes()->joinSelect($tableClient, $param, $where, $field);
        //var_dump($codes);
        echo json_encode($code);
    }
    //改变更新之前的注册码
    public function updateOld($oldCode){
        $oldWhere = array('code'=>$oldCode['code']);
        $oldCode['other'] = $oldCode['other'].",已更新!";
        $this->codes()->updateExecute($oldCode, $oldWhere);
    }

    /**
     * 更新注册码
     */
    public function updateCode(){
        //更新前的注册码
        $coId = input("param.coId");
        $oldCode = $this->codes()->findById(array('coId'=>$coId));
        //var_dump($oldCode);exit();
        $coId = mt_rand(0,9999);
        $co = $this->codes();
        $code = $this->getCode();
        $code['coId'] = $coId;
        $code['other'] .= ",更新于:".$oldCode['coId'];
        unset($code['company']);
        /**
         * 请求注册码接口
         */
        $operatetype = 'generatelapisregkey';
        $operatoruid = 'hhj';
        $operatorpwd = '4BCBBBC00B0341B4B413CFFC9EB02208';
        $companycode = '0';
        $mac = $code['mac'];
        $expirydate = $code['expireTime'];
        $requestid = $code['coId'];
        /*
         * operatetype=generatelapisregkey&operatoruid=hhj&operatorpwd=4BCBBBC00B0341B4B413CFFC9EB02208&companycode=0&mac=123456789562&expirydate=20170630&requestid=12354
         * */
        $getcode = new GetCode();
        $response = $getcode->getCode($operatetype, $operatoruid, $operatorpwd, $companycode, $mac, $expirydate, $requestid);
        set_time_limit(20);
        if(empty($response)){
            return $this->error('未连接，更新注册码失败', 'User/code');
        }
        $regstring = substr($response, strrpos($response, '&') + 1);
        $code['code'] = substr($regstring, strrpos($regstring, '=') + 1);
        //var_dump($code);exit();
        if (strlen($code['code']) == 35){
            $result = $co->add($code, '');
            if (!$result){
                return $this->error('更新失败，请重新更新', 'User/code');
            }
            $this->updateOld($oldCode);  //更新旧验证码
            session('coId', $requestid);
            session('cliId',$code['cliId']);

            return $this->success('更新成功', 'User/code');
        }
        return $this->error('未连接，注册码更新失败', 'User/code');

    }
    public function aSt(){
        return $this->fetch('user/state/add');
    }
    /*
     * 添加国家
     * */
    public function addStates(){
        $st = $this->state();
        $states =input('post.');
        if (empty($states['stId']) || empty($states['state'])){
            return $this->error('信息不能为空', 'User/listS');
        }
        $stId = $this->state()->findById(array('stId'=>$states['stId']));
        $state = $this->state()->findById(array('state'=>$states['state']));
        if ($stId || $state){
            return $this->error('该国家信息已存在', 'User/stLi');
        }
        $result = $st->add($states, '');
        if (!$result){
            return $this->error('添加失败', 'User/stLi');
        }
        return $this->success('添加成功', 'User/stLi');
    }
    /*
     * 国家列表
     * */
    public function listS(){
        return $this->fetch('user/states');
    }
    public function stLi1(){
        $where = '';
        $field = 'stId,state';
        $num = 10;
        $count = $this->state()->count($where);
        $data = $this->state()->selectPage($field, $where, $num, $count);
        $this->base()->page($data);
        $this->assign('states', $data);
        return $this->fetch('user/states');
    }
    public function stLi()
    {
        $param = null;
        $where = '';
        $html = 'states';
        $field = 'stId,state';
        $destination = 'user/states';
        $tableStates = 'states';
        $order = 'stId';
        $search = $this->base()->searchBase($tableStates, '', $param, $html, $field, $where, $destination, $order);
        return $search;
    }

    public function eSt(){
        $stId = input('stId/s');
        $field = 'stId,state';
        $where = array('stId'=>$stId);
        $state = $this->state()->select($field, $where);
        $this->assign('state', $state['0']);
        return $this->fetch('user/state/update');
    }
    /*
     *查询修改国家的信息
     */
    public function states(){
        $stId = input('stId/s');
        $field = 'stId,state';
        $where = array('stId'=>$stId);
        $data = $this->state()->select($field, $where);
        echo json_encode($data);
    }
    /*
     * 国家信息修改
     * */
    public function editStates(){
        $stId = input('stId/s');
        $where = array('stId'=>$stId);
        $find = $this->state()->findById($where);
        if (!$find){
            return $this->error('未找到该国家信息', 'User/stLi');
        }
        $state = input('param.state');
        $states = array(
            'stId'=> $stId,
            'state'=> $state
        );
        $result = $this->state()->update($states, $where);
        if (!$result){
            return $this->error('修改失败', 'User/stLi');
        }
        return $this->success('修改成功', 'User/stLi');
    }
    /*
     * 删除国家
     * */
    public function deleteStates(){
        $stId = input('param.stId');
        $result = $this->delSta($stId);
        return $result;
    }
    public function delSta($stId){
        $where = array('stId'=>$stId);
        $find = $this->state()->findById($where);
        if (!$find){
            return $this->error('未找到该国家', 'User/stLi');
        }
        $delete = $this->state()->del($where);
        if (!$delete){
            return $this->error('删除失败', 'User/stLi');
        }
        return $this->success('删除成功', 'User/stLi');
    }

    public function getClients(){
        //以国家名称获取国家编号
        $state = input('param.state');
        $where = array('state'=>$state);
        $stId = $this->state()->findById($where);
        $client = input('post.');
        $client['stId'] = $stId['stId'];
        return $client;
    }

    public function getCode(){
        $expire1 = input('param.expireTime');
        $expire2 = explode('-', $expire1);
        $expireTime = join('', $expire2);
        $creator = session('username');   //创建者即为用户
        $code = input('post.');
        $code['expireTime'] = $expireTime;
        $code['creator'] = $creator;
        $company = input('param.company');   //以公司名获取客户公司编号
        $where = array('company'=>$company);
        $cliId = $this->clients()->findById($where);
        $code['cliId'] = $cliId['cliId'];
        return $code;
    }
    /**
     * @return mixed
     * 模糊查询
     */
    public function searchCode(){
        //获取不重复的公司名
        $client = $this->clients()->selectDistinct('company,cliId', '');
        $this->assign('client', $client);

        $search = input('param.search');
        $sel = input('param.sel');
        $field = 'coId,company,mac,creator,createTime,expireTime,code,other';
        $tableName2 = 'client';
        $param = 'cliId';
        //$where = '';
        $condition = input('param.condition');
        if($condition == 'tab'){
            $where = array($sel=>['like', "%$search%"]);
        }else if($condition == 'soTab'){
            //只显示即将失效的注册码，已经失效的则不显示
            $soonTime = date('Ymd', strtotime('+1 month'));
            $expire = date('Ymd', strtotime('-1 minute'));
            //条件很重要！！！！！！！！！！！！！！！！！！！！！！！
            $where = array('expireTime'=>['between', [$expire, $soonTime]], $sel=>['like', "%$search%"]);
        }else{
            //只显示即将失效的注册码，已经失效的则不显示
            $now = date('Ymd', time());
            //条件很重要！！！！！！！！！！！！！！！！！！！！！！！
            $where = array('expireTime'=>['between', ['0', $now]], $sel=>['like', "%$search%"]);
        }
        //$where = array("$sel"=>$search);
        $code = $this->codes()->joinSelect($tableName2, $param, $where, $field);
        $codes = $this->array_unique_code($code);
        //var_dump($codes);exit();
        $this->assign('code', json_encode($codes));
        if($condition == 'tab'){
            return $this->fetch("user/code/tab");
        }else if($condition == 'soTab'){
            return $this->fetch("user/code/soTab");
        }
        return $this->fetch("user/code/exTab");

        //return $this->fetch("user/code/tab");
    }
    /**
     * @return mixed
     * 模糊查询
     */
    public function searchState(){
        $search = input('param.search');
        //$sel = input('param.sel');
        $field = 'stId,state';
        $all = $this->state()->select($field, '');
        $states1 = array();
        for($i = 0;$i<count($all);$i++){
            foreach ($all[$i] as $k=>$v){
                $where1 = array(
                    $k => ['like', "%$search%"]
                );
                $temp = $this->state()->select($field, $where1);
                if(count($temp) != 0){
                    $states1 = array_merge($states1, $temp);
                }
            }

        }
        //$where = '';
        //$where = array("$sel"=>$search);
        //var_dump($where);exit();
        //$states = $this->state()->select($field, $where);
        $states = $this->array_unique_st($states1);
        $this->assign('states', json_encode($states));
        return $this->fetch("user/state/table");
    }

    /**
     * @return mixed
     * 模糊查询
     */
    public function searchClient(){
        $search = input('param.search');
        $sel = input('param.sel');
        $field = 'cliId,state,contacts,phone,company,email,app';
        $tableName2 = 'states';
        $param = 'stId';
        $all = $this->clients()->joinSelect($tableName2, $param, '', $field);
        //var_dump($all);exit();
        $clients1 = array();
        for($i = 0;$i<count($all);$i++){
            foreach ($all[$i] as $k=>$v){
                $where1 = array(
                    $k => ['like', "%$search%"]
                );
                $temp = $this->clients()->joinSelect($tableName2, $param, $where1, $field);
                if(count($temp) != 0){
                    $clients1 = array_merge($clients1, $temp);
                }
            }

        }
        //var_dump($clients1);exit();
        /*$where = array("$sel"=>$search);
        $client = $this->clients()->joinSelect($tableName2, $param, $where, $field);*/
        $client = $this->array_unique_cli($clients1);
        $this->assign('client', json_encode($client));
        //需要从国家列表中获取国家名称
        $states = $this->state()->select('stId,state', '');
        $this->assign('states', $states);
        return $this->fetch("user/client/table");
    }

    //二维数组去掉重复值,并保留键值
    function array_unique_st($array2D){
        $temp = array();
        $temp2 = array();
        foreach ($array2D as $k=>$v){
            $v=join(',',$v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
            $temp[$k]=$v;
        }
        $temp=array_unique($temp); //去掉重复的字符串,也就是重复的一维数组
        foreach ($temp as $k => $v){
            $array=explode(',',$v); //再将拆开的数组重新组装
            //下面的索引根据自己的情况进行修改即可
            $temp2[$k]['stId'] =$array[0];
            $temp2[$k]['state'] =$array[1];
        }
        return $temp2;
    }
    //二维数组去掉重复值,并保留键值
    function array_unique_cli($array2D){
        $temp = array();
        $temp2 = array();
        foreach ($array2D as $k=>$v){
            $v=join(',',$v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
            $temp[$k]=$v;
        }
        $temp=array_unique($temp); //去掉重复的字符串,也就是重复的一维数组
        foreach ($temp as $k => $v){
            $array=explode(',',$v); //再将拆开的数组重新组装
            //下面的索引根据自己的情况进行修改即可

            $temp2[$k]['cliId'] =$array[0];
            $temp2[$k]['state'] =$array[1];
            $temp2[$k]['contacts'] =$array[2];
            $temp2[$k]['phone'] =$array[3];
            $temp2[$k]['company'] =$array[4];
            $temp2[$k]['email'] =$array[5];
            $temp2[$k]['app'] =$array[6];
        }
        return $temp2;
    }
    //二维数组去掉重复值,并保留键值
    function array_unique_code($array2D){
        $temp = array();
        $temp2 = array();
        foreach ($array2D as $k=>$v){
            $v=join(',',$v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
            $temp[$k]=$v;
        }
        $temp=array_unique($temp); //去掉重复的字符串,也就是重复的一维数组
        foreach ($temp as $k => $v){
            $array=explode(',',$v); //再将拆开的数组重新组装
            //下面的索引根据自己的情况进行修改即可

            $temp2[$k]['coId'] =$array[0];
            $temp2[$k]['company'] =$array[1];
            $temp2[$k]['mac'] =$array[2];
            $temp2[$k]['creator'] =$array[3];
            $temp2[$k]['createTime'] =$array[4];
            $temp2[$k]['expireTime'] =$array[5];
            $temp2[$k]['code'] =$array[6];
            $temp2[$k]['other'] =$array[7];
        }
        return $temp2;
    }
}