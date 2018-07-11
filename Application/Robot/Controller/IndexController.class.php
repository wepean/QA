<?php
namespace Robot\Controller;
use Think\Controller;
class IndexController extends Controller
{
    var $onOff;
    var $articleOnOff;
    /**
     * 初始化
     */
    public function _initialize()
    {
        set_time_limit(0);
        $sets = M('RobotSet')->getField('status',true);
        //自学开关是否已经启动
        $this->onOff        = $sets[0]; //杂志版
        $this->articleOnOff = $sets[1]; //文章版
    }
    /**
     * （杂志版）机器自动学习--知识咨询
     * @access public
     * @param  mixed $data 要返回的数据
     * @author wepean<2050301456@qq.com>
     * @return
     */
    public function robotStudy()
    {
        if ( $this->onOff == '-1')  return false;

        $url    = 'http://www.ucdrs.net/admin/union/getQA.do'; //资料来源：全国图书馆参考咨询联盟
        $cinfo  = array();
        $tday   = date('Y-m-d',strtotime('yesterday'));  //昨天
        $times  = time();
        //知识咨询
        for ( $i = 1;$i <= 4; $i++ )
        {
            $arr            = array('pageNo'=>$i);
            $consultantInfo = curl_https_post($url,$arr);
            if ( empty($consultantInfo) )
            {
                for ( $i=0;$i<=4;$i++ )
                {
                    $consultantInfo = curl_https_get($url);
                    if ( ! empty($consultantInfo) ) break;
                }

            }

            if ( $consultantInfo )
            {
                foreach ( $consultantInfo as $va => $ke )
                {
                    $time  = $ke["回复时间"];
                    $ntime = find_num($time);
                    $tdayi = date('Y-m-d',substr($ntime,0,-3));
                    if ( $tdayi == $tday )
                    {
                        $cinfo[$i][$va]["question_title"] = str_filter($ke["提问标题"]); //安全过滤
                        $cinfo[$i][$va]["consultants"]    = str_filter($ke["提问人名称"]);//安全过滤
                        $cinfo[$i][$va]["add_time"]       = $times;
                        $cinfo[$i][$va]["is_robot_learning"] = 1;      //1机器人
                    }
                    else
                    {
                        break;
                    }
                }
            }
            else
                break;
        }

        if ( $cinfo )
        {
            //机器自己学习问题并获取答案，完成后返回答案
            $qlist        = M('QuestionList');
            $questions    = M('QuestionAnswer');
            $getWords     = A('Api/Index');  //调用Api模块方法
            $questionType = $getWords->getQuestionType();  //分类库

            foreach (  $cinfo as $k => $v )
            {
                foreach ( $v as $ke => $va )
                {
                    $schkey =  $va['question_title'];
                    if ( $schkey )
                    {
//相似问题是否存在若已经存在，则不会再录入
                        $qres   = $qlist->field('id')
                                        ->where('POSITION("' . $schkey . '" IN question_title) OR question_title="' .$schkey. '"')
                                       // ->where('POSITION("' . $schkey . '" IN question_title) OR question_title like "' . $schkey . '%" OR question_title like "%' . $schkey . '" OR question_title="' .$schkey. '"')
                                        ->find();
                        if ( ! $qres )
                        {
                            $qdata[$ke]['add_time']       = $times;
                            $qdata[$ke]['question_title'] = $schkey;
                            $qdata[$ke]['consultanter']   = $va['consultants'];  //咨询人
                            //自动获取分类
                            $words        = $getWords->getWords($schkey); //百度分析词句
                            $magaSortId   = $strBodyi = '';
                            if ( $questionType )
                            {
                                foreach ( $questionType as $ki => $vi )
                                {
                                    foreach ( $words as $item )
                                    {
                                        if ( stripos('"'.$item.'"',$vi['type_name']) OR '"'.$item.'"' == $vi['type_name']) $magaSortId = $v['id'];
                                    }
                                }
                            }
                            $magaSortIdi  = $magaSortId ? $magaSortId : 2869;  //2869表 其它 ID
                            $qdata[$ke]['type_id'] = $magaSortIdi;
                            $qdata[$ke]['is_robot_learning'] = 1;    //是机器学习，-1不是
                            $qid = $qlist->filter('strip_tags')->add($qdata[$ke]);   //问题入库
                            if ( $qid )
                            {
                                $estr   = '1';
                                //接口获取答案
                                $urls    = "http://public.dooland.com/v1/QAMagazine/getMagazineResult/q/";
                                $content = curl_https_get($urls.$schkey);
                                $datas[$ke]['question_id']       = $qid;
                                $datas[$ke]['question_title']    = $schkey;
                                $datas[$ke]['add_time']          = $times;
                                $datas[$ke]['type_id']           = $magaSortIdi;
                                $datas[$ke]['is_robot_learning'] = 1;      //是机器学习，-1不是
                                if ($content['status'] == 1)
                                {
                                    //杂志
                                    if ($content['data']['magaList'])
                                    {
                                        //判断是一维还是二维数组
                                        $arr = get_max_array($content['data']['magaList']);
                                        if ( $arr == 3)
                                        {
                                            foreach($content['data']['magaList'] as $ki => $vi)
                                            {
                                                foreach ($content['data']['magaList'][$ki] as $item)
                                                {
                                                    $strBodyi .= '<div  style="float:left;margin:2px;font-size:12px;"><div><img src="'.$item['pic_size2'].'" width="130" height="170"/></div><div style=" text-overflow:ellipsis; white-space:nowrap; overflow:hidden; margin:2px 0;color:#0009;max-width:130px"><a style="'.$item['id'].'">'.$item['title'].'</a></div></div>';
                                                }
                                            }
                                        }
                                        else
                                        {
                                            foreach($content['data']['magaList'] as $ki => $vi)
                                            {
                                                $strBodyi .= '<div  style="float:left;margin:2px;font-size:12px;"><div><img src="'.$vi['pic_size2'].'" width="130" height="170"/></div><div style=" text-overflow:ellipsis; white-space:nowrap; overflow:hidden; margin:2px 0;color:#0009;max-width:130px"><a style="'.$vi['id'].'">'.$vi['title'].'</a></div></div>';
                                            }
                                        }
                                    }
                                    //杂志
                                    if ( $content['data']['bookList'] )
                                    {
                                        //判断是一维还是二维数组
                                        $arri = get_max_array($content['data']['bookList']);

                                        if ( $arri == 3 )
                                        {
                                            foreach($content['data']['bookList'] as $ki => $vi)
                                            {
                                                foreach ( $content['data']['bookList'][$ki] as $item )
                                                {
                                                    $strBodyi .= '<div style="float:left;margin:2px;font-size:12px;"><div><img src="'.$item['pic'].'" width="130" height="170"/></div><div style=" text-overflow:ellipsis; white-space:nowrap; overflow:hidden; margin:2px 0;color:#0009;max-width:130px"><a style="'.$item['id'].'">'.$item['title'].'</a></div></div>';
                                                }
                                            }
                                        }
                                        else
                                        {
                                            foreach($content['data']['bookList'] as $ki => $vi)
                                            {
                                                $strBodyi .= '<div style="float:left;margin:2px;font-size:12px;"><div><img src="'.$vi['pic'].'" width="130" height="170"/></div><div style=" text-overflow:ellipsis; white-space:nowrap; overflow:hidden; margin:2px 0;color:#0009;max-width:130px"><a style="'.$vi['id'].'">'.$vi['title'].'</a></div></div>';
                                            }
                                        }

                                    }
                                    if ( $strBodyi ) $datas[$ke]['contents'] = $strBodyi;
                                }
                                $qkey = $questions->add($datas[$ke]); //答案入库，入库

                                if ( $qkey )
                                    $akey = '1';
                                else
                                    $akey = '-1';
                            }
                            else
                            {
                                $estr = '-1';
                            }

                            //写入日志
                            $this->robotStudyLog(1,$qid,$schkey,$estr,$akey);
                        }
                        else
                            $this->robotStudyLog(1,$qres['id'],$schkey);
                    }
                }
            }
        }
        else
        {
            $this->robotStudyLog(1);
        }
    }
    /**
     * （文章版）机器自动学习--知识咨询
     * @access public
     * @param  mixed $data 要返回的数据
     * @author wepean<2050301456@qq.com>
     * @return
     */
    public function articleRobotStudy()
    {
        if ( $this->articleOnOff == '-1' )  return false;

        $url    = 'http://www.ucdrs.net/admin/union/getQA.do'; //资料来源：全国图书馆参考咨询联盟
        $cinfo  = array();
        $tday   = date('Y-m-d',strtotime('yesterday'));  //昨天
        $times  = time();
        //知识咨询
        for ( $i = 1;$i <= 4; $i++ )
        {
            $arr            = array('pageNo'=>$i);
            $consultantInfo = curl_https_post($url,$arr);
            //接口不稳定需要这步
            if ( empty($consultantInfo) )
            {
                for ( $i=0;$i<=4;$i++ )
                {
                    $consultantInfo = curl_https_get($url);
                    if ( ! empty($consultantInfo) ) break;
                }

            }
            if ( $consultantInfo )
            {
                foreach ( $consultantInfo as $va => $ke )
                {
                    $time  = $ke["回复时间"];
                    $ntime = find_num($time);
                    $tdayi = date('Y-m-d',substr($ntime,0,-3));
                    if ( $tdayi == $tday )
                    {
                        $cinfo[$i][$va]["question_title"] = str_filter($ke["提问标题"]); //安全过滤
                        $cinfo[$i][$va]["consultants"]    = str_filter($ke["提问人名称"]);//安全过滤
                        $cinfo[$i][$va]["add_time"]       = $times;
                        $cinfo[$i][$va]["is_robot_learning"] = 1;      //1机器人
                    }
                    else
                    {
                        break;
                    }
                }
            }
            else
                break;
        }

        if ( $cinfo )
        {
            //机器自己学习问题并获取答案，完成后返回答案
            $qlist        = M('ArticleQuestionList');
            $questions    = M('ArticleQuestionAnswer');
            $getWords     = A('Api/Index');  //调用Api模块方法
            $questionType = $getWords->getArticleQuestionType();  //分类库

            foreach (  $cinfo as $k => $v )
            {
                foreach ( $v as $ke => $va )
                {
                    $schkey =  $va['question_title'];
                    if ( $schkey )
                    {
                         //相似问题是否存在若已经存在，则不会再录入
                        $qres   = $qlist->field('id')
                                        ->where('MATCH(question_title) AGAINST("*' . $schkey . '*" IN BOOLEAN MODE)')
                                        ->find();
                        if ( ! $qres )
                        {
                            $qdata[$ke]['add_time']       = $times;
                            $qdata[$ke]['question_title'] = $schkey;
                            $qdata[$ke]['consultanter']   = $va['consultants'];  //咨询人
                            //自动获取分类
                            $words        = $getWords->getWords($schkey); //百度分析词句
                            $magaSortId   = '';
                            if ( $questionType )
                            {
                                foreach ( $questionType as $ki => $vi )
                                {
                                    foreach ( $words as $item )
                                    {
                                        if ( stripos('"'.$item.'"',$vi['type_name']) OR '"'.$item.'"' == $vi['type_name']) $magaSortId = $v['id'];
                                    }
                                }
                            }
                            $magaSortIdi                     = $magaSortId ? $magaSortId : 2760;  //2760表 其它 ID
                            $qdata[$ke]['is_robot_learning'] = 1;    //是机器学习，-1不是
                            $qdata[$ke]['ip']                     = get_client_ip();     //提问人IP
                            $qdata[$ke]['article_type_id']        = $magaSortIdi;
                            $qid = $qlist->filter('strip_tags')->add($qdata[$ke]);   //问题入库
                            if ( $qid )
                            {
                                $estr   = '1';
                                //接口获取答案
                                $urls    = "http://public.dooland.com/v1/QAArticle/getArticleResult/q/";
                                $content = curl_https_get($urls.$schkey);
                                $datas[$ke]['question_id']       = $qid;
                                $datas[$ke]['question_title']    = $schkey;
                                $datas[$ke]['add_time']          = $times;
                                $datas[$ke]['article_type_id']   = $magaSortIdi;
                                $datas[$ke]['is_robot_learning'] = 1;      //是机器学习，-1不是
                                $strs                            = array();
                                if ($content['status'] == 1)
                                {
                                    //判断是一维还是二维数组
                                    $arr = get_max_array($content['data']);
                                    if ( $arr == 3)
                                    {
                                        foreach($content['data'] as $k => $v)
                                        {
                                            foreach ($v as $item=>$ve)
                                            {
                                                $strs[]  = $v[$item];
                                            }
                                        }
                                    }
                                    else
                                    {
                                        foreach($content['data'] as $k => $v)
                                        {
                                            $strs[]  = $content['data'][$k];
                                        }
                                    }


                                    if ( $strs )  $datas[$ke]['contents'] = json_encode($strs);
                                }
                                $qkey = $questions->add($datas[$ke]); //答案入库，入库

                                if ( $qkey )
                                    $akey = '1';
                                else
                                    $akey = '-1';
                            }
                            else
                            {
                                $estr = '-1';
                            }

                            //写入日志
                            $this->robotStudyLog(2,$qid,$schkey,$estr,$akey);
                        }
                        else
                            $this->robotStudyLog(2,$qres['id'],$schkey);
                    }
                }
            }
        }
        else
        {
            $this->robotStudyLog(2);
        }
    }
    /**
     * (杂志版)机器自动学习--文献咨询
     * @access public
     * @param  mixed $data 要返回的数据
     * @author wepean<2050301456@qq.com>
     * @return
     */
    public function robotStudyMagBook()
    {
        if ( $this->onOff == '-1')  return false;

        $cinfo        = array();
        $tday         = date('m-d');
        $times        = time();
        $getWords     = A('Api/Index');   //调用Api模块方法
        $questionType = $getWords->getQuestionType();  //分类库
        $tkey         = '';
        $ttype        = M('QuestionType');
        //知识咨询
        for ( $i = 1;$i <= 2; $i++ )
        {
            $url = 'http://www.ucdrs.net/admin/union/index.do?t=json&type=repay&pages='.$i; //资料来源：全国图书馆参考咨询联盟
            $consultantInfo = curl_https_get($url);

            if ( empty($consultantInfo) )
            {
                for ( $i = 0;$i <= 4;$i++ )
                {
                    $consultantInfo = curl_https_get($url);
                    if ( ! empty($consultantInfo) ) break;
                }

            }

            if ( $consultantInfo )
            {
                foreach ( $consultantInfo as $va => $ke )
                {
                    $tdayi = substr($ke['audittime'],0,5);
                    if ( $tdayi == $tday )
                    {
                        $cinfo[$i][$va]["question_title"] = str_filter($ke["title"]); //安全过滤
                        $cinfo[$i][$va]["consultants"]    = $ke["ctime"];
                        $cinfo[$i][$va]["add_time"]       = $times;
                        $cinfo[$i][$va]["is_robot_learning"] = 1;      //1机器人
                        $ttid = $ttype->field('id')->where('type_name="' .$ke["type"]. '"')->find();
                        if ( $ttid )
                        {
                            $tkey = $ttid;
                        }
                        else
                        {
                            //没有的分类就录进去
                            $tdata[$va]['add_time']  = $times;
                            $tdata[$va]['type_name'] = $ke["type"];
                            $tdata[$va]['types']     = ($ke["type"] == '期刊') ? "1" : "-1";
                            $tdata[$va]['is_robot_learning'] = 1;
                            $tkey = $ttype->filter('strip_tags')->add($tdata[$va]);
                        }
                        $cinfo[$i][$va]["type_id"] = $tkey['id'] ? $tkey['id'] : 2869; //2869表 其它 ID
                    }
                    else
                    {
                        break;
                    }
                }
            }
            else
                break;
        }

        if ( $cinfo )
        {
            //机器自己学习问题并获取答案，完成后返回答案
            $qlist     = M('QuestionList');
            $questions = M('QuestionAnswer');
            foreach (  $cinfo as $k => $v )
            {
                foreach ( $v as $ke => $va )
                {
                    $schkey =  $va['question_title'];
                    if ( $schkey )
                    {
                        //相似问题是否存在若已经存在，则不会再录入
                        $qres   = $qlist->field('id')
                                        ->where('question_title="' .$schkey. '"')
                                        // ->where('POSITION("' . $schkey . '" IN question_title) OR question_title like "' . $schkey . '%" OR question_title like "%' . $schkey . '" OR question_title="' .$schkey. '"')
                                        ->find();
                        if ( ! $qres )
                        {
                            $qdata[$ke]['add_time']       = $times;
                            $qdata[$ke]['question_title'] = $schkey;
                            $qdata[$ke]['consultanter']   = $va['consultants'];
                            //自动获取分类
                            $words    = $getWords->getWords($schkey); //百度分析词句
                            $strBodyi = '';
                            $magaSortIdi = $va['type_id'];
                            $qdata[$ke]['type_id'] = $magaSortIdi;
                            $qdata[$ke]['is_robot_learning'] = 1;    //是机器学习，-1不是
                            $qid = $qlist->filter('strip_tags')->add($qdata[$ke]);
                            if ( $qid )
                            {
                                $estr   = '1';
                                //接口获取答案
                                $urls    = "http://public.dooland.com/v1/QAMagazine/getMagazineResult/q/";
                                $content = curl_https_get($urls.$schkey);
                                $datas[$ke]['question_id']       = $qid;
                                $datas[$ke]['question_title']    = $schkey;
                                $datas[$ke]['add_time']          = $times;
                                $datas[$ke]['type_id']           = $magaSortIdi;
                                $datas[$ke]['is_robot_learning'] = 1;      //是机器学习，-1不是
                               // $strBodyi                        = ($strBody[$ke]);
                                if ($content['status'] == 1)
                                {
                                    //杂志
                                    if ($content['data']['magaList'])
                                    {
                                        //判断是一维还是二维数组
                                        $arr = get_max_array($content['data']['magaList']);
                                        if ( $arr == 3)
                                        {
                                            foreach($content['data']['magaList'] as $ki => $vi)
                                            {
                                                foreach ($content['data']['magaList'][$ki] as $item)
                                                {
                                                    $strBodyi .= '<div  style="float:left;margin:2px;font-size:12px;"><div><img src="'.$item['pic_size2'].'" width="130" height="170"/></div><div style=" text-overflow:ellipsis; white-space:nowrap; overflow:hidden; margin:2px 0;color:#0009;max-width:130px"><a style="'.$item['id'].'">'.$item['title'].'</a></div></div>';
                                                }
                                            }
                                        }
                                        else
                                        {
                                            foreach($content['data']['magaList'] as $ki => $vi)
                                            {
                                                $strBodyi .= '<div  style="float:left;margin:2px;font-size:12px;"><div><img src="'.$vi['pic_size2'].'" width="130" height="170"/></div><div style=" text-overflow:ellipsis; white-space:nowrap; overflow:hidden; margin:2px 0;color:#0009;max-width:130px"><a style="'.$v['id'].'">'.$vi['title'].'</a></div></div>';
                                            }
                                        }
                                    }
                                    //杂志
                                    if ( $content['data']['bookList'] )
                                    {
                                        //判断是一维还是二维数组
                                        $arri = get_max_array($content['data']['bookList']);

                                        if ( $arri == 3 )
                                        {
                                            foreach($content['data']['bookList'] as $ki => $vi)
                                            {
                                                foreach ( $content['data']['bookList'][$ki] as $item )
                                                {
                                                    $strBodyi .= '<div style="float:left;margin:2px;font-size:12px;"><div><img src="'.$item['pic'].'" width="130" height="170"/></div><div style=" text-overflow:ellipsis; white-space:nowrap; overflow:hidden; margin:2px 0;color:#0009;max-width:130px"><a style="'.$item['id'].'">'.$item['title'].'</a></div></div>';
                                                }
                                            }
                                        }
                                        else
                                        {
                                            foreach($content['data']['bookList'] as $ki => $vi)
                                            {
                                                $strBodyi .= '<div style="float:left;margin:2px;font-size:12px;"><div><img src="'.$vi['pic'].'" width="130" height="170"/></div><div style=" text-overflow:ellipsis; white-space:nowrap; overflow:hidden; margin:2px 0;color:#0009;max-width:130px"><a style="'.$vi['id'].'">'.$v['title'].'</a></div></div>';
                                            }
                                        }

                                    }
                                    if ( $strBodyi ) $datas[$ke]['contents'] = $strBodyi;
                                }
                                $qkey = $questions->add($datas[$ke]); //答案入库，入库

                                if ( $qkey )
                                    $akey = '1';
                                else
                                    $akey = '-1';

                            }
                            else
                            {
                                $estr = '-1';
                            }

                            //写入日志
                            $this->robotStudyLog(1,$qid,$schkey,$estr,$akey);
                        }
                        else
                            $this->robotStudyLog(1,$qres['id'],$schkey);
                    }
                }
            }
        }
        else
        {
            $this->robotStudyLog(1);
        }

    }
    /**
     * (文章版)机器自动学习--文献咨询
     * @access public
     * @param  mixed $data 要返回的数据
     * @author wepean<2050301456@qq.com>
     * @return
     */
    public function articleRobotStudyMagBook()
    {
        //开关是否启动
        if ( $this->articleOnOff == '-1')  return false;

        $cinfo        = array();
        $tday         = date('m-d');
        $times        = time();
        $getWords     = A('Api/Index');   //调用Api模块方法
        $questionType = $getWords->getArticleQuestionType();  //分类库
        $tkey         = '';
        $ttype        = M('ArticleQuestionType');
        //知识咨询
        for ( $i = 1;$i <= 2; $i++ )
        {
            $url = 'http://www.ucdrs.net/admin/union/index.do?t=json&type=repay&pages='.$i; //资料来源：全国图书馆参考咨询联盟
            $consultantInfo = curl_https_get($url);
            if ( empty($consultantInfo) )
            {
                for ( $i=0;$i<=4;$i++ )
                {
                    $consultantInfo = curl_https_get($url);
                    if ( ! empty($consultantInfo) ) break;
                }

            }

            if ( $consultantInfo )
            {
                foreach ( $consultantInfo as $va => $ke )
                {
                    $tdayi = substr($ke['audittime'],0,5);
                    if ( $tdayi == $tday )
                    {
                        $cinfo[$i][$va]["question_title"] = str_filter($ke["title"]); //安全过滤
                        $cinfo[$i][$va]["consultants"]    = $ke["ctime"];
                        $cinfo[$i][$va]["add_time"]       = $times;
                        $cinfo[$i][$va]["is_robot_learning"] = 1;      //1机器人
                        $ttid = $ttype->field('id')->where('type_name="' .$ke["type"]. '"')->find();
                        if ( $ttid )
                        {
                            $tkey = $ttid;
                        }
                        else
                        {
                            //没有的分类就录进去
                            $tdata[$va]['add_time']  = $times;
                            $tdata[$va]['type_name'] = $ke["type"];
                            $tdata[$va]['is_robot_learning'] = 1;
                            $tkey = $ttype->filter('strip_tags')->add($tdata[$va]);
                        }
                        $cinfo[$i][$va]["type_id"] = $tkey['id'] ? $tkey['id'] : 2760; //2760表 其它 ID
                    }
                    else
                    {
                        break;
                    }
                }
            }
            else
                break;
        }

        if ( $cinfo )
        {
            //机器自己学习问题并获取答案，完成后返回答案
            $qlist     = M('ArticleQuestionList');
            $questions = M('ArticleQuestionAnswer');
            foreach (  $cinfo as $k => $v )
            {
                foreach ( $v as $ke => $va )
                {
                    $schkey =  $va['question_title'];
                    if ( $schkey )
                    {
                        //相似问题是否存在若已经存在，则不会再录入
                        $qres   = $qlist->field('id')
                                        ->where('MATCH(question_title) AGAINST("'.$schkey.'" IN BOOLEAN MODE)')
                                        ->find();
                        if ( ! $qres )
                        {
                            $qdata[$ke]['add_time']       = $times;
                            $qdata[$ke]['question_title'] = $schkey;
                            $qdata[$ke]['consultanter']   = $va['consultants'];
                            //自动获取分类
                            $words    = $getWords->getWords($schkey); //百度分析词句
                            $magaSortIdi = $va['type_id'];
                            $qdata[$ke]['article_type_id']   = $magaSortIdi;
                            $qdata[$ke]['is_robot_learning'] = 1;    //是机器学习，-1不是
                            $qdata[$ke]['ip']                = get_client_ip();     //IP
                            $qid = $qlist->filter('strip_tags')->add($qdata[$ke]);
                            if ( $qid )
                            {
                                $estr   = '1';
                                //接口获取答案
                                $urls    = "http://public.dooland.com/v1/QAArticle/getArticleResult/q/";
                                $content = curl_https_get($urls.$schkey);
                                $datas[$ke]['question_id']       = $qid;
                                $datas[$ke]['question_title']    = $schkey;
                                $datas[$ke]['add_time']          = $times;
                                $datas[$ke]['type_id']           = $magaSortIdi;
                                $datas[$ke]['is_robot_learning'] = 1;      //是机器学习，-1不是
                                $strs                            = array();
                                if ( $content['status'] == 1 )
                                {
                                    //判断是一维还是二维数组
                                    $arr = get_max_array($content['data']);
                                    if ( $arr == 3)
                                    {
                                        foreach($content['data'] as $k => $v)
                                        {
                                            foreach ($v as $item=>$ve)
                                            {
                                                $strs[]  = $v[$item];
                                            }
                                        }
                                    }
                                    else
                                    {
                                        foreach($content['data'] as $k => $v)
                                        {
                                            $strs[]  = $content['data'][$k];
                                        }
                                    }


                                    if ( $strs )  $datas[$ke]['contents'] = json_encode($strs);
                                }
                                $qkey = $questions->add($datas[$ke]); //答案入库，入库

                                if ( $qkey )
                                    $akey = '1';
                                else
                                    $akey = '-1';

                            }
                            else
                            {
                                $estr = '-1';
                            }

                            //写入日志
                            $this->robotStudyLog(2,$qid,$schkey,$estr,$akey);
                        }
                        else
                            $this->robotStudyLog(2,$qres['id'],$schkey);
                    }
                }
            }
        }
        else
        {
            $this->robotStudyLog(2);
        }

    }

