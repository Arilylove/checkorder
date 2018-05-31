<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/31
 * Time: 16:56
 */
namespace app\receipt\validate;

use think\Validate;

class ReceiptModels extends Validate{
    protected $rule=array(
        'model'=>'require|unique:receipt_models'
    );
    protected $message=array(
        'model.require'=>'发票模板名称必须',
        'model.unique'=>'发票模板名称已存在'
    );



}