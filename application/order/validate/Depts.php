<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/24
 * Time: 10:52
 */
namespace app\order\validate;

use think\Validate;

class Depts extends Validate{

    protected $rule=array(
        'dept_name'=>'require|unique:depts'
    );
    protected $message=array(
        'dept_name.require'=>'部门必须',
        'dept_name.unique'=>'部门已存在'
    );



}