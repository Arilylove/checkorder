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
    public function selectPage($where, $count, $order){
        $num = 10;
        $admin = Db::table($this->tableName)->where($where)->order($order)->paginate($num, $count, ['query' => request()->param()]);
        return $admin;
    }

    /**
     * 分类输出
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function selectGroup($field, $group, $order, $where){
        $result = Db::table($this->tableName)->field($field)->group($group)->order($order)->where($where)->select();
        return $result;
    }

    /**
     * 排序输出
     * @param $where
     * @param $order
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function orderSelect($where, $order){
        $data = Db::table($this->tableName)->where($where)->order($order)->select();
        return $data;
    }

    /**
     * 订单总额
     * @return float|int
     */
    public function sum($condition){
        $sum = Db::table($this->tableName)->sum($condition);
        return $sum;
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
    public function join($meterNum, $deliveryStatus, $sid, $cid, $orderNum, $modelNum, $mfId, $ord){
        $num = 10;
        $where = array();
        //需要多层判断（如果为空则不执行）
        if($meterNum != ''){
            $meterNum = trim($meterNum);
            $where['meterStart'] = ['<=', $meterNum];
            $where['meterEnd'] = ['>=', $meterNum];
        }
        if($deliveryStatus != ''){
            $where['deliveryStatus'] = $deliveryStatus;
        }
        if($sid != ''){
            $where['sid'] = $sid;
        }
        if($cid != ''){
            $where['cid'] = $cid;
        }
        if($orderNum != ''){
            $orderNum = trim($orderNum);
            $where['orderNum'] = ['like', "%$orderNum%"];
        }
        if($modelNum != ''){
            $modelNum = trim($modelNum);
            $where['modelNum'] = ['like', "%$modelNum%"];
        }
        if($mfId != ''){
            $where['mfId'] = $mfId;
        }
        $count = Db::table($this->tableName)->where($where)->count();
        $orders = Db::table($this->tableName)->where($where)->order($ord)->paginate($num, $count, ['query'=>request()->param()]);
        return $orders;
    }

    /**
     * 表计组
     */
    public function newJoin($meterNum, $deliveryStatus, $sid, $cid, $where, $mfId){
        $num = 10;
        //需要多层判断（如果为空则不执行）
        if($meterNum != ''){
            $where['meterStart'] = ['<=', $meterNum];
            $where['meterEnd'] = ['>=', $meterNum];
        }
        if($deliveryStatus != ''){
            $where['deliveryStatus'] = $deliveryStatus;
        }
        if($sid != ''){
            $where['sid'] = $sid;
        }
        if($cid != ''){
            $where['cid'] = $cid;
        }
        if($mfId != ''){
            $where['mfId'] = $mfId;
        }
        $count = Db::table($this->tableName)->where($where)->count();
        $orders = Db::table($this->tableName)->where($where)->paginate($num, $count, ['query'=>request()->param()]);
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