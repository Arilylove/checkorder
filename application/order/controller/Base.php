<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/7
 * Time: 17:25
 */
namespace app\order\controller;

use app\order\model\Admins;
use app\order\model\Logs;
use app\order\model\Meters;
use app\order\model\MeterTypes;
use app\order\model\ModelTypes;
use app\order\model\Orders;
use app\order\model\ProductPrinciple;
use app\order\model\SaleDepts;
use think\Controller;
use think\Db;
use app\order\crypt\AesCrypt;
use app\order\model\States;
use app\order\model\Clients;
use app\order\model\Manufacturer;
use app\order\model\Roles;
use app\order\model\Ways;
use app\order\model\Depts;
use think\Lang;

class Base extends Controller{
    public function _initialize(){
        $username = session('orderuser');
        $status = session('orderstatus');
        $surname = session('surname');
        $sale_id = session('sale_id');
        $a = is_null($username);
        //var_dump($a);exit();
        //判断用户是否已经登录
        if ($a) {
            return $this->error(Lang::get('login first,thanks'), 'Login/index');
        }
        $this->assign("username", $username);
        $this->assign("surname", $surname);
        $this->assign("status", $status);
        $this->assign("sale_id", $sale_id);
        //登录成功，将用户的权限操作id传给前端
        $this->assignOwnWays($username);
        return true;
    }

    public function authVerify(){
        $controller = ucfirst(request()->controller());
        $action = request()->action();
        $auth = $this->auth($controller, $action);
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
    }
    /**
     * 1表示有权限，0表示无权限
     */
    public function auth($controller, $action){
        $username = session('orderuser');
        $ways = $this->getWaysByUid($username);
        //$controller = request()->controller();
        //$action = request()->action();
        if(in_array($controller, $ways['w_control']) && in_array($action, $ways['w_way'])){
            return 1;
        }
        return 0;
    }

    /*{if condition="in_array('Role',$ownways['w_control']) && in_array('add',$ownways['w_way'])"}*/
    /**
     * 根据UID获取该用户所有权限
     * @param $uid
     * @return array|int
     */
    public function getWaysByUid($username){
        $find = $this->admins()->findById(array('username'=>$username));
        $role_id = $find['role_id'];
        $wid = $this->roles()->findById(array('role_id'=>$role_id));
        //查看是否有权限
        $len = strlen($wid['wid']);
        if($len < 1){
            return null;
        }
        //去除最后一个','
        $wid['wid'] = substr($wid['wid'], 0, strlen($wid['wid'])-1);
        $widArr = explode(',', $wid['wid']);
        $ways = array();
        for($i=0;$i<count($widArr);$i++){
            $wid = $widArr[$i];
            $findWay = $this->ways()->findById(array('wid'=>$wid));
            $ways['w_control'][$i] = $findWay['w_control'];
            $ways['w_way'][$i] = $findWay['w_way'];
        }
        return $ways;
    }
    protected function assignOwnWays($username){
        $ways = $this->getWaysByUid($username);
        //var_dump($ways);exit();
        if($ways == null){
            $ways['w_control'] = array();
            $ways['w_way'] = array();
            $this->assign('ownways', $ways);
            return $this->error(Lang::get('no authority'), 'Login/index');
        }else{
            //var_dump($ways);exit();
            $this->assign('ownways', $ways);
        }

    }


    ///////model类
    protected function admins(){
        $admins = new Admins();
        return $admins;
    }
    protected function hex(){
        $hex = new AesCrypt();
        return $hex;
    }
    protected function clients(){
        $client = new Clients();
        return $client;
    }
    protected function state(){
        $state = new States();
        return $state;
    }
    protected function manus(){
        $manus = new Manufacturer();
        return $manus;
    }
    protected function meterTypes(){
        $meterTypes = new MeterTypes();
        return $meterTypes;
    }
    protected function modelTypes(){
        $modelTypes = new ModelTypes();
        return $modelTypes;
    }
    protected function orders(){
        $orders = new Orders();
        return $orders;
    }
    protected function principles(){
        $principles = new ProductPrinciple();
        return $principles;
    }
    protected function meters(){
        $meters = new Meters();
        return $meters;
    }
    protected function roles(){
        $roles = new Roles();
        return $roles;
    }
    protected function ways(){
        $ways = new Ways();
        return $ways;
    }
    protected function depts(){
        $depts = new Depts();
        return $depts;
    }

