<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/14
 * Time: 18:47
 */
namespace app\receipt\controller;

use think\Controller;
use think\Db;
use think\Lang;

class Excel extends Base {

    /**
     * 图片设置
     * @param $img
     * @return \PHPExcel_Worksheet_Drawing
     * @throws \PHPExcel_Exception
     */
    public function setImg($img){
        $objDrawing = new \PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('Photo');
        $objDrawing->setDescription('Photo');
        $objDrawing->setPath($img);
        $objDrawing->setHeight(60);
        $objDrawing->setWidth(80);
        $objDrawing->setOffsetX(10);
        $objDrawing->setOffsetY(6);
        return $objDrawing;

    }

    /**
     * 香港公司
     * @throws \PHPExcel_Reader_Exception
     */
   public function forHongkong($profomaData, $receiptData, $notes, $fileName){
       Vendor('phpexcel.PHPExcel');
       Vendor('phpexcel.PHPExcel.IOFactory');
       Vendor('phpexcel.PHPExcel.Reader.Excel5');
       Vendor('phpexcel.PHPExcel.Writer.Excel2007');
       //1.导入发票模板
       $temPath = ROOT_PATH.'public'.DS."model".DS."model1.xls";
       //var_dump($temPath);exit();
       //检查文件路径
       if(!file_exists($temPath)){
           return $this->error(Lang::get('model not exist'));
       }
       //加载模板
       $phpCreate =  \PHPExcel_IOFactory::createReader("Excel5");
       //var_dump($phpCreate);exit();
       $phpexcel = $phpCreate->load($temPath);
       return $this->exportReceipt($phpexcel, $profomaData, $receiptData, $notes, $fileName);
   }

    /**
     * laison
     * @throws \PHPExcel_Reader_Exception
     */
   public function forLaison($profomaData, $receiptData, $notes, $fileName){
       Vendor('phpexcel.PHPExcel');
       Vendor('phpexcel.PHPExcel.IOFactory');
       Vendor('phpexcel.PHPExcel.Reader.Excel5');
       Vendor('phpexcel.PHPExcel.Writer.Excel2007');
       //1.导入发票模板
       //$temPath = ROOT_PATH.'public'.DS."model".DS.$receiptModel;
       $temPath = ROOT_PATH.'public'.DS."model".DS."model1.xls";
       //var_dump($temPath);exit();
       //检查文件路径
       if(!file_exists($temPath)){
           return $this->error(Lang::get('model not exist'));
       }
       //加载模板
       $phpCreate =  \PHPExcel_IOFactory::createReader("Excel5");
       //var_dump($phpCreate);exit();
       $phpexcel = $phpCreate->load($temPath);
       return $this->exportReceipt($phpexcel, $profomaData, $receiptData, $notes, $fileName);
   }
    /**
     * 有图片模板导出excel
     * * @param $receiptModel 发票模板
     * @param $profomaData   发票抬头
     * @param $receiptData  数据项数据
     * @param $notes   注意事项数据
     * @param $fileName  发票名--即发票号
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function exportReceipt($phpexcel, $profomaData, $receiptData, $notes, $fileName){
        //2.发票抬头
        $phpexcel->getActiveSheet()->setCellValue('B9', $profomaData['to']);
        $phpexcel->getActiveSheet()->setCellValue('B10', $profomaData['contact']);
        /*if($profomaData['email'] != ''){
            $phpexcel->getActiveSheet()->setCellValue('B11', $profomaData['email']);
        }*/
        $phpexcel->getActiveSheet()->setCellValue('G9', $profomaData['no']);
        $phpexcel->getActiveSheet()->setCellValue('G10', $profomaData['date']);

        //3.数据项数据
        $rowIndex = 13;
        //a.总共有几项数据
        $dataNum = count($receiptData);
        if($dataNum < 7){
            $long = 7-$dataNum;
            //删除多余的单元格项
            $phpexcel->getActiveSheet()->removeRow(25-($long-1)*2, $long*2);

        }
        /*if($dataNum > 2){
            $long = $dataNum-2;
            $phpexcel->getSheet()->insertNewRowBefore(17, $long*2);
        }*/

