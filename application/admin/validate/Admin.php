<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/5/8
 * Time: 17:22
 */
namespace app\admin\validate;

use think\Validate;

class Admin extends Validate{
    protected $rule = array(
        'username'=>'require|length:6,20',
        'username'=>'unique',
        'surname'=> 'require|length:3,30',
    );
    protected $message = array(
        'username.require'=>'用户名要求',
        'surname.require'=>'姓名要求',
        'username.length'=>'用户名长度在6-20位之间',
        'surname.length'=>'姓名长度在3-30位之间',
        'username.unique'=>'用户名已存在',
    );
    protected $scene = [
        'add'   =>  ['username','surname'],
        'edit'  =>  ['surname'],
    ];
}