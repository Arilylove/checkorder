<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/24
 * Time: 18:43
 */
namespace app\receipt\lang;

use think\Lang;

/**
 * 语言包配置
 * Class Langs
 * @package app\order\lang
 */
return [
    //登录提示
    'no user exist'       => '用户不存在',
    'login success'       => '登录成功',
    'no authority'        =>'对不起,没有权限',
    'pass wrong'          =>'密码错误',

    //基本提示
    'add success'          =>'添加成功',
    'add fail'             =>'添加失败',
    'edit success'         =>'修改成功',
    'edit fail'            =>'修改失败',
    'del success'          =>'删除成功',
    'del fail'             =>'删除失败',

    //错误类型提示
    'del self unallowed'       =>'不能删除自己',
    'del root unallowed'       =>'后台管理员不可删除',
    'login first,thanks'       =>'请先登录,谢谢！',
    'same to old'              =>'修改密码同原始密码相同',
    'unallowed as null'        =>'密码不能为空',
    'two not same'             =>'两次输入密码不相同',
    'number and four the same' =>'表号必须是数字,前四位相同且长度相等',
    'end bigger than start'    =>'结束表号要大于开始表号',
    'only start unallowed'     =>'不允许只有结束表号',
    'only 10 11 12 13 long'    =>'表号长度只能是10,11,12,13位',
    'enddate later than start' =>'结束日期要晚于开始日期',
    'undel as exist user'      =>'存在用户,不允许删除',
    'upload file please'       =>'请上传文件',
    'upload excel file'        =>'请上传excel文件',
    'upload img file'          =>'请上传图片文件',
    'type can not null'        =>'数据分类不能为空',
    'file not exist'           =>'文件不存在',
    'model not exist'          =>'模板不存在',

    //已存在错误提示
    'existed user'             =>'用户已存在',
    'meternum used'            =>'表号已被使用',
    'existed client'           =>'客户已存在',

    //未找到错误类型提示
    'order unexist'            =>'订单不存在',
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
    'unfind note'           =>'未找到该注意事项',
    'unfind receipt model'  =>'未找到该发票模板',


    //前端显示
    'users'                 =>'用户',




];