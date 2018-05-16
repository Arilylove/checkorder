<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 16:47
 */
namespace app\order\validate;

use think\Validate;

class Manus extends Validate{

    protected $rule=array(
        'manufacturer'=>'require|unique:manu'
    );
    protected $message=array(
        'manufacturer.require'=>'制造商必须',
        'manufacturer.unique'=>'制造商已存在'
    );



}