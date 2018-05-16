<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/5/12
 * Time: 15:18
 */
namespace app\admin\validate;

use think\Validate;

class States extends Validate{
    protected $rule=array(
        'stId'=>['regex'=>'^[(\\+)|(\d{1})]\d{1,5}$']
    );
    protected $message=array(
        'stId.regex'=>'国家代码要求数字或是前面允许+号,最长6位'
    );

}