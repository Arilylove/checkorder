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
        'modelNum'=>'require|unique:orders',
        'meterStart'=>'require|length:10,13',
        'meterEnd'=>'require|length:10,13'
    );
    protected $message=array(
        'orderNum.require'=>'基表订单号不能为空',
        'orderNum.unique'=>'基表订单号已存在',
        'modelNum.require'=>'模块订单号不能为空',
        'modelNum.unique'=>'模块订单号已存在',
        'meterStart.require'=>'表号不能为空',
        'meterStart.length'=>'表号只能为10,11,12,13位长度',
        'meterEnd.require'=>'表号不能为空',
        'meterEnd.length'=>'表号只能为10,11,12,13位长度'
    );

}