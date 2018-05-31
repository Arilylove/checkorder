<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/31
 * Time: 16:58
 */
namespace app\receipt\validate;

use think\Validate;

class DataModels extends Validate{
    protected $rule=array(
        'specification'=>'require|unique:data_models'
    );
    protected $message=array(
        'specification.require'=>'specification必须',
        'specification.unique'=>'specification已存在'
    );



}