<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 11:25
 */
namespace app\order\controller;

use think\Db;
class Order extends Base{

    public function index(){

        //先从订单表中获取全部信息，mid，在由mid获取表号
        $field = "oid,state,client,meterType,modelType,modelStart,modelEnd,modelNum,meterStart,meterEnd,assemStart,assemEnd,deliveryTime,orderNum,manufacturer,productPrinciple,deliveryStatus,orderCycle,assemCycle";
        $where = '';
        $count = $this->orders()->count($where);
        $orders = $this->orders()->selectPage($where, $count);
        $order = $this->getJoinId($orders);
        //var_dump($sql);
        $this->page($order);

        $this->assignState();
        $this->assignClient();

        //var_dump($order);exit();
        $this->assign('orders', $order);
        return $this->fetch("ord/index");

    }

    /**
     * 跳转到添加页
     * @return mixed
     */
    public function aOrd(){
        //国家，客户，基表型号，电子模块类型，制造商，生产负责人，发货状态都是下拉框选择
        $this->assignState();
        $this->assignClient();
        $this->assignMeterType();
        $this->assignModelType();
        $this->assignManu();
        $this->assignPrinc();
        return $this->fetch("ord/add");
    }

    /**
     * 添加action
     */
    public function addOrd(){
        $orders = input("post.");

        $startLen = strlen($orders['meterStart']);
        $endLen = strlen($orders['meterEnd']);
        //5.如果是13位，只保留前面12位。
        if(($startLen == 13) && ($endLen == 13)){
            $orders['meterStart'] = substr($orders['meterStart'], 0, ($startLen-1));
            $orders['meterEnd'] = substr($orders['meterEnd'], 0, ($endLen-1));
        }
        $start = "1".$orders['meterStart'];
        $end = "1".$orders['meterEnd'];
        //orders验证
        $this->validateOrder($orders);

        //计算周期
        if(!intval($orders['deliveryStatus'])){
            $orders['orderCycle'] = $this->countDate($orders['modelStart'], $orders['modelEnd']);
            $orders['assemCycle'] = $this->countDate($orders['assemStart'], $orders['assemEnd']);
            if(($orders['orderCycle'] < 0) || ($orders['assemCycle'] < 0)){
                return $this->error("结束日期要晚于开始日期");
            }
        }else{
            $orders['orderCycle'] = 0;
            $orders['assemCycle'] = 0;
        }
        //var_dump($orders);exit();

        $addOrder = $this->orders()->add($orders, '');
        if($addOrder < 1){
            return $this->error("添加订单失败");
        }

        $oid = $this->getOid($orders['orderNum']);
        //添加表号
        $this->meterListAdd($start, $end, $oid);
        return $this->success('添加成功', 'Order/index');

    }

    /**
     * 跳转到修改页
     * @return mixed
     */
    public function eOrd(){
        //国家，客户，基表型号，电子模块类型，制造商，生产负责人，发货状态都是下拉框选择
        $this->assignState();
        $this->assignClient();
        $this->assignMeterType();
        $this->assignModelType();
        $this->assignManu();
        $this->assignPrinc();
        $oid = input('param.oid');
        $order = $this->orders()->findById(array('oid'=>$oid));
        //国家，客户，基表型号，电子模块类型，制造商，生产负责人
        $order = $this->getOneJoinId($order);
        $this->assign('order', $order);
        return $this->fetch("ord/update");
    }

