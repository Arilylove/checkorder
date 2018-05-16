<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/5/8
 * Time: 17:24
 */
namespace app\admin\validate;
use think\Validate;
class Client extends Validate{
    protected $rule=array(
        'email'=>'email'
    );
    protected $message=array(
        'email.email'=>'邮箱格式不正确'
    );
}