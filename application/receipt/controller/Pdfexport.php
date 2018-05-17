<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/17
 * Time: 10:12
 */
namespace app\receipt\controller;

class Pdfexport extends Base{

    public function exportPdf($profomaData, $plasticData, $softwareData, $filename){
        /*var_dump($profomaData);
        var_dump($plasticData);
        var_dump($softwareData);exit();*/
        $date = date('Ymd-His', time());
        Vendor('phpexcel.PHPExcel');
        Vendor('phpexcel.PHPExcel.IOFactory');
        Vendor('phpexcel.PHPExcel.Reader.Excel5');
        //Vendor('phpexcel.PHPExcel.Writer.Excel2007');
        Vendor('phpexcel.PHPExcel.Writer.PDF');
        Vendor('phpexcel.PHPExcel.Writer.PDF.mPDF');
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
        $outputFileName = $filename."-发票". "_" . date('Ymd') . ".pdf";
        ob_end_clean();//清除缓冲区,避免乱码

        /*DomPDF*/

        $rendererName = \PHPExcel_Settings::PDF_RENDERER_MPDF;
        //$rendererLibrary = 'domPDF0.6.0beta3';
        //$rendererLibraryPath = ROOT_PATH.'public'.DS.'img';
        $rendererLibraryPath = 'E:\web-linux-20180129\linux\web0111\vendor\phpexcel\PHPExcel\Writer\PDF\mPDF.php';
        //  Here's the magic: you __tell__ PHPExcel what rendering engine to use
        //     and where the library is located in your filesystem
        if (!\PHPExcel_Settings::setPdfRenderer(
            $rendererName,
            $rendererLibraryPath
        )) {
            die('NOTICE: Please set the $rendererName and $rendererLibraryPath values' .
                '<br />' .
                'at the top of this script as appropriate for your directory structure'
            );
        }


        $objWriter = \PHPExcel_IOFactory::createWriter($phpexcel, 'PDF');

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment;filename="01simple.pdf"');
        header('Cache-Control: max-age=0');
        header("Content-Disposition:attachment;filename=".$outputFileName);
        $objWriter->save("php://output");
    }
}