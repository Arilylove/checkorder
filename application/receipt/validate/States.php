<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/5/12
 * Time: 15:18
 */
namespace app\receipt\validate;

use think\Validate;

class States extends Validate{
    protected $rule=array(
        'state'=>'require|unique:states'
    );
    protected $message=array(
        'state.require'=>'国家必须',
        'state.unique'=>'国家已存在'
    );

}