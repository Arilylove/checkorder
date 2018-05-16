<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/5/12
 * Time: 15:18
 */
namespace app\order\validate;

use think\Validate;

class States extends Validate{
    protected $rule=array(
        'sid'=>['regex'=>'^[(\\+)|(\d{1})]\d{1,5}$'],
        'state'=>'require|unique:state'
    );
    protected $message=array(
        'sid.regex'=>'国家代码要求数字或是前面允许+号,最长6位',
        'state.require'=>'国家必须',
        'state.unique'=>'国家已存在'
    );

}