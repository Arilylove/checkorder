<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/7
 * Time: 17:25
 */
namespace app\receipt\controller;

use app\receipt\model\Admins;
use app\receipt\model\Clients;
use app\receipt\model\DataModels;
use app\receipt\model\Notes;
use app\receipt\model\ReceiptModels;
use app\receipt\model\Receipts;
use app\receipt\model\SaleDepts;
use app\receipt\model\States;
use think\Controller;
use think\Db;
use app\receipt\crypt\AesCrypt;

class Base extends Controller{
    public function _initialize(){
        $username = session('receiptuser');
        $status = session('status');
        $a = is_null($username);
        //var_dump($a);exit();
        //判断用户是否已经登录
        if ($a) {
            return $this->error('对不起,您还没有登录!请先登录', 'Login/index');
        }
        $this->assign("username", $username);
        $this->assign("status", $status);
        return true;
    }
   protected function state(){
       $state = new States();
       return $state;
   }
   protected function clients(){
       $client = new Clients();
       return $client;
   }

    protected function datas(){
       $data = new DataModels();
       return $data;
    }
    protected function notes(){
       $notes = new Notes();
       return $notes;
    }
    protected function sales(){
       $sales = new SaleDepts();
       return $sales;
    }
    protected function admins(){
       $admin = new Admins();
       return $admin;
    }
    protected function hex(){
        $hex = new AesCrypt();
        return $hex;
    }
    protected function receiptModels(){
       $model = new ReceiptModels();
       return $model;
    }
    protected function receipts(){
       $receipts = new Receipts();
       return $receipts;
    }
    protected function excel(){
       $excel = new Excel();
       return $excel;
    }
    /**
     * 分页
     * @param $table
     */
    public function page($table){
        $page = $table->render();
        $currentPage = $table->currentPage();
        $this->assign('currentPage', $currentPage);
        $this->assign('pageOrder', ($currentPage-1)*10);
        $this->assign('page', $page);
    }



    /**
     * 客户option
     */
    protected function assignClient(){
        $where = '';
        $field = 'cid,sid,client,address,contacts,phone,email,create_time';
        $client = $this->clients()->select($field, $where);
        $this->assign('clients', $client);
    }

    /**
     * 国家option
     */
    protected function assignState(){
        $field = 'sid,state';
        $state = $this->state()->select($field, '');
        $this->assign('state', $state);
    }

    /**
     * 业务部门option
     */
    protected function assignSale(){
        $field = 'sale_id,sale_name,remark,create_time,status';
        $where = '';
        $data = $this->sales()->select($field, $where);
        $this->assign('saledepts', $data);
    }

    /**
     * 发票模板
     */
    protected function assignRetModel(){
        $field = 'rm_id,model,model_file,create_time';
        $where = '';
        $data = $this->receiptModels()->select($field, $where);
        $this->assign("retmodels", $data);
    }

    /**
     * 数据模板
     */
    protected function assignDataModel(){
        $field = 'dm_id,type,specification,unit,img,create_time';
        $where = '';
        $data = $this->datas()->select($field, $where);
        $this->assign("datas", $data);
    }

    protected function assignJsonNote(){
        $where = '';
        $field = 'nid,note,create_time';
        $data = $this->notes()->select($field, $where);
        $jsonNote = json_encode($data);
        $this->assign("jsonNotes", $jsonNote);
    }

    /**
     * @param $path
     * @return string|void
     */
    public function getImgFile($path){
        $file = request()->file('img');
        //上传了文件
        //var_dump(count($file));exit();
        //$fileName ;
        if($file){
            $len = count($file);
            foreach ($file as $key=>$value){
                $fileName[$key] = $this->oneFile($value, $path);
            }
            return $fileName;
        }
        return array();
    }

    /**
     * 返回一个图像文件名
     * @param $file
     * @param $path
     * @return string|void
     */
    private function oneFile($file, $path){
        if($file){
            $info = $file->move($path);
            $type = $info->getExtension();           //文件类型
            //var_dump($type);exit;
            if(($type != 'png') && ($type != 'jpg') && ($type != 'jpeg') && ($type != 'gif')){
                return $this->error(Lang::get('upload img file'));
            }
            //$path = $info->getPath();
            $date = date('Ymd', time());
            $fileName = $info->getFilename();
            return $date.DS.$fileName;
        }
        return '';

    }

    /**
     * 处理缺少图片的数据项
     * @param $file
     * @param $type
     * @return mixed
     */
    public function doFileImg($file, $type){
        foreach ($type as $key=>$value){
            $temp1[] = $key;
        }
        foreach ($file as $k=>$v){
            $temp2[] = $k;
        }
        //1.比较两个数组，取出不同的值
        $temp = array_diff($temp1, $temp2);
        foreach ($temp as $val){
            $file[$val] = '';
        }
        foreach ($file as $k=>$v){
            $sortK[$k] = $k;
        }
        array_multisort($sortK, SORT_NUMERIC, $file);
        //2.对file进行排序

        /*var_dump($temp);echo '<hr/>';
        var_dump($temp2);echo '<hr/>';
        var_dump($file);exit();*/
        return $file;

    }

    /*
      *修改密码
      * */
    public function update(){
        $username = session('receiptuser');
        $this->assign('username', $username);
        return $this->fetch('lic/upPass');
    }

    public function updatePassword(){
        $username = session('receiptuser');
        //$this->assign('username', $username);
        $where = array('username'=>$username);
        $admin = Db::table('admin')->where($where)->find();
        if (!$admin){
            return $this->error('该用户不存在');
        }
        //var_dump($admin['password']);exit();
        $this->assign('uid', $admin['uid']);
        $string = new AesCrypt();
        //解密
        $password = $admin['password'];
        //var_dump($password);exit();
        $inputPassword = $string->encrypt(input('param.password'));
        $update = $string->encrypt(input('param.update'));
        $confirm = $string->encrypt(input('param.confirm'));
        if($password != $inputPassword){
            return $this->error('密码输入错误');
        }
        if ($update == $password){
            return $this->error('修改密码同原始密码相同');
        }
        if ($update == ''){
            return $this->error('密码不能为空');
        }
        if ($update != $confirm){
            return $this->error('两次输入密码不相同');
        }
        $result = Db::table('admin')->where('username', $username)->update(['password'=>$update]);
        //var_dump($result);exit();
        if (!$result){
            return $this->error('修改失败');
        }
        session('receiptuser', null);
        return $this->success('修改成功,返回登录界面', 'Login/index');

    }

}