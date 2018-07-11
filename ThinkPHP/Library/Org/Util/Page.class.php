<?php

// +----------------------------------------------------------------------

// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------

// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.

// +----------------------------------------------------------------------

// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )

// +----------------------------------------------------------------------

// | Author: liu21st <liu21st@gmail.com>

// +----------------------------------------------------------------------

// $Id: Page.class.php 2806 2012-03-08 03:21:38Z liu21st $



class Page {

    // 分页栏每页显示的页数

    public $rollPage = 5;

    // 页数跳转时要带的参数

    public $parameter  ;

    // 默认列表每页显示行数

    public $listRows = 20;

    // 起始行数

    public $firstRow	;

    // 分页总页面数

    protected $totalPages  ;

    // 总行数

    protected $totalRows  ;

    // 当前页数

    protected $nowPage    ;

    // 分页的栏的总页数

    protected $coolPages   ;

    // 分页显示定制

    protected $config  =	array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>' &nbsp;  &nbsp; 首 页 &nbsp; ','last'=>' &nbsp; 末 页 &nbsp; ','theme'=>'共 %totalRow% %header% %nowPage%/%totalPage% 页 %first% %upPage%  %prePage%  %linkPage%  %nextPage% %downPage% %end%');

	

	//protected $myconfig  =	array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>' &nbsp; 首 页 &nbsp; ','last'=>' &nbsp; 末 页 &nbsp; ','theme'=>'<div class="pagenum"> 共 %totalRow% %header%，当前第%nowPage% / %totalPage% 页，每页显示 %listRows% 条记录 </div><div  class="listpage"> %first% %upPage%  %prePage%  %linkPage%  %nextPage% %downPage% %end% <div class="page">第 <input  name="p" type="text" value=" curpage" /> 页 </div> <a href="javascript:void(0);" onclick="javascript:window.location=\'%url%&p=\'+document.form.p.value;+\'\'">跳转</a> </div>');
    protected $myconfig  =	array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>' &nbsp; 首 页 &nbsp; ','last'=>' &nbsp; 末 页 &nbsp; ','theme'=>'<div class="pagenum"> 共 %totalRow% %header%，当前第%nowPage% / %totalPage% 页，每页显示 %listRows% 条 </div><div  class="listpage"> %first% %upPage%  %prePage%  %linkPage%  %nextPage% %downPage% %end% </div>');

    // 默认分页变量名

    protected $varPage;



    /**

     +----------------------------------------------------------

     * 架构函数

     +----------------------------------------------------------

     * @access public

     +----------------------------------------------------------

     * @param array $totalRows  总的记录数

     * @param array $listRows  每页显示记录数

     * @param array $parameter  分页跳转的参数

     +----------------------------------------------------------

     */

    public function __construct($totalRows,$listRows='',$parameter='',$nowPages=1) {

        $this->totalRows = $totalRows;

        $this->parameter = $parameter;

        $this->varPage = C('VAR_PAGE') ? C('VAR_PAGE') : 'p' ;

        if(!empty($listRows)) {

            $this->listRows = intval($listRows);

        }

        $this->totalPages = ceil($this->totalRows/$this->listRows);     //总页数

        $this->coolPages  = ceil($this->totalPages/$this->rollPage);

        $this->nowPage  = !empty($_GET[$this->varPage])?intval($_GET[$this->varPage]):$nowPages;

        if(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {

            $this->nowPage = $this->totalPages;

        }

        $this->firstRow = $this->listRows*($this->nowPage-1);

    }



    public function setConfig($name,$value) {

        if(isset($this->config[$name])) {

            $this->config[$name]    =   $value;

        }

    }



    /**

     +----------------------------------------------------------

     * 原始分页显示输出

     +----------------------------------------------------------

     * @access public

     +----------------------------------------------------------

     */

    public function show() {

        if(0 == $this->totalRows) return '';

        $p = $this->varPage;

        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);

        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;

        $parse = parse_url($url);

        if(isset($parse['query'])) {

            parse_str($parse['query'],$params);

            unset($params[$p]);

            $url   =  $parse['path'].'?'.http_build_query($params);

        }

        //上下翻页字符串

        $upRow   = $this->nowPage-1;

        $downRow = $this->nowPage+1;

        if ($upRow>0){

            $upPage="<a href='".$url."&".$p."=$upRow'>".$this->config['prev']."</a>";

        }else{

            $upPage="";

        }



        if ($downRow <= $this->totalPages){

            $downPage="<a href='".$url."&".$p."=$downRow'>".$this->config['next']."</a>";

        }else{

            $downPage="";

        }

        // << < > >>

        if($nowCoolPage == 1){

            $theFirst = "";

            $prePage = "";

        }else{

            $preRow =  $this->nowPage-$this->rollPage;

            $prePage = "<a href='".$url."&".$p."=$preRow' >上".$this->rollPage."页</a>";

            $theFirst = "<a href='".$url."&".$p."=1' >".$this->config['first']."</a>";

        }

        if($nowCoolPage == $this->coolPages){

            $nextPage = "";

            $theEnd="";

        }else{

            $nextRow = $this->nowPage+$this->rollPage;

            $theEndRow = $this->totalPages;

            $nextPage = "<a href='".$url."&".$p."=$nextRow' >下".$this->rollPage."页</a>";

            $theEnd = "<a href='".$url."&".$p."=$theEndRow' >".$this->config['last']."</a>";

        }

        // 1 2 3 4 5

        $linkPage = "";

        for($i=1;$i<=$this->rollPage;$i++){

            $page=($nowCoolPage-1)*$this->rollPage+$i;

            if($page!=$this->nowPage){

                if($page<=$this->totalPages){

                    $linkPage .= "&nbsp;<a href='".$url."&".$p."=$page'>&nbsp;".$page."&nbsp;</a>";

                }else{

                    break;

                }

            }else{

                if($this->totalPages != 1){

                    $linkPage .= "&nbsp;<dt class='current'>".$page."</dt>";

                }

            }

        }

        $pageStr	 =	 str_replace(

            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),

            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);

        return $pageStr;

    }

	

	

	/**

     +----------------------------------------------------------

     * 分页显示输出

     +----------------------------------------------------------

     * @access public

     +----------------------------------------------------------

     */

    public function NewShow() {

        if(0 == $this->totalRows) return '';

        $p = $this->varPage;

        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);

		$nowCoolPage2     = ceil($this->rollPage/$this->nowPage);

		

        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;

        $parse = parse_url($url);

        if(isset($parse['query'])) {

            parse_str($parse['query'],$params);

            unset($params[$p]);

            $url   =  $parse['path'].'?'.http_build_query($params);

        }

        //上下翻页字符串

        $upRow   = $this->nowPage-1;

        $downRow = $this->nowPage+1;

        if ($upRow>0){

			$theFirst = "<a href='".$url."&".$p."=1' >".$this->config['first']."</a>";

            $upPage="<a class='pageup' href='".$url."&".$p."=$upRow'>".$this->config['prev']."</a>";

        }else{

			$theFirst = "<a href='#' >".$this->config['first']."</a>";

            $upPage="<a class='pageup' href='#'>".$this->config['prev']."</a>";

        }



        if ($downRow <= $this->totalPages){

			$theEndRow = $this->totalPages;

            $downPage="<a class='nextpage' href='".$url."&".$p."=$downRow'>".$this->config['next']."</a>";

			$theEnd = "<a href='".$url."&".$p."=$theEndRow' >".$this->config['last']."</a>";

        }else{

            $downPage="<a class='nextpage' href='#'>".$this->config['next']."</a>";

			$theEnd = "<a href='#' >".$this->config['last']."</a>";

       }

        

		// 1 2 3 4 5

        $linkPage = "";

        for($i=1;$i<=$this->rollPage;$i++){

            $page=($nowCoolPage-1)*$this->rollPage+$i;

            if($page!=$this->nowPage){

                if($page<=$this->totalPages){

                    $linkPage .= "&nbsp;<a href='".$url."&".$p."=$page'>&nbsp;".$page."&nbsp;</a>";

                }else{

                    break;

                }

            }else{

                //if($this->totalPages != 1){

                    $linkPage .= "&nbsp;<a class='hot'>".$page."</a>";

                //}

            }

        }

        $pageStr	 =	 str_replace(

            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),

            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);

        return $pageStr;

    }

	

	

	/**

     +----------------------------------------------------------

     * 网站总后台分页显示输出

     +----------------------------------------------------------

     * @access public

     +----------------------------------------------------------

     */

    public function MyShow() {

        if(0 == $this->totalRows) return '';

        $p = $this->varPage;

        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);

		$nowCoolPage2     = ceil($this->rollPage/$this->nowPage);

		

        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;

        $parse = parse_url($url);

        if(isset($parse['query'])) {

            parse_str($parse['query'],$params);

            unset($params[$p]);

            $url   =  $parse['path'].'?'.http_build_query($params);

        }

        //上下翻页字符串

        $upRow   = $this->nowPage-1;

        $downRow = $this->nowPage+1;

        if ($upRow>0){

			$theFirst = "<a href='".$url."&".$p."=1' class='pageup' >".$this->myconfig['first']."</a>";

            $upPage="<a class='pageup' href='".$url."&".$p."=$upRow'>".$this->myconfig['prev']."</a>";

        }else{

			$theFirst = "<a href='#' class='pageup' >".$this->myconfig['first']."</a>";

            $upPage="<a class='pageup' href='#'>".$this->myconfig['prev']."</a>";

        }



        if ($downRow <= $this->totalPages){

			$theEndRow = $this->totalPages;

            $downPage="<a class='nextpage' href='".$url."&".$p."=$downRow'>".$this->myconfig['next']."</a>";

			$theEnd = "<a href='".$url."&".$p."=$theEndRow'  class='nextpage'>".$this->myconfig['last']."</a>";

        }else{

            $downPage="<a class='nextpage' href='#'>".$this->myconfig['next']."</a>";

			$theEnd = "<a href='#'  class='nextpage'>".$this->myconfig['last']."</a>";

       }

        

		// 1 2 3 4 5

        $linkPage = "";

        for($i=1;$i<=$this->rollPage;$i++){

            $page=($nowCoolPage-1)*$this->rollPage+$i;

            if($page!=$this->nowPage){

                if($page<=$this->totalPages){

                    $linkPage .= "&nbsp;<a href='".$url."&".$p."=$page'>&nbsp;".$page."&nbsp;</a>";

                }else{

                    break;

                }

            }else{

                //if($this->totalPages != 1){

                    $linkPage .= "&nbsp;<a class='hot'>".$page."</a>";$curpage = $page;

                //}

            }

        }

		

		switch ($this->listRows) {

			case 30:

				$select = '<select name="listRows" onchange="form.submit()"><option value="15">15</option><option value="30" selected>30</option><option value="50">50</option></select>';

				break;

			case 50:

				$select = '<select name="listRows" onchange="form.submit()"><option value="15">15</option><option value="30">30</option><option value="50" selected>50</option></select>';

				break;

			default:

				$select = '<select name="listRows" onchange="form.submit()"><option value="15" selected>15</option><option value="30">30</option><option value="50">50</option></select>';

				break;

		}

		

        $pageStr	 =	 str_replace(

            array('curpage','%listRows%','%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),

            array($curpage,$select,$this->myconfig['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->myconfig['theme']);

        return $pageStr;

    }





    /**

    +----------------------------------------------------------

     * 网站总后台分页显示输出

    +----------------------------------------------------------

     * @access public

    +----------------------------------------------------------

     */

    public function PageShow() {

        if(0 == $this->totalRows) return '';

        $p = $this->varPage;

        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);

        $nowCoolPage2     = ceil($this->rollPage/$this->nowPage);



        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;

        $parse = parse_url($url);

        if(isset($parse['query'])) {

            parse_str($parse['query'],$params);

            unset($params[$p]);

            $url   =  $parse['path'].'?'.http_build_query($params);

        }

        //上下翻页字符串

        $upRow   = $this->nowPage-1;

        $downRow = $this->nowPage+1;

        if ($upRow>0){

            $theFirst = "<a href='".$url."&".$p."=1' class='pageup' >".$this->myconfig['first']."</a>";

            $upPage="<a class='pageup' href='".$url."&".$p."=$upRow'>".$this->myconfig['prev']."</a>";

        }else{

            $theFirst = "<a href='#' class='pageup' >".$this->myconfig['first']."</a>";

            $upPage="<a class='pageup' href='#'>".$this->myconfig['prev']."</a>";

        }



        if ($downRow <= $this->totalPages){

            $theEndRow = $this->totalPages;

            $downPage="<a class='nextpage' href='".$url."&".$p."=$downRow'>".$this->myconfig['next']."</a>";

            $theEnd = "<a href='".$url."&".$p."=$theEndRow'  class='nextpage'>".$this->myconfig['last']."</a>";

        }else{

            $downPage="<a class='nextpage' href='#'>".$this->myconfig['next']."</a>";

            $theEnd = "<a href='#'  class='nextpage'>".$this->myconfig['last']."</a>";

        }



        // 1 2 3 4 5

        $linkPage = "";

        for($i=1;$i<=$this->rollPage;$i++){

            $page=($nowCoolPage-1)*$this->rollPage+$i;

            if($page!=$this->nowPage){

                if($page<=$this->totalPages){

                    $linkPage .= "&nbsp;<a href='".$url."&".$p."=$page'>&nbsp;".$page."&nbsp;</a>";

                }else{

                    break;

                }

            }else{

                //if($this->totalPages != 1){

                $linkPage .= "&nbsp;<a class='hot'>".$page."</a>";$curpage = $page;

                //}

            }

        }



        $select = $this->listRows;

        $pageStr	 =	 str_replace(

            array('curpage','%listRows%','%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%','%url%'),

            array($curpage,$select,$this->myconfig['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd,$url),$this->myconfig['theme']);

        return $pageStr;

    }


    /**

    +----------------------------------------------------------

     * 前台接口输出

    +----------------------------------------------------------

     * @access public

    +----------------------------------------------------------

     */

    public function InterFacePageShow() {

        if(0 == $this->totalRows) return '';

        $p = $this->varPage;

        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;

        $parse = parse_url($url);

        if(isset($parse['query'])) {

            parse_str($parse['query'],$params);

            unset($params[$p]);

            $url   =  $parse['path'].'?'.http_build_query($params);

        }

        //上下翻页字符串

        $upRow   = $this->nowPage-1;

        $downRow = $this->nowPage+1;



        if ($downRow <= $this->totalPages){

            $downPage=$url."&".$p."=$downRow";


        }

        $nextPage = str_replace("/gtlisten/","http://iphone.dooland.com/gtlisten/",$downPage);
        return $nextPage;

    }

}