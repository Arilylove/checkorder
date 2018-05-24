<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/24
 * Time: 10:53
 */
namespace app\order\validate;

use think\Validate;

class Roles extends Validate{

    protected $rule=array(
        'role_name'=>'require|unique:roles'
    );
    protected $message=array(
        'role_name.require'=>'角色必须',
        'role_name.unique'=>'角色已存在'
    );



}