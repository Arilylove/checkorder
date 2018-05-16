<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/14
 * Time: 15:43
 */
namespace app\record\validate;

use think\Validate;
class Classifies extends Validate{
    protected $rule=array(
        'classify'=>'require|unique:problem_classify'
    );
    protected $message=array(
        'classify.require'=>'问题分类必须',
        'classify.unique'=>'问题分类已存在'
    );
}