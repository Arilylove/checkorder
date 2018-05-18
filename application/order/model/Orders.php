<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/7
 * Time: 17:40
 */
namespace app\order\model;

use think\Db;

class Orders{
    protected $tableName = 'orders';
    protected $oid;
    /**
     * @var 国家
     */
    protected $state;
    /**
     * @var 客户
     */
    protected $client;
    /**
     * @var 基表型号
     */
    protected $meterType;
    /**
     * @var 电子模块类型
     */
    protected $modelType;
    /**
     * @var 模块交付日期
     */
    protected $deliveryTime;
    /**
     * @var 基表订单编号
     */
    protected $orderNum;
    /**
     * @var 表号
     */
    protected $meterNum;
    /**
     * @var 基表组装开始时间
     */
    protected $assemStartTime;
    /**
     * @var 基表组装完成时间
     */
    protected $assemEndTime;
    /**
     * @var 制造商
     */
    protected $manufacturer;
    /**
     * @var 生产负责人
     */
    protected $productPrinciple;
    /**
     * @var 发货状态
     */
    protected $deliveryStatus;

    public function add($order, $where){
        $result = Db::table($this->tableName)->where($where)->insert($order);
        return $result;
    }
    public function update($order, $where){
        $result = Db::table($this->tableName)->where($where)->update($order);
        return $result;
    }
    public function del($where){
        $result = Db::table($this->tableName)->where($where)->delete();
        return $result;
    }

    /**
     * 获取上次添加的id
     * @return string
     */
    public function lastSql(){
        $lastId = Db::table($this->tableName)->getLastInsID();
        return $lastId;
    }

    /**
     * id查找
     * @param $where
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function findById($where){
        $result = Db::table($this->tableName)->where($where)->find();
        return $result;
    }
    public function select($field, $where){
        $result = Db::table($this->tableName)->field($field)->where($where)->select();
        return $result;
    }
    public function selectAll(){
        $result = Db::table($this->tableName)->select();
        return $result;
    }

    /**
     * 分页
     * @param string $field
     * @param $where
     * @param $num
     * @param $count
     * @return \think\paginator\Collection
     */
    public function selectPage($where, $count){
        $num = 10;
        $admin = Db::table($this->tableName)->where($where)->paginate($num, $count);
        return $admin;
    }

    public function count($where){
        $count = Db::table($this->tableName)->where($where)->count();
        return $count;
    }

    /**
     * 分页联合查询
     * @param $field
     * @param $joinTable
     * @param $param
     * @param $where
     * @param $count
     * @return \think\paginator\Collection
     */
    public function join($search, $where){
        $num = 10;
        $deliveryStatus = $search['deliveryStatus'];
        $meterNum = $search['meterNum'];
        $orderNum = $search['orderNum'];
        $modelNum = $search['modelNum'];
        $sid = $search['sid'];
        $cid =  $search['cid'];
        //var_dump($deliveryTime);exit();
        //1.根据表号查询
        if(empty($meterNum)){
            $count = Db::table($this->tableName)->where($where)
                ->where('deliveryStatus', 'like', "%$deliveryStatus%")
                ->where('sid', 'like', "%$sid%")->where('cid', 'like', "%$cid%")->where('orderNum', 'like', "%$orderNum%")
                ->where('modelNum', 'like', "%$modelNum%")
                ->count();
            $orders = Db::table($this->tableName)->where($where)
                ->where('deliveryStatus', 'like', "%$deliveryStatus%")
                ->where('sid', 'like', "%$sid%")->where('cid', 'like', "%$cid%")->where('orderNum', 'like', "%$orderNum%")
                ->where('modelNum', 'like', "%$modelNum%")
                ->paginate($num, $count);
            $sql = Db::table('orders')->getLastSql();
            //var_dump($sql);
            //var_dump($orders);exit();

        }else{
            $count = Db::table($this->tableName)->where($where)
                ->where('deliveryStatus', 'like', "%$deliveryStatus%")
                ->where('sid', 'like', "%$sid%")->where('cid', 'like', "%$cid%")->where('orderNum', 'like', "%$orderNum%")
                ->where('modelNum', 'like', "%$modelNum%")->where('meterStart', '<=', $meterNum)->where("meterEnd", '>=', $meterNum)
                ->whereOr('meterStart', 'like', "%$meterNum%")->whereOr('meterEnd', 'like', "%$meterNum%")
                ->count();
            $orders = Db::table($this->tableName)->where($where)
                ->where('deliveryStatus', 'like', "%$deliveryStatus%")
                ->where('sid', 'like', "%$sid%")->where('cid', 'like', "%$cid%")->where('orderNum', 'like', "%$orderNum%")
                ->where('modelNum', 'like', "%$modelNum%")->where('meterStart', '<=', $meterNum)->where("meterEnd", '>=', $meterNum)
                ->whereOr('meterStart', 'like', "%$meterNum%")->whereOr('meterEnd', 'like', "%$meterNum%")
                ->paginate($num, $count);

        }

        return $orders;
    }


