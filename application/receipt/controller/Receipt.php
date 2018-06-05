<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/14
 * Time: 14:34
 */
namespace app\receipt\controller;

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
     * 增加发票
     * */
    public function save(){
        $data = input('post.');
        //3.发票数据项数据--三维数组(type作为外key)
        $types = $data['type'];
        //有几条数据
        $typeLen = count($types);
        //图片处理
        $path = ROOT_PATH.DS.'public'.DS.'datamodel';
        $file = $this->getImgFile($path);
        //$data['img'] = $this->setFile($file, $typeLen);
        //a.如果有的数据项没有图片
        if(count($file) < $typeLen){
            $data['img'] = $this->doFileImg($file, $types);
        }else{
            $data['img'] = $file;
        }
        //var_dump($data['img']);exit();

        $specification = $data['specification'];
        $unit = $data['unit'];
        $qty = $data['qty'];
        $price = $data['price'];
        $receiptDatas = $this->getReceiptData($data);

        $sale_id = '';
        //1.保存到数据库的信息
        $sid = $data['sid'];
        $cid = $data['cid'];
        $findClient = $this->clients()->findById(array('cid'=>$cid));
        //固定发票模板
        //$rm_id = $data['rm_id'];
        //需要先获取今天这个sale_id的用户导出了几张发票了，然后相应的进行加减
        $uid = '';
        $asciiNum = $this->getAscii($uid);
        $num = 'LTP'.date('ymd', time()).$asciiNum.$sale_id;;
        //几个数据分类
        $data_num = count($receiptDatas);   //暂时的
        $create_time = date('Y-m-d H:i:s', time());
        $sqlData = array(
            'sid'        =>$sid,
            'cid'        =>$cid,
            'num'        =>$num,
            'uid'        =>'',
            'data_num'   =>$data_num,
            'create_time'=>$create_time
        );
        $add = $this->receipts()->add($sqlData, '');

        //带英文的日期格式
        $us_time = gmstrftime('%dth %b.,%Y', time());
        //2.发票抬头数据--一维数组
        $profomaData = $this->proformaData($num, $findClient, $us_time);

        //4.注意事项--一维数组
        $note = substr($data['note'],0,strlen($data['note'])-1);
        $noteArr = explode(",", $note);
        $notes = $this->getNotes($noteArr);

        //5.导出发票
        $fileName = $num;
        return $this->excel()->exportReceipt('', $profomaData, $receiptDatas, $notes, $fileName);

    }
    //导出测试
    public function exportTest(){

        //return DrawImg::setImg();
        $receiptModel = '';
        $profomaData = array(
            'to'=>'SAFE WATER NETWORK',
            'contact'=>'13 Tanbu Street, Adjacent French School, Shiashie, Accra, Ghana',
            'no'=>'No.: LTP170112A',
            'date'=>'DATE: Jan. 01, 2018'
        );
        $data = array(
            'type'=>array('11','12','12','13','11','12'),
            'specification'=>array('s0','s1','s2','s3','s4','s5'),
            'unit'=>array('u0','u1','u2','u3','u4','u5'),
            'img'=>array('20180604/38aca19a4866d3dd37735bc9ffd1a295.jpg','','','','',''),
            'qty'=>array('10','11','12','13','14','15'),
            'price'=>array('20','21','22','23','24','25')
        );
        $receiptData = $this->getReceiptData($data);
        $notes = array(
            '0'=>'Trade Term: C&F Accra Airport',
            '1'=>'Payment Terms: 50% Down Payment By T/T, 50% Balance Payment by T/T before shipment',
            '2'=>'Delivery Time: 8  weeks after Down Payment',
            '3'=>'The communication mode between LAISON STS water meter and CIU is RF wireless '
        );
        $a = 66;
        $asciiNum = chr($a);
        $fileName = 'LTP180601'.$asciiNum.'1';
        $excel = new Excel();
        return $excel->exportReceipt($receiptModel, $profomaData, $receiptData, $notes, $fileName);
    }

    /**
     * 重组数据项，将type相同的放在同一项
     * @return mixed
     */
    public function getReceiptData($data){
        //注：都是一维数组
        $types = $data['type'];
        //有几条数据
        $typeLen = count($types);
        $specification = $data['specification'];
        $unit = $data['unit'];
        $img = $data['img'];
        $qty = $data['qty'];
        $price = $data['price'];
        $receiptData = array([]);
        for ($i=0;$i<$typeLen;$i++){
            $receiptData[$i]['type'] = $types[$i];
            $receiptData[$i]['specification'] = $specification[$i];
            $receiptData[$i]['unit'] = $unit[$i];
            $receiptData[$i]['img'] = $img[$i];
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
                'img'=>$receiptData[$j]['img'],
                'qty'=>$receiptData[$j]['qty'],
                'price'=>$receiptData[$j]['price']
            );
        }
        foreach($receiptDatas as $k=>$v){
            foreach ($v as $value){
                $temp[$k][] = $value;
            }
        }
        return $temp;

    }

    /**
     * 计算ascii排序
     * @param $uid
     * @return string
     */
    private function getAscii($uid){
        //$field = 're_id,sid,cid,rm_id,num,uid,data_num,create_time';
        $date = date('Y-m-d', time());
        $whereTime ="'create_time','today'";
        $count = $this->receipts()->countByTime(array('uid'=>$uid), $whereTime);
        $a = 65;
        $a += $count;
        $ascii = chr($a);
        return $ascii;
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
        return $proforma;
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

    public function upload(){
        $re_id = input('param.re_id');
        $find = $this->receipts()->findById(array('re_id'=>$re_id));
        $fileName = $find['num'].'.xlsx';
        //$fileName_path = ROOT_PATH.DS.'public'.DS.'receipt'.DS.$fileName;
        return $this->downdetails($fileName);
    }
    private function downdetails($fileName){
        header("Content-type:text/html;charset=utf-8");
        $file_path = ROOT_PATH.DS.'public'.DS.'receipt'.DS.$fileName;
        //首先要判断给定的文件存在与否
        if(!file_exists($file_path)){

            return $this->error("没有该文件");
        }

        $fp=fopen($file_path,"r");
        $file_size = filesize($file_path);
        //下载文件需要用到的头
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".$file_size);
        Header("Content-Disposition: attachment; filename=".$fileName);
        $buffer=1024;
        $file_count=0;
        //向浏览器返回数据
        while(!feof($fp) && $file_count<$file_size){
            $file_con=fread($fp,$buffer);
            $file_count+=$buffer;
            echo $file_con;
        }
        fclose($fp);
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