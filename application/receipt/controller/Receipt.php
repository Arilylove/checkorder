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
        //有几条数据
        $typeLen = count($types);
        //1.保存到数据库的信息
        $sid = $data['sid'];
        $cid = $data['cid'];
        $findClient = $this->clients()->findById(array('cid'=>$cid));
        $rm_id = $data['rm_id'];
        $num = 'LTP'.date('YYmmdd', time()).''.$sale_id;;
        /*$user = session('receiptuser');
        $findUser = $this->admins()->findById(array('username'=>$user));
        $sale_id = $findUser['sale_id'];
        */
        $data_num = $typeLen;   //暂时的
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

        //带英文的日期格式
        $us_time = gmstrftime('%dth %b.,%Y', time());
        //2.发票抬头数据--一维数组
        $proforma = $this->proformaData($num, $findClient, $us_time);
        //3.发票数据项数据--三维数组(type作为外key)
        $specification = $data['specification'];
        $unit = $data['unit'];
        $qty = $data['qty'];
        $price = $data['price'];
        $receiptDatas = $this->getReceiptData($data);

        //4.注意事项--一维数组
        $note = substr($data['note'],0,strlen($data['note'])-1);
        $noteArr = explode(",", $note);
        $notes = $this->getNotes($noteArr);

        //导出发票

    }
    //导出测试
    public function exportTest(){
        $receiptModel = '';
        $profomaData = array(
            'to'=>'SAFE WATER NETWORK',
            'contact'=>'13 Tanbu Street, Adjacent French School, Shiashie, Accra, Ghana',
            'no'=>'No.: LTP170112A',
            'date'=>'DATE: Jan. 01, 2018'
        );
        $receiptData = $this->getReceiptData();
        $notes = array(
            '0'=>'Trade Term: C&F Accra Airport',
            '1'=>'Payment Terms: 50% Down Payment By T/T, 50% Balance Payment by T/T before shipment',
            '2'=>'Delivery Time: 8  weeks after Down Payment',
            '3'=>'The communication mode between LAISON STS water meter and CIU is RF wireless '
        );
        $fileName = 'LTP180601A4';
        $excel = new Excel();
        return $excel->exportReceipt($receiptModel, $profomaData, $receiptData, $notes, $fileName);
    }

    /**
     * 重组数据项，将type相同的放在同一项
     * @return mixed
     */
    public function getReceiptData(){
        $data = array(
            'type'=>array('11','12','12','13','11','12'),
            'specification'=>array('s0','s1','s2','s3','s4','s5'),
            'unit'=>array('u0','u1','u2','u3','u4','u5'),
            'qty'=>array('10','11','12','13','14','15'),
            'price'=>array('20','21','22','23','24','25')
        );
        //注：都是一维数组
        $types = $data['type'];
        //有几条数据
        $typeLen = count($types);
        $specification = $data['specification'];
        $unit = $data['unit'];
        $qty = $data['qty'];
        $price = $data['price'];
        $receiptData = array([]);
        for ($i=0;$i<$typeLen;$i++){
            $receiptData[$i]['type'] = $types[$i];
            $receiptData[$i]['specification'] = $specification[$i];
            $receiptData[$i]['unit'] = $unit[$i];
            $receiptData[$i]['qty'] = $qty[$i];
            $receiptData[$i]['price'] = $price[$i];
        }
        //如果属于同一个分类
        //1.按二维数组中的type值排序
        foreach ($receiptData as $k=>$v){
            $keyType[$k] = $v['type'];
        }
        array_multisort($keyType,SORT_NUMERIC, $receiptData);
        //2.如果前后两个值相等，加到一个数组中(三维数组)
        for($j=0;$j<count($receiptData);$j++){
            $k = $receiptData[$j]['type'];
            $receiptDatas[$k][$j] = array(
                'type'=>$receiptData[$j]['type'],
                'specification'=>$receiptData[$j]['specification'],
                'unit'=>$receiptData[$j]['unit'],
                'qty'=>$receiptData[$j]['qty'],
                'price'=>$receiptData[$j]['price']
            );
        }
        /*var_dump($receiptDatas);
        exit();*/
        foreach($receiptDatas as $k=>$v){
            foreach ($v as $value){
                $temp[$k][] = $value;
            }
        }
        /*foreach ($temp as $type=>$v){
            $eachType[] = $type;
            $datas[] = $v;
        }*/
        //var_dump($temp);exit();
        return $temp;

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