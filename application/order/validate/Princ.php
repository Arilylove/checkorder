<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 16:47
 */
namespace app\order\validate;

use think\Validate;

class Princ extends Validate{

    protected $rule=array(
        'productPrinciple'=>'require|unique:product_principle'
    );
    protected $message=array(
        'productPrinciple.require'=>'生产负责人必须',
        'productPrinciple.unique'=>'生产负责人已存在'
    );
}