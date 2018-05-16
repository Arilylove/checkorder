<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 16:47
 */
namespace app\order\validate;

use think\Validate;

class Met extends Validate{

    protected $rule=array(
        'meterType'=>'require|unique:meter_type'
    );
    protected $message=array(
        'meterType.require'=>'基表型号必须',
        'meterType.unique'=>'基表型号已存在'
    );

}