    /**
     * 修改action
     */
    public function editOrd(){
        $orders = input("post.");
        //订单号存在
        $oid = $orders['oid'];
        $where = array('oid'=>$oid);
        $find = $this->orders()->findById($where);
        if(!$find){
            return $this->error("订单不存在");
        }
        $startLen = strlen($orders['meterStart']);
        $endLen = strlen($orders['meterEnd']);
        //5.如果是13位，只保留前面12位。
        if(($startLen == 13) && ($endLen == 13)){
            $orders['meterStart'] = substr($orders['meterStart'], 0, ($startLen-1));
            $orders['meterEnd'] = substr($orders['meterEnd'], 0, ($endLen-1));
        }
        //查看表号是否没变,如果有一个变了，去判断是否重复
        if(($find['meterStart'] != $orders['meterStart']) || ($find['meterEnd'] != $orders['meterEnd'])){
            //看是否有重复表号，如果有，直接报错
            $start = "1".$orders['meterStart'];
            $end = "1".$orders['meterEnd'];
            $meterNum = $this->meterNumList($start, $end);
            $isExist = $this->isExist($meterNum, $oid);
            if($isExist){
                return $this->error("该表号已被使用");
            }
        }

        //orders验证
        $this->validateOrder($orders);

        //计算周期
        if(!intval($orders['deliveryStatus'])){
            $orders['orderCycle'] = $this->countDate($orders['modelStart'], $orders['modelEnd']);
            $orders['assemCycle'] = $this->countDate($orders['assemStart'], $orders['assemEnd']);
            if(($orders['orderCycle'] < 0) || ($orders['assemCycle'] < 0)){
                return $this->error("结束日期要晚于开始日期");
            }
        }else{
            $orders['orderCycle'] = 0;
            $orders['assemCycle'] = 0;
        }
        //var_dump($orders);exit();
        //获取之前的表号
        $mid = $this->getMeterNumBeforeEdit($oid);
        $edit = $this->orders()->update($orders, $where);
        if($edit < 1){
            return $this->error("修改订单失败");
        }
        //添加表号
        $this->meterListAdd($start, $end, $orders['oid']);
        $this->delMeter($mid);
        return $this->success('修改成功', 'Order/index');

    }

    /**
     * json数据
     */
    public function order(){
        $oid = input('param.oid');
        $field = "oid,state";
        $where = array('oid'=>$oid);
        $data = $this->orders()->select($field, $where);
        //var_dump($data);
        echo json_encode($data);
    }

    /**
     * 删除action
     */
    public function delOrd(){
        $oid = input('param.oid');
        $where = array('oid'=>$oid);
        $find = $this->orders()->findById($where);
        if(!$find){
            return $this->error("订单不存在");
        }
        $del = $this->orders()->del($where);
        if($del < 1){
            return $this->error("删除订单失败");
        }
        return $this->success('删除成功', 'Order/index');
    }

    /**
     * 查看详单
     */
    public function view(){
        //国家，客户，基表型号，电子模块类型，制造商，生产负责人，发货状态都是下拉框选择
        $oid = input('param.oid');
        $order = $this->orders()->findById(array('oid'=>$oid));
        $order = $this->getOneJoinId($order);
        $this->assign('order', $order);
        return $this->fetch("ord/view");
    }

    /**
     * 搜索
     */
    public function search(){
        $search = input('post.');
        //var_dump($search);exit();
        $orders = $this->orders()->join($search);
        //var_dump($orders);exit();
        $len = count($orders);
        //存在搜索的结果
        if($len >= 1){
            $orders = $this->getJoinId($orders);
        }
        //$sql = Db::table('orders')->getLastSql();
        //var_dump($sql);
        //var_dump($orders);exit();
        $this->assignState();
        $this->assignClient();
        $this->page($orders);
        //var_dump($orders);exit();
        $this->assign('orders', $orders);
        return $this->fetch("ord/index");

    }