    /**
     * @return mixed
     */
    public function getOid()
    {
        return $this->oid;
    }

    /**
     * @param mixed $oid
     */
    public function setOid($oid)
    {
        $this->oid = $oid;
    }

    /**
     * @return 国家
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param 国家 $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return 客户
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param 客户 $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @return 基表型号
     */
    public function getMeterType()
    {
        return $this->meterType;
    }

    /**
     * @param 基表型号 $meterType
     */
    public function setMeterType($meterType)
    {
        $this->meterType = $meterType;
    }

    /**
     * @return 电子模块类型
     */
    public function getModelType()
    {
        return $this->modelType;
    }

    /**
     * @param 电子模块类型 $modelType
     */
    public function setModelType($modelType)
    {
        $this->modelType = $modelType;
    }

    /**
     * @return 模块交付日期
     */
    public function getDeliveryTime()
    {
        return $this->deliveryTime;
    }

    /**
     * @param 模块交付日期 $deliveryTime
     */
    public function setDeliveryTime($deliveryTime)
    {
        $this->deliveryTime = $deliveryTime;
    }

    /**
     * @return 基表订单编号
     */
    public function getOrderNum()
    {
        return $this->orderNum;
    }

    /**
     * @param 基表订单编号 $orderNum
     */
    public function setOrderNum($orderNum)
    {
        $this->orderNum = $orderNum;
    }

    /**
     * @return 表号
     */
    public function getMeterNum()
    {
        return $this->meterNum;
    }

    /**
     * @param 表号 $meterNum
     */
    public function setMeterNum($meterNum)
    {
        $this->meterNum = $meterNum;
    }

    /**
     * @return 基表组装开始时间
     */
    public function getAssemStartTime()
    {
        return $this->assemStartTime;
    }

    /**
     * @param 基表组装开始时间 $assemStartTime
     */
    public function setAssemStartTime($assemStartTime)
    {
        $this->assemStartTime = $assemStartTime;
    }

    /**
     * @return 基表组装完成时间
     */
    public function getAssemEndTime()
    {
        return $this->assemEndTime;
    }

    /**
     * @param 基表组装完成时间 $assemEndTime
     */
    public function setAssemEndTime($assemEndTime)
    {
        $this->assemEndTime = $assemEndTime;
    }

    /**
     * @return 制造商
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * @param 制造商 $manufacturer
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }

    /**
     * @return 生产负责人
     */
    public function getProductPrinciple()
    {
        return $this->productPrinciple;
    }

    /**
     * @param 生产负责人 $productPrinciple
     */
    public function setProductPrinciple($productPrinciple)
    {
        $this->productPrinciple = $productPrinciple;
    }

    /**
     * @return 发货状态
     */
    public function getDeliveryStatus()
    {
        return $this->deliveryStatus;
    }

    /**
     * @param 发货状态 $deliveryStatus
     */
    public function setDeliveryStatus($deliveryStatus)
    {
        $this->deliveryStatus = $deliveryStatus;
    }



}