<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/5/8
 * Time: 17:24
 */
namespace app\admin\validate;
use think\Validate;
class Code extends Validate{
    protected $rule=array(
        'mac'=>'require|length:12'
    );
    protected $message=array(
        'mac.length'=>'mac地址长度为12位数字字母'
    );
}