<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/28
 * Time: 17:30
 */
namespace app\order\lang;


/**
 * 语言包配置
 * Class Langs
 * @package app\order\lang
 */
return [
    //登录提示
    'no user exist'       => 'user not exist',
    'login success'       => 'login success',
    'no authority'        =>'sorry,no authority',
    'pass wrong'          =>'wrong password',

    //基本提示
    'add success'          =>'success to add',
    'add fail'             =>'fail to add',
    'edit success'         =>'success to update',
    'edit fail'            =>'fail to update',
    'del success'          =>'success to delete',
    'del fail'             =>'fail to delete',

    //错误类型提示
    'existed user'             =>'user already exist',
    'del self unallowed'       =>'can not delete yourself',
    'del root unallowed'       =>'admininistrator can not delete',
    'login first,thanks'       =>'please login first,thanks!',
    'same to old'              =>'same to old',
    'unallowed as null'        =>'password can not be null',
    'two not same'             =>'not the same to new password',
    'number and four the same' =>'表号必须是数字,前四位相同且长度相等',
    'end bigger than start'    =>'结束表号要大于开始表号',
    'order unexist'            =>'订单不存在',
    'only start unallowed'     =>'不允许只有结束表号',
    'only 10 11 12 13 long'    =>'表号长度只能是10,11,12,13位',
    'enddate later than start' =>'结束日期要晚于开始日期',
    'meternum used'            =>'表号已被使用',
    'undel as exist user'      =>'存在用户,不允许删除',

    //未找到错误类型提示
    'unfind user'           =>'未找到该用户',
    'unfind principle'      =>'未找到该生产负责人',
    'unfind role'           =>'未找到该角色',
    'unfind state'          =>'未找到该国家信息',
    'unfind auth way'       =>'未找到该权限菜单',
    'unfind client'         =>'未找到该客户',
    'unfind dept'           =>'部门不存在',
    'unfind manufacturer'   =>'未找到该制造商',
    'unfind metertype'      =>'未找到该基表型号',
    'unfind modeltype'      =>'为找到该电子模块类型',

    //前端显示
    'users'                 =>'用户',



];