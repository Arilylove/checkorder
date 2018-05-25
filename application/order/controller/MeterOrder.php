<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/18
 * Time: 14:06
 */
namespace app\order\controller;

use think\Lang;
class MeterOrder extends Base{
    public function index(){
        //先从订单表中获取全部信息，mid，在由mid获取表号
        $field = "oid,state,client,meterType,modelType,modelStart,modelEnd,modelNum,meterStart,meterEnd,assemStart,assemEnd,deliveryTime,orderNum,manufacturer,productPrinciple,deliveryStatus,orderCycle,assemCycle";
        $pid = $this->getPid();
        $where = array('pid'=>$pid);
        $count = $this->orders()->count($where);
        $orders = $this->orders()->selectPage($where, $count);
        $order = $this->getJoinId($orders);
        //var_dump($sql);
        $this->page($order);

        $this->assignState();
        $this->assignClient();

        //var_dump($order);exit();
        $this->assign('orders', $order);
        return $this->fetch("mord/index");

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
        return $this->fetch("mord/view");
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
        return $this->fetch("mord/update");
    }

    /**
     * 修改action
     */
    public function editOrd(){
        //订单号存在
        $oid = input('param.oid');
        $where = array('oid'=>$oid);
        $find = $this->orders()->findById($where);

        if(!$find){
            return $this->error(Lang::get('order unexist'));
        }
        //只允许更新这两项
        $orders['customTool'] = input("param.customTool");
        $orders['dataVerify'] = input('param.dataVerify');
        /*var_dump($meterStart);
        var_dump($meterEnd);exit();*/
        $edit = $this->orders()->update($orders, $where);
        if($edit < 1){
            return $this->error(Lang::get('edit fail'));
        }

        return $this->success(Lang::get('edit success'), 'MeterOrder/index');

    }

    /**
     * 搜索
     */
    public function search(){
        $search = input('post.');
        $deliveryStatus = input('param.deliveryStatus');
        $meterNum = input('param.meterNum');
        $sid = input('param.sid');
        $cid = input('param.cid');
        //var_dump($search);exit();
        $pid = $this->getPid();
        $where = array('pid'=>$pid);
        $orders = $this->orders()->newJoin($meterNum, $deliveryStatus, $sid, $cid, $where);
        //var_dump($orders);exit();
        $len = count($orders);
        //存在搜索的结果
        if($len >= 1){
            $orders = $this->getJoinId($orders);
        }
        $this->assignState();
        $this->assignClient();
        $this->page($orders);
        //var_dump($orders);exit();
        $this->assign('orders', $orders);
        return $this->fetch("mord/index");

    }
    private function getPid(){
        $surname = session('surname');
        //var_dump($surname);exit();
        //从生产负责人表中获取该负责人id
        $principle = $this->principles()->findById(array('productPrinciple'=>$surname));
        $pid = $principle['pid'];
        return $pid;
    }
}