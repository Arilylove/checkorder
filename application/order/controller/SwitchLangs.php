<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/28
 * Time: 17:09
 */
namespace app\order\controller;

use think\Lang;
/**
 * 切换语言包
 * Class SwitchLangs
 * @package app\order\controller
 */
class SwitchLangs extends Base
{
    //现允许的语言列表有中文和英文
    public static function get_lang(){
        $lang = input('param.lang');
        //如果客户端没有设置语言切换
        if($lang == null){
            //默认中文
            $lang = 'zh-cn';
        }
        //加载当前语言包
        Lang::load(APP_PATH . DS . 'order' . DS . 'lang' . DS . $lang.'.php',  $lang);
        return $lang;
    }


}