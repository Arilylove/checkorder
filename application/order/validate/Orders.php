<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 16:47
 */
namespace app\order\validate;

use think\Validate;

class Orders extends Validate{

    protected $rule=array(
        'orderNum'=>'require|unique:orders',
        'modelNum'=>'require',
        'meterStart'=>'require|length:10,13',
        'meterEnd'=>'require|length:10,13',
        'orderQty'=>'require|number',
    );
    protected $message=array(
        'orderNum.require'=>'基表订单号不能为空',
        'orderNum.unique'=>'基表订单号已存在',
        'modelNum.require'=>'模块订单号不能为空',
        'meterStart.require'=>'表号不能为空',
        'meterStart.length'=>'表号只能为10,11,12,13位长度',
        'meterEnd.require'=>'表号不能为空',
        'meterEnd.length'=>'表号只能为10,11,12,13位长度',
        'orderQty.require'=>'订单数量不能为空',
        'orderQty.number'=>'订单数量为整数'
    );

}