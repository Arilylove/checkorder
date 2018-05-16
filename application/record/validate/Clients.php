<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 16:46
 */
namespace app\record\validate;

use think\Validate;

class Clients extends Validate{
    protected $rule=array(
        'client'=>'require|unique:client'
    );
    protected $message=array(
        'client.require'=>'客户名称必须',
        'client.unique'=>'客户名称已存在'
    );



}