    protected function logs(){
        $logs = new Logs();
        return $logs;
    }
    protected function sales(){
        $sales = new SaleDepts();
        return $sales;
    }
    /**
     * 分页
     * @param $table
     */
    public function page($table){
        $page = $table->render();
        $currentPage = $table->currentPage();
        $this->assign('currentPage', $currentPage);
        $this->assign('pageOrder', ($currentPage-1)*10);
        $this->assign('page', $page);
        //默认传参config（for search）
        $this->assign('config', '');
    }


    /*
      *修改密码
      * */
    public function update(){
        $username = session('orderuser');
        $this->assign('username', $username);
        return $this->fetch('lic/upPass');
    }

    public function updatePassword(){
        $username = session('orderuser');
        //$this->assign('username', $username);
        $where = array('username'=>$username);
        $admin = Db::table('admin')->where($where)->find();
        if (!$admin){
            return $this->error(Lang::get('no user exist'));
        }
        //var_dump($admin['password']);exit();
        $this->assign('adId', $admin['adId']);
        $string = new AesCrypt();
        //解密
        $password = $admin['password'];
        //var_dump($password);exit();
        $inputPassword = $string->encrypt(input('param.password'));
        $update = $string->encrypt(input('param.update'));
        $confirm = $string->encrypt(input('param.confirm'));
        if($password != $inputPassword){
            return $this->error(Lang::get('pass wrong'));
        }
        if ($update == $password){
            return $this->error(Lang::get('same to old'));
        }
        if ($update == ''){
            return $this->error(Lang::get('unallowed as null'));
        }
        if ($update != $confirm){
            return $this->error(Lang::get('two not same'));
        }
        $result = Db::table('admin')->where('username', $username)->update(['password'=>$update]);
        //var_dump($result);exit();
        if (!$result){
            return $this->error(Lang::get('edit fail'));
        }
        session('orderuser', null);
        return $this->success(Lang::get('edit success'), 'Login/index');

    }

    /**
     * 客户option
     */
    protected function assignClient(){
        $joinTable = 'state';
        $param = 'sid';
        $where = '';
        $field = 'cid,state,client';
        $client = $this->clients()->joinSelect($joinTable, $param, $where, $field);
        //如果一个国家下面有好几个客户
        $this->assign('clients', $client);
    }

    /**
     * 国家option
     */
    protected function assignState(){
        $field = 'sid,state';
        $state = $this->state()->select($field, '');
        $this->assign('state', $state);
    }
    /**
     * 制造商option
     */
    protected function assignManu(){
        $field = 'mfId,manufacturer';
        $manu = $this->manus()->select($field, '');
        $this->assign('manu', $manu);
    }
    /**
     * 基表型号option
     */
    protected function assignMeterType(){
        $field = 'meterId,meterType';
        $meterType = $this->meterTypes()->select($field, '');
        $this->assign('meterType', $meterType);
    }
    /**
     * 电子模块类型option
     */
    protected function assignModelType(){
        $field = 'modelId,modelType';
        $modelType = $this->modelTypes()->select($field, '');
        $this->assign('modelType', $modelType);
    }
    /**
     * 生产负责人option
     */
    protected function assignPrinc(){
        $field = 'pid,productPrinciple,dept,position';
        $principle = $this->principles()->select($field, '');
        $this->assign('principle', $principle);
    }

    /**
     * 表号option
     */
    protected function assignMeter(){
        $field = 'mid,meterNum,oid';
        $meter = $this->meters()->select($field, '');
        $this->assign('meters', $meter);
    }

    /**
     * 订单
     */
    protected function assignOrder(){
        $field = "oid,state,client,meterType,modelType,modelStart,modelEnd,modelNum,meterStart,meterEnd,assemStart,assemEnd,deliveryTime,orderNum,manufacturer,productPrinciple,deliveryStatus,orderCycle,assemCycle";
        $order = $this->orders()->select($field, '');
        $this->assign('order', $order);
    }

