<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 16:47
 */
namespace app\order\validate;

use think\Validate;

class Mot extends Validate{

    protected $rule=array(
        'modelType'=>'require|unique:model_type'
    );
    protected $message=array(
        'modelType.require'=>'电子模块类型必须',
        'modelType.unique'=>'电子模块类型已存在'
    );

}