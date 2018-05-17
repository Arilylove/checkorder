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
        'orderQty'=>'require|number',
    );
    protected $message=array(
        'orderQty.require'=>'订单数量不能为空',
        'orderQty.number'=>'订单数量为整数'
    );

}