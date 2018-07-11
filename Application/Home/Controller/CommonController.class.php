<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {
	 //是否登录
	 public function  __construct()
	 {
		 parent::__construct();
		 $login = get_admin_name();
		 if ( ! $login)
           redirect(U('Login/index'));
		 else
			$this->assign('users',get_admin_tname());
		    $this->assign('userid',get_admin_id());

    }
	/**
	 * 导入数据方法
	 * return  array
	 */
	protected function questionsImportAction($filename, $exts = 'xls')
	{
		//导入PHPExcel类库
		import("Org.Util.PHPExcel");
		$PHPExcel = new \PHPExcel();
		//如果excel文件后缀名为.xls，导入这个类
		if ($exts == 'xls')
		{
			import("Org.Util.PHPExcel.Reader.Excel5");
			$PHPReader = new \PHPExcel_Reader_Excel5();
		}
		else if($exts == 'xlsx')
		{
			import("Org.Util.PHPExcel.Reader.Excel2007");
			$PHPReader = new \PHPExcel_Reader_Excel2007();
		}


		//载入文件
		$PHPExcel     = $PHPReader->load($filename);
		$currentSheet = $PHPExcel->getSheet(0); //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
		$allColumn    = $currentSheet->getHighestColumn(); //获取总列数
		$allRow       = $currentSheet->getHighestRow();//获取总行数
		$data         = array();
		//循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
		for ($currentRow = 1;$currentRow <= $allRow;$currentRow++)
		{
			//从哪列开始，A表示第一列
			for ($currentColumn = 'A';$currentColumn <= $allColumn;$currentColumn++)
			{
				$address             = $currentColumn.$currentRow;    //数据坐标
				$data[$currentRow][] = $currentSheet->getCell($address)->getValue();

				if($data[$currentRow] instanceof PHPExcel_RichText)
				{
					$data[$currentRow]  = $cell->__toString();
				}
			}
		}
		return $data;
	}
	/**
	 * 导出数据方法
	 * @$fileName 文件名
	 * @$headArr 表头
	 * @string $data
	 * return  array
	 */
	protected  function getExcels($fileName,$headArr,$data)
	{
		//导入PHPExcel类库
		import("Org.Util.PHPExcel");
		import("Org.Util.PHPExcel.Writer.Excel5");
		import("Org.Util.PHPExcel.IOFactory.php");
		$date        = date("Y_m_d",time());
		$fileName    .= "_{$date}.xls";
		//创建PHPExcel对象，注意，不能少了\
		$objPHPExcel = new \PHPExcel();
		$objProps    = $objPHPExcel->getProperties();
		$key         = ord("A");	//设置表头

		foreach($headArr as $v)
		{
			$colum = chr($key);
			$objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
			$objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
			$key += 1;
		}

		$column = 2;
		$objActSheet = $objPHPExcel->getActiveSheet();

		foreach($data as $key => $rows)
		{ //行写入
			$span = ord("A");
			foreach($rows as $keyName=>$value)
			{// 列写入
				$j = chr($span);
				$objActSheet->setCellValue($j.$column, $value);
				$span++;
			}
			$column++;
		}
		$fileName = iconv("utf-8", "gb2312", $fileName);

		//重命名表
		//$objPHPExcel->getActiveSheet()->setTitle('test');
		//设置活动单指数到第一个表,所以Excel打开这是第一个表
		$objPHPExcel->setActiveSheetIndex(0);
		ob_end_clean();//清除缓冲区,避免乱码
		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment;filename=\"$fileName\"");
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output'); //文件通过浏览器下载
		exit;
	}
}