    /**
     * //修改订单之后删除之前添加的表号
     */
    private function delMeter($num){
        foreach ($num as $v){
            Db::startTrans();
            //var_dump($meterNums);exit();
            $where = array('mid'=>$v);
            try{
                $result = $this->meters()->del($where);
                Db::commit();
            }catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
            }
        }
    }

    /**
     * order验证
     * @param $orders
     */
    private function validateOrder($orders){
        //开始表号和结束表号的判断
        //1.长度10,,11,12,13;2.长度相等；3.前四位相同;4.都是数字；5.如果是13位，只保留前面12位。
        /*//判断结束先保存到meter表
        $meter['meterStart'] = $orders['meterStart'];
        $meter['meterEnd'] = $orders['meterEnd'];
        $validate = $this->validate($meter, 'Meters');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }*/
        $this->verifyNum($orders['meterStart'], $orders['meterEnd']);
        //$orders['mid'] = '';
        $validate = $this->validate($orders, 'Orders');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }

    }


    /**
     * 表号的序列添加
     */
    private function meterListAdd($start, $end, $oid){

        $num = $this->meterNumList($start, $end);
        foreach ($num as $v){
            $meterNums['meterNum'] = $v;
            $meterNums['oid'] = $oid;
            Db::startTrans();
            //var_dump($meterNums);exit();
            try{
                $result = $this->meters()->add($meterNums, '');
                Db::commit();
            }catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
            }
        }


    }

    /**
     * 表号序列
     * @param $start
     * @param $end
     * @return mixed
     */
    private function meterNumList($start, $end){
        $startNum = floatval($start);
        $endNum = floatval($end);
        $intLength = intval($endNum-$startNum);
        $num = array();
        for ($i = 0;$i <= $intLength;$i++){
            $tem[$i] = $startNum + $i;
            $num[$i] = substr($tem[$i], 1);
        }
        return $num;
    }


    /**
     * 获取删除之前的表号列表，获取这些表号id
     */
    private function getMeterNumBeforeEdit($oid){
        $where = array('oid'=>$oid);
        //$field = "oid,numStartToEnd";
        $order = $this->orders()->findById($where);
        $start = "1".$order['meterStart'];
        $end = "1".$order['meterEnd'];
        $num = $this->meterNumList($start, $end);
        foreach ($num as $ke=>$value){
            $meter = $this->meters()->findById(array('meterNum'=>$value));
            $mid[$ke] = $meter['mid'];
        }
        return $mid;
    }

    /**
     * 判断表号是否已存在
     */
    private function isExist($meterNum, $oid){
        foreach ($meterNum as $value){
            $where = array('meterNum'=>$value);
            $find = $this->meters()->findExist($where, $oid);
            if($find){
                return 1;
            }
        }
        return 0;
    }

    /**
     * 获取当前生成的订单编号
     * @param $orderNum
     */
    private function getOid($orderNum){
        $where = array('orderNum'=>$orderNum);
        $find = $this->orders()->findById($where);
        $oid = $find['oid'];
        return $oid;
    }

    /**
     * 获取联合表中的值
     * @param $orders
     * @return mixed
     */
    private function getJoinId($orders){

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
    private function getOneJoinId($order){
        if(count($order) >= 1){
            $states = $this->state()->findById(array('sid'=>$order['sid']));
            $clients = $this->clients()->findById(array('cid'=>$order['cid']));
            $meterTypes = $this->meterTypes()->findById(array('meterId'=>$order['meterId']));
            $modelTypes = $this->modelTypes()->findById(array('modelId'=>$order['modelId']));
            $manufacturers = $this->manus()->findById(array('mfId'=>$order['mfId']));
            $productPrinciples = $this->principles()->findById(array('pid'=>$order['pid']));
            $order['state'] = $states['state'];
            $order['client'] = $clients['client'];
            $order['meterType'] = $meterTypes['meterType'];
            $order['modelType'] = $modelTypes['modelType'];
            $order['manufacturer'] = $manufacturers['manufacturer'];
            $order['productPrinciple'] = $productPrinciples['productPrinciple'];

        }else{
            $order['state'] = "";
            $order['client'] = "";
            $order['meterType'] = "";
            $order['modelType'] = "";
            $order['manufacturer'] = "";
            $order['productPrinciple'] = "";
        }
        return $order;
    }

}