    /**
     * 部门
     */
    protected function assignDept(){
        $field = 'de_id,dept_name,description,create_time';
        $where = '';
        $depts = $this->depts()->select($field, $where);
        $this->assign('depts', $depts);
    }

    /**
     * 角色
     */
    protected function assignRole(){
        $field = 'role_id,role_name,remark,create_time,wid,status';
        $where = '';
        $roles = $this->roles()->select($field, $where);
        $this->assign('roles', $roles);
    }

    /**
     * 所有权限操作
     */
    protected function assignWays(){
        $field = 'wid,w_name,pid,w_control,w_way,url,create_time,status';
        $where = '';
        $ways = $this->ways()->select($field, $where);
        $this->assign('ways', $ways);
    }

    /**
     * 业务部
     */
    protected function assignSaleDept(){
        $field = 'sale_id,sale_name,remark,create_time,status';
        $where = '';
        $data = $this->sales()->select($field, $where);
        $this->assign('saledepts', $data);
    }


    /**
     * 日期计算-》周期(单位：日)
     */
    public function countDate($start, $end){
        //刚开始格式为2018-05-10
        $startDate = strtotime($start);
        $endDate = strtotime($end);
        $days = round(($endDate-$startDate)/3600/24) ;
        return $days;
    }

    /**
     * 验证表号
     */
    protected function verifyNum($startNum, $endNum){
        //1.长度10,,11,12,13;2.长度相等；3.前四位相同;4.都是数字；5.如果是13位，只保留前面12位。
        $startLen = strlen($startNum);
        $endLen = strlen($endNum);
        $start4 = substr($startNum, 0, 4);
        $end4 = substr($endNum, 0, 4);
        $startIsNum = is_numeric($startNum);
        $endIsNum = is_numeric($endNum);
        if(($startLen != $endLen) || ($start4 != $end4) || (!$startIsNum) || (!$endIsNum)){
            return $this->error(Lang::get('number and four the same'));
        }
        $startFloat = floatval($startNum);
        $endFloat = floatval($endNum);
        $intLength = intval($endFloat-$startFloat);
        //var_dump($intLength);exit();
        if($intLength < 1){
            return $this->error(Lang::get('end bigger than start'));
        }

    }
    /**
     * 获取联合表中的值
     * @param $orders
     * @return mixed
     */
    protected function getJoinId($orders){

        for($k=0;$k<count($orders);$k++){
            //国家，客户，基表型号，电子模块类型，制造商，生产负责人
            $orders[$k] = $this->getOneJoinId($orders[$k]);
        }
        return $orders;
    }
    /**
     * 获取一个order联合表中的值
     * @param $order
     * @return mixed
     */
    protected function getOneJoinId($order){
        if(count($order) >= 1){
            $states = $this->state()->findById(array('sid'=>$order['sid']));
            $clients = $this->clients()->findById(array('cid'=>$order['cid']));
            $meterTypes = $this->meterTypes()->findById(array('meterId'=>$order['meterId']));
            $modelTypes = $this->modelTypes()->findById(array('modelId'=>$order['modelId']));
            $manufacturers = $this->manus()->findById(array('mfId'=>$order['mfId']));
            $productPrinciples = $this->principles()->findById(array('pid'=>$order['pid']));
            $saleDepts = $this->sales()->findById(array('sale_id'=>$order['sale_id']));
            $order['state'] = $states['state'];
            $order['client'] = $clients['client'];
            $order['meterType'] = $meterTypes['meterType'];
            $order['modelType'] = $modelTypes['modelType'];
            $order['manufacturer'] = $manufacturers['manufacturer'];
            $order['productPrinciple'] = $productPrinciples['productPrinciple'];
            $order['sale_name'] = $saleDepts['sale_name'];

        }else{
            $order['state'] = "";
            $order['client'] = "";
            $order['meterType'] = "";
            $order['modelType'] = "";
            $order['manufacturer'] = "";
            $order['productPrinciple'] = "";
            $order['sale_name'] = '';
        }
        return $order;
    }

    /*
     * 事务操作；
     * */
    protected function startTrans() {
        Db::startTrans();
    }

    protected function commit() {
        Db::commit();
    }

    protected function rollback() {
        Db::rollback();
    }

}