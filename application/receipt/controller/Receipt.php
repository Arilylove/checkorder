<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/14
 * Time: 14:34
 */
namespace app\receipt\controller;

use think\Db;
use app\receipt\crypt\AesCrypt;
use app\receipt\model\Admins;

/**
 * 发票
 * Class Receipt
 * @package app\receipt\controller
 */
class Receipt extends Base{

    /**
     *查询修改的信息
     */
    public function one(){
        $re_id = input('param.re_id');
        $where = array('re_id'=>$re_id);
        $field = 're_id,sid,cid,rm_id,num,uid,data_num,create_time';
        $data = $this->receipts()->select($field, $where);
        echo json_encode($data);
    }

    /**
     * 列表
     * */
    public function index(){
        $field = 're_id,sid,cid,rm_id,num,uid,data_num,create_time';
        $where = '';
        $order = 'create_time desc';
        $data = $this->receipts()->selectPage($field, $where);
        $data = $this->resetReceipts($data);
        $this->page($data);
        $this->assign('receipts', $data);
        return $this->fetch("ret/index");
    }


    /**
     * 跳转到添加页
     * @return mixed
     */
    public function add(){
        //国家
        $this->assignState();
        //客户
        $this->assignClient();
        //发票模板
        $this->assignRetModel();
        //数据模板
        $this->assignDataModel();
        //注意事项
        $this->assignJsonNote();
        return $this->fetch('ret/add');
    }

    /**
     * 增加
     * */
    public function save(){
        $data = input('post.');
        var_dump($data);exit();
        /*array(8) { ["rm_id"]=> string(1) "1" ["sid"]=> string(1) "1"
        ["type"]=> array(1) { [0]=> string(5) "gerfg" }
        ["specification"]=> array(1) { [0]=> string(138) "LAPIS STS Vending Management System - C/S Version,
        Microsoft SQL Server Database 2008 WITHOUT CUSTMOZATION MODIFICATION ON SOFTWARE PART" }
         ["unit"]=> array(1) { [0]=> string(4) "copy" } ["qty"]=> array(1) { [0]=> string(3) "1.0" }
        ["price"]=> array(1) { [0]=> string(5) "14999" } ["note"]=> string(4) "1,2," }*/
        $types = $data['type'];
        //有几项分类数据
        $typeLen = count($types['type']);
        //保存到数据库的信息
        $sid = $data['sid'];
        $cid = $data['cid'];
        $findClient = $this->clients()->findById(array('cid'=>$cid));
        $rm_id = $data['rm_id'];
        $num = 'LTP'.date('YYmmdd', time()).''.$sale_id;;
        /*$user = session('receiptuser');
        $findUser = $this->admins()->findById(array('username'=>$user));
        $sale_id = $findUser['sale_id'];
        */
        $data_num = $typeLen;
        $create_time = date('Y-m-d H:i:s', time());
        $sqlData = array(
            'sid'        =>$sid,
            'cid'        =>$cid,
            'rm_id'      =>$rm_id,
            'num'        =>$num,
            'uid'        =>'',
            'data_num'   =>$data_num,
            'create_time'=>$create_time
        );
        //$add = $this->receipts()->add($sqlData, '');

        //发票数据
        $specification = $data['specification'];
        $unit = $data['unit'];
        $qty = $data['unit'];
        $price = $data['price'];

        //注意事项
        $note = substr($data['note'],0,strlen($data['note'])-1);
        $noteArr = explode(",", $note);
        $notes = $this->getNotes($noteArr);

        //带英文的日期格式
        $us_time = gmstrftime('%dth %b.,%Y', time());
        $proforma = $this->proformaData($num, $findClient, $us_time);
        //数据项数据
        for ($i=0;$i<$typeLen;$i++){

        }



        //导出发票
        $add = $this->receipts()->add($data, '');
        if (!$add){
            return $this->error('添加失败');
        }
        return $this->success('添加成功', 'Receipt/index');
    }

    /**
     * 发票抬头：客户信息
     * @param $sale_id
     * @param $findClient
     * @param $create_time
     */
    private function proformaData($num, $findClient, $create_time){
        $proforma['no'] = 'No.:'.$num;
        $proforma['to'] = $findClient['client'];
        $proforma['contact'] = '';
        if($findClient['address'] != ''){
            $proforma['contact'] .= $findClient['address']." ";
        }
        if($findClient['contacts'] != ''){
            $proforma['contact'] .= $findClient['contacts']." ";
        }
        if($findClient['phone'] != ''){
            $proforma['contact'] .= $findClient['phone'];
        }
        $proforma['email'] = $findClient['email'];
        $proforma['date'] = "DATE:".$create_time;
    }
    /*
     * 由note的nid获取note描述
     */
    private function getNotes($notes){
        $newNotes = array();
        $len = count($notes);
        for($i=0;$i<$len;$i++){
            $nid = $notes[$i];
            $find = $this->notes()->findById(array('nid'=>$nid));
            $newNotes[$i] = $find['note'];
        }
        return $newNotes;
    }


