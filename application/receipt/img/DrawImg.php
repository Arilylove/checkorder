<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/6/4
 * Time: 9:06
 */
namespace app\receipt\img;

use think\Image;
class DrawImg{

    /**
     * 图像处理
     */
    public static function setImg(){
        $image = Image::open(ROOT_PATH.DS.'public'.DS.'img'.DS.'laison.jpg');
        // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.png
        //$image->thumb(150, 150)->save('./thumb.png');

        var_dump($image);


    }
}