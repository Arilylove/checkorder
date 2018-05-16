<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 18:14
 */
namespace app\order\validate;

use think\Validate;

class Meters extends Validate{

    protected $rule=array(
        'meterNum'=>'require|unique:meter'
    );
    protected $message=array(
        'meterNum.require'=>'表号不能为空',
        'meterNum.unique'=>'表号唯一'
    );

}