    /**
     * 删除
     * */
    public function del(){
        $re_id = input('param.re_id');
        //var_dump($re_id);exit();
        $where = array('re_id'=>$re_id);
        $user = $this->receipts()->findById($where);
        //$self = session('username');

        $delete = $this->receipts()->del($where);
        if (!$delete){
            return $this->error('删除失败');
        }
        //弹出确认窗口
        return $this->success('删除成功', 'Receipt/index');
    }
    /**
     * @return mixed
     * 模糊查询
     */
    public function search(){
        $search = input('param.search');
        $where = array();
        $field = 're_id,sid,cid,rm_id,num,uid,data_num,create_time';
        $data = $this->receipts()->selectPage($field, $where);
        $this->page($data);
        $this->assign('receipts', $data);
        return $this->fetch('ret/index');

    }

    /**
     * 跳转到导出页面，输入信息
     */
    public function export(){
        return $this->fetch("ret/export");
    }

    /**
     * 导出action
     */
    public function exportA(){
        //1.发票收方消息
        $pro['to'] = input('param.to');
        $pro['nameNum'] = input('param.nameNum');
        $pro['no'] = input('param.no');
        $pro['email'] = input('param.email');
        //带英文的日期格式
        $pro['date'] = gmstrftime('%dth %b.,%Y', time());

        //2.plastic
        $post = input('post.');
        $plastic['specification'] = $post['specification1'];
        $plastic['unit'] = $post['unit1'];
        $plastic['qty'] = $post['qty1'];
        $plastic['price'] = $post['price1'];
        //3.software
        $software['specification'] = $post['specification'];
        $software['unit'] = $post['unit'];
        $software['qty'] = $post['qty'];
        $software['price'] = $post['price'];

        //数组重新组合，达到导出数据的要求
        $plastic = $this->resetArray($plastic);
        $software = $this->resetArray($software);
        /*var_dump($pro);
        var_dump($plastic);
        var_dump($software);
        exit();*/
        /*$excel = new Excel();
        return $excel->export($pro, $plastic, $software, $pro['no']);*/
        $pdf = new Pdfexport();
        return $pdf->exportPdf($pro, $plastic, $software, $pro['no']);

    }

    /**
     * 获取的数组重新组合
     */
    private function resetArray($array){
        $specification = $array['specification'];
        $unit = $array['unit'];
        $qty = $array['qty'];
        $price = $array['price'];
        $new = array();
        for($i=0;$i<count($specification);$i++){
            $new[$i]['specification'] = $specification[$i];
            $new[$i]['unit'] = $unit[$i];
            $new[$i]['qty'] = $qty[$i];
            $new[$i]['price'] = $price[$i];
        }
        return $new;
    }

    /**
     * 重组（二维数组）
     * @param $receipts
     */
    private function resetReceipts($receipts){
        $len = count($receipts);
        if($len >= 1){
            for($i=0;$i<$len;$i++){
                $receipts[$i] = $this->resetReceipt($receipts[$i]);
            }
        }
        return $receipts;
    }
    /**
     * 重组（一维数组）
     * @param $receipt
     */
    private function resetReceipt($receipt){
        $len = count($receipt);
        if($len >= 1){
            $sid = $receipt['sid'];
            $cid = $receipt['cid'];
            $uid = $receipt['uid'];
            $rm_id = $receipt['rm_id'];
            $findState = $this->state()->findById(array('sid'=>$sid));
            $findClient = $this->clients()->findById(array('cid'=>$cid));
            $findUser = $this->admins()->findById(array('uid'=>$uid));
            //$findRetModel = $this->receiptModels()->findById(array('rm_id'=>$rm_id));
            $receipt['state'] = $findState['state'];
            $receipt['client'] = $findClient['client'];
            $receipt['surname'] = $findUser['surname'];

        }else{
            $receipt['state'] = '';
            $receipt['client'] = '';
            $receipt['surname'] = '';
        }
        return $receipt;

    }
}