    /**
     * 机器自动学习日志
     * @access protected
     * @param int $qid
     * @param string $qtitle
     * @param int $estr
     * @param int $astr
     * @author wepean<2050301456@qq.com>
     */
    protected function robotStudyLog($tid = 1,$qid = NULL,$qtitle = NULL,$estr = NULL,$astr = NULL)
    {

        if ( $tid == 1 )
            $robot = M('RobotStudyLog');
        else
            $robot = M('RobotStudyArticleLog');

        $time  = time();
        if ( $qid && $qtitle)
        {
            $data['question_id']     = $qid;
            $data['question_title']  = $qtitle;
            $data['add_time']        = $time;
            $data['origins']         = '全国图书馆参考咨询联盟';
            $data['origins_url']     = 'http://www.ucdrs.net/';
            if ( ! empty($estr) &&  ! empty($astr) )
            {
                $data['status']          = $estr;
                $data['contents_status'] = $astr;
            }
            else
            {
                $data['msg'] = '此问题已经学习过！';
            }

            $tarr  = $data;
        }
        else
        {
            $tarr = array('msg'=>'没有问题！','add_time'=>$time,'origins'=>'全国图书馆参考咨询联盟','origins_url'=>'http://www.ucdrs.net/');
        }

        $robot->filter('strip_tags')->add($tarr);
    }
}