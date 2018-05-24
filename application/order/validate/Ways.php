<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/24
 * Time: 10:53
 */
namespace app\order\validate;

use think\Validate;

class Ways extends Validate{

    protected $rule=array(
        'w_name'=>'require|unique:ways'
    );
    protected $message=array(
        'w_name.require'=>'菜单必须',
        'w_name.unique'=>'菜单已存在'
    );



}