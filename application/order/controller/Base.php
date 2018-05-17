<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/7
 * Time: 17:25
 */
namespace app\order\controller;

use app\order\model\Meters;
use app\order\model\MeterTypes;
use app\order\model\ModelTypes;
use app\order\model\Orders;
use app\order\model\ProductPrinciple;
use think\Controller;
use think\Db;
use app\order\crypt\AesCrypt;
use app\order\model\States;
use app\order\model\Clients;
use app\order\model\Manufacturer;

class Base extends Controller{
    public function _initialize(){
        $username = session('username');
        $status = session('status');
        $a = is_null($username);
        //var_dump($a);exit();
        //判断用户是否已经登录
        if ($a) {
            return $this->error('对不起,您还没有登录!请先登录', 'Login/index');
        }
        $this->assign("username", $username);
        $this->assign("status", $status);
        return true;
    }
    ///////model类
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
    }


    /*
      *修改密码
      * */
    public function update(){
        $username = session('username');
        $this->assign('username', $username);
        return $this->fetch('lic/upPass');
    }

    public function updatePassword(){
        $username = session('username');
        //$this->assign('username', $username);
        $where = array('username'=>$username);
        $admin = Db::table('admin')->where($where)->find();
        if (!$admin){
            return $this->error('该用户不存在');
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
            return $this->error('密码输入错误');
        }
        if ($update == $password){
            return $this->error('修改密码同原始密码相同');
        }
        if ($update == ''){
            return $this->error('密码不能为空');
        }
        if ($update != $confirm){
            return $this->error('两次输入密码不相同');
        }
        $result = Db::table('admin')->where('username', $username)->update(['password'=>$update]);
        //var_dump($result);exit();
        if (!$result){
            return $this->error('修改失败');
        }
        session('username', null);
        return $this->success('修改成功,返回登录界面', 'Login/index');

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

    protected function assignOrder(){
        $field = "oid,state,client,meterType,modelType,modelStart,modelEnd,modelNum,meterStart,meterEnd,assemStart,assemEnd,deliveryTime,orderNum,manufacturer,productPrinciple,deliveryStatus,orderCycle,assemCycle";
        $order = $this->orders()->select($field, '');
        $this->assign('order', $order);
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
            return $this->error("表号必须是数字,前四位相同且长度相等");
        }
        $startFloat = floatval($startNum);
        $endFloat = floatval($endNum);
        $intLength = intval($endFloat-$startFloat);
        //var_dump($intLength);exit();
        if($intLength < 1){
            return $this->error("结束表号要大于开始表号");
        }

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