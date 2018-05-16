<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/5/23
 * Time: 15:41
 */
namespace app\admin\crypt;

class AesCrypt{
    /*
     * RIJNDAEL_128加密（ECB模式，PKCS7填充）得出固定长度hex；密码加密解密
     * */
    protected $cipher=MCRYPT_RIJNDAEL_128;  //加密算法
    protected $key = 'laisonremotepay#';
    protected $mode=MCRYPT_MODE_ECB;    //加密模式
    protected $iv = '';       //加密向量

    /*
     * AES加密->hex
     * */
    public function encrypt($password){

        if (empty($password)){
            return false;
        }
        $iv = mcrypt_create_iv(mcrypt_get_iv_size($this->cipher, $this->mode), MCRYPT_DEV_URANDOM);
        $this->iv = $iv;
        //2.再AES加密
        $aes = mcrypt_encrypt($this->cipher, $this->key, $password, $this->mode, $this->iv);
        //$lenAes = strlen($aes);
        //3.转换成字符串
        $hexstr = $this->strToHex($aes);
        //$hexstrlen = strlen($hexstr);
        //$dispstr = 'bytes len='.$lenAes.', hexstr len = '.$hexstrlen.', result='.$hexstr;
        return $hexstr;
    }

    /*
     * 解密
     * */
    public function decrypt($hex){
        if (empty($hex)){
            return false;
        }
        $iv = mcrypt_create_iv(mcrypt_get_iv_size($this->cipher, $this->mode), MCRYPT_DEV_URANDOM);
        $this->iv = $iv;
        $string = $this->hexToStr($hex);
        $password = mcrypt_decrypt($this->cipher, $this->key, $string, $this->mode,$this->iv);
        return $password;
    }
    /*
     * AES加密后转变为hexstring->EBC模式固定32位hex。
     * */
    public function strToHex($string)
    {
        $hex = "";
        $len = strlen($string);
        for($i = 0; $i < $len; $i ++){
            $curhex = dechex(ord($string[$i]));
            $lenCur = strlen($curhex);
            if ($lenCur < 2){
                $curhex = '0'.$curhex;            //防止出现有的只有一位而最后总的字节不是32位，应该是固定的32位string。
            }
            $hex .= $curhex;
        }
        $hex = strtoupper($hex);
        return $hex;
    }
    /*
     * 解密hex->string
     * */
    public function hexToStr($hex)
    {
        $string="";
        $len = strlen($hex);
        $iLen = $len - 1;
        for($i = 0;$i < $iLen;$i += 2){
            $string .= chr(hexdec($hex[$i].$hex[$i + 1]));
        }
        return $string;
    }

    /*
    * 转换成ASCII->不用
    * */
    public function ascii($content){
        $len = strlen($content);
        $ascii = '';
        for ($i = 0; $i < $len; $i ++){
            $ascii .= ord($content{$i});
        }
        return $ascii;
    }
}