        //b.先将每项type保存
        foreach ($receiptData as $type=>$v){
            $eachType[] = $type;
            $newReceiptData[] = $v;
        }
        //var_dump($newReceiptData);exit();
        $sum = 0;   //计算数据总和
        for($len=0;$len<$dataNum;$len++){
            $eachTypeLen = count($newReceiptData[$len]);
            //数据超过1行，需要插入新的单元格
            if($eachTypeLen > 1){
                $phpexcel->getSheet()->insertNewRowBefore($rowIndex+2, $eachTypeLen-1);
            }
            //数据编号
            $index = 1;
            //type值
            $typeId = $eachType[$len];
            $findType = $this->datas()->findById(array('dm_id'=>$typeId));
            $type = $findType['type'];
            $phpexcel->getActiveSheet()->setCellValue('A'.$rowIndex, $type);
            //设置填充的样式和背景色
            $phpexcel->getActiveSheet()->getStyle( 'A'.$rowIndex)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $phpexcel->getActiveSheet()->getStyle( 'A'.$rowIndex)->getFill()->getStartColor()->setARGB('80808080');        //设置背景色
            //合并单元格
            $phpexcel->getActiveSheet()->mergeCells('A'.$rowIndex.':G'.$rowIndex);
            $phpexcel->getActiveSheet()->getRowDimension($rowIndex)->setRowHeight(24);
            $rowIndex += 1;
            //设置type行背景色
            foreach ($newReceiptData[$len] as $k=>$value){
                $phpexcel->getActiveSheet()->setCellValue('A'.$rowIndex, $index);
                $phpexcel->getActiveSheet()->setCellValue('B'.$rowIndex, $value['specification']);
                $phpexcel->getActiveSheet()->setCellValue('C'.$rowIndex, $value['unit']);
                //一张图片
                if($value['img']){
                    $path = ROOT_PATH.'public'.DS.'datamodel'.DS.$value['img'];
                    $img = $this->setImg($path);
                    $img->setCoordinates('D'.$rowIndex);
                    $img->setWorksheet($phpexcel->getActiveSheet());
                }else{
                    $phpexcel->getActiveSheet()->setCellValue('D'.$rowIndex, '/');
                }

                $phpexcel->getActiveSheet()->setCellValue('E'.$rowIndex, $value['qty']);

                //设置数值格式
                $phpexcel->getActiveSheet()->getStyle('E'.$rowIndex)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_GENERAL);

                $value['price'] = number_format($value['price'], 2, '.','');
                //设置默认前缀
                $phpexcel->getActiveSheet()->getStyle('F'.$rowIndex)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_new);
                $phpexcel->getActiveSheet()->setCellValue('F'.$rowIndex, $value['price']);
                //设置默认前缀
                $phpexcel->getActiveSheet()->getStyle('G'.$rowIndex)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_new);

                $item4 = number_format($value['qty']*$value['price'], 2, '.','');
                $phpexcel->getActiveSheet()->setCellValue('G'.$rowIndex, '=E'.$rowIndex.'*'.'F'.$rowIndex);
                $sum += $value['qty']*$value['price'];
                //设置行高
                //如果有图片，行高设置大一些
                if($value['img']){
                    $phpexcel->getActiveSheet()->getRowDimension($rowIndex)->setRowHeight(88);
                }else{
                    $phpexcel->getActiveSheet()->getRowDimension($rowIndex)->setRowHeight(60);
                }
                //居中
                $phpexcel->getActiveSheet()->getStyle('B'.$rowIndex)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $rowIndex++;
                $index++;
            }
        }
        //var_dump($sum);exit();
        //4.设置total值
        $sum = number_format($sum, 2, '.','');
        //如果有删除的项，需要拆分单元格
        if($dataNum < 7){
            $phpexcel->getActiveSheet()->unmergeCells('A'.$rowIndex.':G'.$rowIndex);
        }
        //$phpexcel->getActiveSheet()->setCellValue('B'.$rowIndex, 'Total');
        //合并单元格
        $phpexcel->getActiveSheet()->mergeCells('B'.$rowIndex.':F'.$rowIndex);
        //设置默认前缀
        $phpexcel->getActiveSheet()->getStyle('G'.$rowIndex)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_new);
        $phpexcel->getActiveSheet()->setCellValue('G'.$rowIndex, $sum);

        //5.note数据
        $rowIndex += 2;
        $noteLen = count($notes);
        //如果没有12条，删除多余的
        if($noteLen < 12){
            $phpexcel->getActiveSheet()->removeRow($rowIndex+$noteLen, 12-$noteLen);
        }
        for($j=0;$j<$noteLen;$j++){
            $phpexcel->getActiveSheet()->setCellValue('A'.$rowIndex, ($j+1).'.'.$notes[$j]);
            $rowIndex++;
        }

        //导出属性设置
        //$date = str_replace("/","_",$date);
        $outputFileName = $fileName.".xlsx";
        ob_end_clean();//清除缓冲区,避免乱码
        //$objWriter = new \PHPExcel_Writer_Excel2007($phpexcel, 'Excel2007');
        $objWriter = new \PHPExcel_Writer_Excel2007($phpexcel, 'Excel2007');
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outputFileName);
        header("Content-Transfer-Encoding: binary");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        //同时保存到本地
        $objWriter->save(ROOT_PATH.DS.'public'.DS.'receipt'.DS.$outputFileName);
        $objWriter->save('php://output');

    }


    /**
     * 导出excel封装--无图片版本
     * @param $profomaData  发票收方信息array
     * @param $plasticData  plastic信息array
     * @param $softwareData software信息array
     * @param $filename 发票名称
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function export($profomaData, $plasticData, $softwareData, $filename)
    {
        /*var_dump($profomaData);
        var_dump($plasticData);
        var_dump($softwareData);exit();*/
        $date = date('Ymd-His', time());
        Vendor('phpexcel.PHPExcel');
        Vendor('phpexcel.PHPExcel.IOFactory');
        Vendor('phpexcel.PHPExcel.Reader.Excel5');
        Vendor('phpexcel.PHPExcel.Writer.Excel2007');
        $temPath = ROOT_PATH.'public'.DS."model".DS."model.xls";
        //var_dump($temPath);exit();
        //检查文件路径
        if(!file_exists($temPath)){
            return $this->error('模板不存在');
        }
        //加载模板
        $phpCreate =  \PHPExcel_IOFactory::createReader("Excel5");
        //var_dump($phpCreate);exit();
        $phpexcel = $phpCreate->load($temPath);
        //var_dump($phpCreate);exit();
        //1.收方数据
        $phpexcel->getActiveSheet()->setCellValue('B9', $profomaData['to']);
        $phpexcel->getActiveSheet()->setCellValue('B10', $profomaData['nameNum']);
        $phpexcel->getActiveSheet()->setCellValue('B11', $profomaData['email']);
        $phpexcel->getActiveSheet()->setCellValue('F9', "No.:".$profomaData['no']);
        $phpexcel->getActiveSheet()->setCellValue('F10', "DATE:".$profomaData['date']);
        //2.plastic数据
        $rowIndex = 14;
        $plasticDataLen = count($plasticData);
        //数据超过2行，需要插入新的单元格
        if($plasticDataLen > 2){
            $phpexcel->getSheet()->insertNewRowBefore(16, $plasticDataLen-2);
        }/*else if($plasticDataLen < 2){  //只有一行数据
            //合并单元格
            $phpexcel->getActiveSheet()->mergeCells('A14:F15');
        }*/
        //var_dump($groupMember);exit();
        //填充可写的数据
        $sum = 0; //计算数据总和
        //数据编号
        $i = 1;
        foreach($plasticData as $k=>$item){
            $item['no'] = $i;
            //一览表写入数据
            $phpexcel->getActiveSheet()->setCellValue('A'.$rowIndex, $item['no']);
            $phpexcel->getActiveSheet()->setCellValue('B'.$rowIndex, $item['specification']);
            $phpexcel->getActiveSheet()->setCellValue('C'.$rowIndex, $item['unit']);
            $phpexcel->getActiveSheet()->setCellValue('D'.$rowIndex, $item['qty']);
            $item['price'] = number_format($item['price'], 2, '.','');
            $phpexcel->getActiveSheet()->setCellValue('E'.$rowIndex, 'US$'.$item['price']);
            $item4 = number_format($item['qty']*$item['price'], 2, '.','');
            $phpexcel->getActiveSheet()->setCellValue('F'.$rowIndex, 'US$'.$item4);
            $sum += $item['qty']*$item['price'];
            //设置行高
            $phpexcel->getActiveSheet()->getRowDimension($rowIndex)->setRowHeight(44);
            $rowIndex++;
            $i++;
        }
        //3.software数据
        //新的一行数据
        if($plasticDataLen < 2){
            $rowIndex = $rowIndex +2;
        }else{
            $rowIndex = $rowIndex+1;
        }
        $softDataLen = count($softwareData);
        if($softDataLen > 2){
            $phpexcel->getSheet()->insertNewRowBefore($rowIndex+2, $softDataLen-2);
        }/*else if($softDataLen < 2){
            //合并单元格
            $phpexcel->getActiveSheet()->mergeCells('A'.$rowIndex.':F'.($rowIndex+1));
        }*/
        //数据编号
        $j = 1;
        foreach($softwareData as $k=>$item){
            $item['no'] = $j;
            $phpexcel->getActiveSheet()->setCellValue('A'.$rowIndex, $item['no']);
            $phpexcel->getActiveSheet()->setCellValue('B'.$rowIndex, $item['specification']);
            $phpexcel->getActiveSheet()->setCellValue('C'.$rowIndex, $item['unit']);
            $phpexcel->getActiveSheet()->setCellValue('D'.$rowIndex, $item['qty']);
            $item['price'] = number_format($item['price'], 2, '.','');
            $phpexcel->getActiveSheet()->setCellValue('E'.$rowIndex, 'US$'.$item['price']);
            $item5 = number_format($item['qty']*$item['price'], 2, '.','');
            $phpexcel->getActiveSheet()->setCellValue('F'.$rowIndex, 'US$'.$item5);
            $sum += $item['qty']*$item['price'];
            //设置行高
            $phpexcel->getActiveSheet()->getRowDimension($rowIndex)->setRowHeight(44);
            $rowIndex++;
            $j++;
        }

        $sum = number_format($sum, 2, '.', '');
        //var_dump($rowIndex);exit();
        //如果数据少于行数
        if($softDataLen < 2){
            $phpexcel->getActiveSheet()->setCellValue('F'.($rowIndex+1), "US$".$sum);
        }else{
            $phpexcel->getActiveSheet()->setCellValue('F'.$rowIndex, "US$".$sum);
        }
        //导出属性设置
        //$date = str_replace("/","_",$date);
        $outputFileName = $filename."-发票.xlsx";
        ob_end_clean();//清除缓冲区,避免乱码
        $objWriter = new \PHPExcel_Writer_Excel2007($phpexcel, 'Excel2007');
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outputFileName);
        header("Content-Transfer-Encoding: binary");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }

    /**
     * 导出demo
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function exportAll()
    {
        //获取年月和考勤天数
        $date = date('Ymd-His', time());
        //$days = $this->_GET('days');

        //require_once("Classes/PHPExcel.php");
        //include("Classes/PHPExcel/IOFactory.php");
        Vendor('phpexcel.PHPExcel');
        Vendor('phpexcel.PHPExcel.IOFactory');
        Vendor('phpexcel.PHPExcel.Reader.Excel5');
        Vendor('phpexcel.PHPExcel.Writer.Excel2007');
        $temPath = ROOT_PATH.'public'.DS."model".DS."model.xls";
        //var_dump($temPath);exit();
        //检查文件路径
        if(!file_exists($temPath)){
            return $this->error('模板不存在');
        }
        //加载模板
        $phpCreate =  \PHPExcel_IOFactory::createReader("Excel5");
        //var_dump($phpCreate);exit();
        $phpexcel = $phpCreate->load($temPath);
        //var_dump($phpCreate);exit();
        //数据
        //$groupMember  = Db::table('admin')->field('adId,username,surname,password,createTime,status')->order('adId asc')->select();
        $groupMember = array(
            '0'=>array(
                '1',
                'test1',
                'pcs',
                '0.2',
                '18.00'
            ),
            '1'=>array(
                '2',
                'test1',
                'pcs',
                '200',
                '18.00'
            ),
            '2'=>array(
                '3',
                'test1',
                'pcs',
                '200',
                '18.00'
            ),
            '3'=>array(
                '4',
                'test1',
                'pcs',
                '200',
                '18.00'
            )
        );
        $rowIndex = 14;
        $len = count($groupMember);
        //数据超过3行，需要
        if($len > 2){
            //分离单元格
            //$phpexcel->getActiveSheet()->unmergeCells('A16:F16');
            $phpexcel->getSheet()->insertNewRowBefore(16, $len-2);
        }
        //var_dump($groupMember);exit();
        //填充可写的数据
        $sum = 0;
        foreach($groupMember as $k=>$item){
            //一览表写入数据
            $phpexcel->getActiveSheet()->setCellValue('A'.$rowIndex, $item['0']);
            $phpexcel->getActiveSheet()->setCellValue('B'.$rowIndex, $item['1']);
            $phpexcel->getActiveSheet()->setCellValue('C'.$rowIndex, $item['2']);
            $phpexcel->getActiveSheet()->setCellValue('D'.$rowIndex, $item['3']);
            $phpexcel->getActiveSheet()->setCellValue('E'.$rowIndex, 'US$'.$item['4']);
            $item4 = number_format($item['3']*$item['4'], 2, '.','');
            $phpexcel->getActiveSheet()->setCellValue('F'.$rowIndex, 'US$'.$item4);
            $sum += $item['3']*$item['4'];
            //设置行高
            $phpexcel->getActiveSheet()->getRowDimension($rowIndex)->setRowHeight(24);
            $rowIndex++;
        }
        //合并单元格
        //$phpexcel->getActiveSheet()->mergeCells('A18:E22');
        //新的一行数据
        if($len > 2){
            $phpexcel->getSheet()->insertNewRowBefore(19+$len-3, $len-2);
        }
        $rowIndex = $rowIndex+1;
        foreach($groupMember as $k=>$item){
            $phpexcel->getActiveSheet()->setCellValue('A'.$rowIndex, $item['0']);
            $phpexcel->getActiveSheet()->setCellValue('B'.$rowIndex, $item['1']);
            $phpexcel->getActiveSheet()->setCellValue('C'.$rowIndex, $item['2']);
            $phpexcel->getActiveSheet()->setCellValue('D'.$rowIndex, $item['3']);
            $phpexcel->getActiveSheet()->setCellValue('E'.$rowIndex, 'US$'.$item['4']);
            $item5 = number_format($item['3']*$item['4'], 2, '.','');
            $phpexcel->getActiveSheet()->setCellValue('F'.$rowIndex, 'US$'.$item5);
            $sum += $item['3']*$item['4'];
            //设置行高
            $phpexcel->getActiveSheet()->getRowDimension($rowIndex)->setRowHeight(24);
            $rowIndex++;
        }

        //如果数据少于行数，需要删除多余的行
        $sum = number_format($sum, 2, '.', '');
        //var_dump($rowIndex);exit();
        $phpexcel->getActiveSheet()->setCellValue('F'.$rowIndex, "US$".$sum);
        //导出属性设置
        $date = str_replace("/","_",$date);
        $outputFileName = "发票".$date.".xlsx";
        //require_once("Classes/PHPExcel/Writer/Excel2007.php");
        ob_end_clean();//清除缓冲区,避免乱码
        $objWriter = new \PHPExcel_Writer_Excel2007($phpexcel, 'Excel2007');
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outputFileName);
        header("Content-Transfer-Encoding: binary");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }

    /**
     * 导入excel--未实现
     * @param $file
     * @return array
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function importExecl($file){
        if(!file_exists($file)){
            return array("error"=>0,'message'=>'file not found!');
        }
        $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        try{
            $PHPReader = $objReader->load($file);
        }catch(\Exception $e){}
        if(!isset($PHPReader)) return array("error"=>0,'message'=>'read error!');
        $allWorksheets = $PHPReader->getAllSheets();
        $i = 0;
        foreach($allWorksheets as $objWorksheet){
            $sheetname=$objWorksheet->getTitle();
            $allRow = $objWorksheet->getHighestRow();//how many rows
            $highestColumn = $objWorksheet->getHighestColumn();//how many columns
            $allColumn = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            $array[$i]["Title"] = $sheetname;
            $array[$i]["Cols"] = $allColumn;
            $array[$i]["Rows"] = $allRow;
            $arr = array();
            $isMergeCell = array();
            foreach ($objWorksheet->getMergeCells() as $cells) {//merge cells
                foreach (\PHPExcel_Cell::extractAllCellReferencesInRange($cells) as $cellReference) {
                    $isMergeCell[$cellReference] = true;
                }
            }
            for($currentRow = 1 ;$currentRow<=$allRow;$currentRow++){
                $row = array();
                for($currentColumn=0;$currentColumn<$allColumn;$currentColumn++){;
                    $cell =$objWorksheet->getCellByColumnAndRow($currentColumn, $currentRow);
                    $afCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn+1);
                    $bfCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn-1);
                    $col = \PHPExcel_Cell::stringFromColumnIndex($currentColumn);
                    $address = $col.$currentRow;
                    $value = $objWorksheet->getCell($address)->getValue();
                    if(substr($value,0,1)=='='){
                        return array("error"=>0,'message'=>'can not use the formula!');
                        exit;
                    }
                    if($cell->getDataType() == \PHPExcel_Cell_DataType::TYPE_NUMERIC){
                        $cellstyleformat=$cell->getParent()->getStyle( $cell->getCoordinate() )->getNumberFormat();
                        $formatcode=$cellstyleformat->getFormatCode();
                        if (preg_match('/^([$[A-Z]*-[0-9A-F]*])*[hmsdy]/i', $formatcode)) {
                            $value = gmdate("Y-m-d", \PHPExcel_Shared_Date::ExcelToPHP($value));
                        }else{
                            $value = \PHPExcel_Style_NumberFormat::toFormattedString($value,$formatcode);
                        }
                    }
                    if($isMergeCell[$col.$currentRow]&&$isMergeCell[$afCol.$currentRow]&&!empty($value)){
                        $temp = $value;
                    }elseif($isMergeCell[$col.$currentRow]&&$isMergeCell[$col.($currentRow-1)]&&empty($value)){
                        $value=$arr[$currentRow-1][$currentColumn];
                    }elseif($isMergeCell[$col.$currentRow]&&$isMergeCell[$bfCol.$currentRow]&&empty($value)){
                        $temp = $value;
                    }
                    $row[$currentColumn] = $value;
                }
                $arr[$currentRow] = $row;
            }
            $array[$i]["Content"] = $arr;
            $i++;
        }
        spl_autoload_register(array('Think','autoload'));//must, resolve ThinkPHP and PHPExcel conflicts
        unset($objWorksheet);
        unset($PHPReader);
        unset($PHPExcel);
        unlink($file);
        return array("error"=>1,"data"=>$array);
    }

    ////////////////////////////////////分割线////////////////////////////////////////////////////////////////////////////////
    /**
     * 导出EXCEL
     * date:2016-10-31
     * author:yuan
     */
    public function exp(){//导出Excel
        $time = time();
        $xlsName  = "用户信息".$time;
        $header = ["用户编号","用户名","用户姓名","密码","创建日期"];
        $xlsCell  = array(
            array('adId'),
            array('username'),
            array('surname'),
            array('password'),
            array('createTime'),
            array('status')
        );
        $xlsData  = Db::table('admin')->field('adId,username,surname,password,createTime')->order('adId asc')->select();
        //时间戳转日期格式
        /*var_dump($xlsData);exit();
        foreach($xlsData as $key=>$val){
            $xlsData[$key]['createTime'] = date('Y-m-d H:i:s', $xlsData[$key]['createTime']);
        }*/
        $this->exportExcel($xlsData, $header, $xlsName);
    }
    /**
     * @creator Jimmy
     * @data 2018/1/05
     * @desc 数据导出到excel(csv文件)
     * @param $filename 导出的csv文件名称 如date("Y年m月j日").'-test.csv'
     * @param array $tileArray 所有列名称
     * @param array $dataArray 所有列数据
     */
    public  function exportToExcel($filename, $tileArray=[], $dataArray=[]){
        ini_set('memory_limit','512M');
        ini_set('max_execution_time',0);
        ob_end_clean();
        ob_start();
        header("Content-Type: text/csv");
        header("Content-Disposition:filename=".$filename.".xlsx");
        $fp=fopen('php://output','w');
        fwrite($fp, chr(0xEF).chr(0xBB).chr(0xBF));//转码 防止乱码(比如微信昵称(乱七八糟的))
        fputcsv($fp,$tileArray);
        $index = 0;
        foreach ($dataArray as $item) {
            if($index==1000){
                $index=0;
                ob_flush();
                flush();
            }
            $index++;
            fputcsv($fp,$item);
        }

        ob_flush();
        flush();
        ob_end_clean();
    }


}