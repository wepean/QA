<?php
namespace Api\Controller;
use Think\Controller;
class IndexController extends Controller
{
    /**
     * 初始化
     */
    public function _initialize()
    {
        //跨域设置
        header('Content-Type:application/json,charset=UTF-8');
        header('Access-Control-Allow-Origin:*');
    }

    /**
     * 获取问题答案(杂志，文章，笔记)缓存
     * @access protected
     * @param  int $qids 2文章，3笔记，默认杂志
     * @author wepean<2050301456@qq.com>
     * return  array
     */
    protected function getQuestionResults($qids = 1)
    {
        $where = 'article_status = 1';
        if ( $qids == 2 )
        {
            $keys   = "questionsi";
            $mTypes = D('AritcleQuestionAnswer');
        }
        elseif ( $qids == 3 )
        {
            $keys   = "questionsn";
            $mTypes = D('NurseQuestionAnswer');
        }
        elseif ( $qids == 4 )
        {
            $keys   = "nurseArticle";
            $mTypes = D('NurseArticleQuestionAnswer');
        }
        else
        {
            $keys   = "questions";
            $mTypes = D('QuestionAnswer');
            $where  = 'status=1';
        }

        $sorts = S($keys);

        if ( ! $sorts)
        {
            $sorts = $mTypes->field('id,question_title')->where($where)->order('id desc')->select();
            if ( $sorts )
            {
                S($keys,$sorts,20*60); //20分钟
            }
        }

        return $sorts;
    }

    /**
     * 获取分类(杂志、文章、笔记)缓存
     * @access public
     * @param  int $tid 2文章，3笔记，默认杂志
     * @author wepean <2050301456@qq.com>
     * return  array
     */
    protected function getTypes($tid = 1)
    {
        if ( $tid == 2 )
        {
            $keys   = "magSortn";
            $mTypes = D('ArticleQuestionType');

        }
        elseif ( $tid == 3 )
        {
            $keys   = "magSorta";
            $mTypes = D('NurseQuestionType');
        }
        elseif ( $tid == 4 )
        {
            $keys   = "nuserArticle";
            $mTypes = D('NurseArticleQuestionType');
        }
        else
        {
            $keys  = "magSort";
            $mTypes = D('questionType');
        }

        $sorts = S($keys);
        if ( ! $sorts)
        {
            $sorts = $mTypes->field('id,type_name')->where('status=1')->order('id desc')->select();
            if ($sorts)
            {
                S($keys,$sorts,12*60*60); //缓存12个小时
            }

        }

        return $sorts;
    }
    /**
     * (杂志版)Ajax方式返回数据到客户端方法一
     * @access public
     * @param  mixed $data 要返回的数据
     * @author wepean<2050301456@qq.com>
     * @return json
     */
    public function index()
    {
        if (IS_POST)
        {
            $msgss  = file_get_contents("php://input");
            $msgs   = json_decode($msgss,true);
            $osType = $msgs['osType'];
            $udid   = $msgs['udid'];
            $schkey = str_filter($msgs['q']); //安全过滤
            if ( ! $osType  OR ! $schkey OR ! $udid) $this->ajaxReturn(array('error'=>C('CSCW_STRING'),'status'=>'-1'));
            //机器自己学习问题并获取答案，完成后返回答案
            $qlist     = M('QuestionList');
            $questions = M('QuestionAnswer');
            //相似问题是否存在若已经存在，则不会再录入
            $qres  = $qlist->field('id')
                           ->where('POSITION("' . $schkey . '" IN question_title) OR question_title like "' . $schkey . '%" OR question_title like "%' . $schkey . '" OR question_title="' .$schkey. '"')
                           ->find();
            $words  = $this->getWords($schkey); //百度分析词句
            $questionType = $this->getTypes();  //分类库
            if ( ! $qres )
            {
                $times                   = time();
                $qdata['add_time']       = $times;
                $qdata['question_title'] = $schkey;
                //自动获取分类
                $magaSortId   = $strBodyi = '';
                if ( $questionType )
                {
                    foreach ( $questionType as $k => $v )
                    {
                        foreach ( $words as $item )
                        {
                            if ( stripos('"'.$item.'"',$v['type_name']) OR '"'.$item.'"' == $v['type_name']) $magaSortId = $v['id'];
                        }
                    }
                }
                $magaSortIdi           = $magaSortId ? $magaSortId : 2869;  //2869 其它 ID
                $qdata['type_id']      = $magaSortIdi;
                $qdata['consultanter'] = $udid;               //提问人
                $qdata['ip']           = get_client_ip();     //提问人IP
                $qdata['is_robot_learning'] = 1;    //是机器学习，-1不是
                $qid = $qlist->filter('strip_tags')->add($qdata);
                if ( $qid )
                {
                    //接口获取答案
                    $urls    = "http://public.dooland.com/v1/QAMagazine/getMagazineResult/q/";
                    $content = curl_https_get($urls.$schkey);
                    $datas['question_id']       = $qid;
                    $datas['question_title']    = $schkey;
                    $datas['add_time']          = $times;
                    $datas['type_id']           = $magaSortIdi;
                    $datas['is_robot_learning'] = 1;      //是机器学习，-1不是
                    if ($content['status'] == 1)
                    {
                        //杂志
                        if ($content['data']['magaList'])
                        {
                            //判断是一维还是二维数组
                            $arr = get_max_array($content['data']['magaList']);
                            if ( $arr == 3)
                            {
                                foreach($content['data']['magaList'] as $k => $v)
                                {
                                    foreach ($content['data']['magaList'][$k] as $item)
                                    {
                                        $strBodyi .= '<div  style="float:left;margin:2px;font-size:12px;"><div><img src="'.$item['pic_size2'].'" width="130" height="170"/></div><div style=" text-overflow:ellipsis; white-space:nowrap; overflow:hidden; margin:2px 0;color:#0009;max-width:130px"><a style="'.$v['id'].'">'.$item['title'].'</a></div></div>';
                                    }
                                }
                            }
                            else
                            {
                                foreach($content['data']['magaList'] as $k => $v)
                                {
                                    $strBodyi .= '<div  style="float:left;margin:2px;font-size:12px;"><div><img src="'.$v['pic_size2'].'" width="130" height="170"/></div><div style=" text-overflow:ellipsis; white-space:nowrap; overflow:hidden; margin:2px 0;color:#0009;max-width:130px"><a style="'.$v['id'].'">'.$v['title'].'</a></div></div>';
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
                                foreach($content['data']['bookList'] as $k => $v)
                                {
                                    foreach ( $content['data']['bookList'][$k] as $item )
                                    {
                                        $strBodyi .= '<div style="float:left;margin:2px;font-size:12px;"><div><img src="'.$item['pic'].'" width="130" height="170"/></div><div style=" text-overflow:ellipsis; white-space:nowrap; overflow:hidden; margin:2px 0;color:#0009;max-width:130px"><a style="'.$item['id'].'">'.$item['title'].'</a></div></div>';
                                    }
                                }
                            }
                            else
                            {
                                foreach($content['data']['bookList'] as $k => $v)
                                {
                                    $strBodyi .= '<div style="float:left;margin:2px;font-size:12px;"><div><img src="'.$v['pic'].'" width="130" height="170"/></div><div style=" text-overflow:ellipsis; white-space:nowrap; overflow:hidden; margin:2px 0;color:#0009;max-width:130px"><a style="'.$v['id'].'">'.$v['title'].'</a></div></div>';
                                }
                            }

                        }
                        if ( $strBodyi ) $datas['contents'] = $strBodyi;
                    }
                    //答案入库，入库后直接返回机器人学习答案
                    if ( $questions->filter('stripslashes')->add($datas) )
                    {
                        die($this->ajaxReturn(array('msg'=>C('SJHQCG_STRING'),'status'=>'1','data'=>array('contents'=>$strBodyi))));
                    }
                }
            }

            $orders    = ' id desc ';
            $result    = $questions->field('contents')
                                   ->where('POSITION("' . $schkey . '" IN question_title) OR question_title like "' . $schkey . '%" OR question_title like "%' . $schkey . '" OR POSITION("' . $schkey . '" IN contents) OR contents like "' . $schkey . '%" OR contents like "%' . $schkey . '" ')
                                   ->order($orders)
                                   ->find();

            //问题标题不相等
            if ( ! $result)
            {
                //问题与标题匹配
                foreach ( $words as $item )
                {
                    $result = $questions->field('contents')
                                        ->where('POSITION("' . $item . '" IN question_title) OR question_title like "' . $item . '%" OR question_title like "%' .$item . '" OR POSITION("' . $item . '" IN contents) OR contents like "' . $item . '%" OR contents like "%' . $item . '" ')
                                        ->order($orders)
                                        ->find();
                    //找到结果马上返回
                    if ( $result )
                    {
                        die($this->ajaxReturn(array('msg'=>C('SJHQCG_STRING'),'status'=>'1','data'=>$result)));
                    }
                }
                //匹配分类查找
                $magaSortId   = $magaSortIdi = array();
                if ($questionType)
                {
                    foreach($questionType as $k => $v)
                    {
                        foreach ( $words as $item )
                        {
                            if ( stripos('"'.$item.'"',$v['type_name']) ) $magaSortId[] = $v['id'];
                            if ( stripos('"'.$v['type_name'].'"',$item) ) $magaSortId[] = $v['id'];
                            if ( $item == $v['type_name'] ) $magaSortId[] = $v['id'];
                        }
                    }
                }
                if ($magaSortId)
                {
                    $wheres['type_id'] = array('IN',array_unique($magaSortId));
                    $result = $questions->field('contents')
                                        ->where($wheres)
                                        ->order($orders)
                                        ->find();
                    if ($result)
                    {
                        die( $this->ajaxReturn(array('msg'=>C('SJHQCG_STRING'),'status'=>'1','data'=>$result)) );
                    }
                    else
                    {
                        $questi = $this->getQuestionResults();

                        foreach($questi as $k => $v)
                        {
                            foreach ( $words as $item )
                            {
                                if (stripos('"' . $item . '"',$v['question_title']))  $magaSortIdi[] = $v['id'];
                                if (stripos('"' . $v['question_title'] . '"',$item) ) $magaSortIdi[] = $v['id'];
                            }
                        }

                        if ($magaSortIdi)
                        {
                            $where['id'] = array('IN',array_unique($magaSortIdi));
                            $result = $questions->field('contents')
                                                ->where($where)
                                                ->order($orders)
                                                ->find();
                        }

                    }

                }
                else
                {
                    $questi = $this->getQuestionResults();

                    foreach($questi as $k => $v)
                    {
                        foreach ( $words as $item )
                        {
                            if (stripos($item,$v['question_title'])) $magaSortIdi[] = $v['id'];
                            if (stripos($v['question_title'],$item)) $magaSortIdi[] = $v['id'];
                        }
                    }
                    if ($magaSortIdi)
                    {
                        $where['id'] = array('IN',array_unique($magaSortIdi));
                        $result = $questions->field('contents')
                                            ->where($where)
                                            ->order($orders)
                                            ->find();
                    }
                }

                if ( $result )
                    die($this->ajaxReturn(array('msg'=>C('SJHQCG_STRING'),'status'=>'1','data'=>$result)));
                else
                    $this->ajaxReturn(array('error'=>C('MYSJ_STRING'),'status'=>'-1'));
            }
            else
                $this->ajaxReturn(array('msg'=>C('SJHQCG_STRING'),'status'=>'1','data'=>$result));

        }
        else
        {
            $this->ajaxReturn(array('error'=>C('FFCZ_STRING'),'status'=>'-1'));
        }

    }
    /**
     * (文章版)Ajax方式返回数据到客户端方法一
     * @access public
     * @param  mixed $data 要返回的数据
     * @author wepean<2050301456@qq.com>
     * @return json
     */
    public function articleStudy()
    {
        if (IS_POST)
        {
            $msgss  = file_get_contents("php://input");
            $msgs   = json_decode($msgss,true);
            $osType = $msgs['osType'];
            $udid   = $msgs['udid'];
            $schkey = str_filter($msgs['q']); //安全过滤
            if ( ! $osType  OR ! $schkey OR ! $udid) $this->ajaxReturn(array('error'=>C('CSCW_STRING'),'status'=>'-1'));
            //机器自己学习问题并获取答案，完成后返回答案
            $qlist     = M('ArticleQuestionList');
            $questions = M('ArticleQuestionAnswer');
            //相似问题是否存在若已经存在，则不会再录入
            $orders    = ' id desc ';
            $fields    = 'contents,remark';
            $result    = $questions->field($fields)
                                   ->where('MATCH(question_title) AGAINST("*' . $schkey . '*" IN BOOLEAN MODE) OR question_title = "' . $schkey . '"')
                                   ->order($orders)
                                   ->find();
            if ( ! $result )
            {
                $times                   = time();
                $qdata['add_time']       = $times;
                $qdata['question_title'] = $schkey;
                //自动获取分类
                $words        = $this->getWords($schkey); //百度分析词句
                $questionType = $this->getTypes(2);       //分类库
                $magaSortId   = '';
                if ( $questionType )
                {
                    foreach ( $questionType as $k => $v )
                    {
                        foreach ( $words as $item )
                        {
                            if ( stripos('"'.$item.'"',$v['type_name']) OR '"'.$item.'"' == $v['type_name']) $magaSortId = $v['id'];
                        }
                    }
                }
                $magaSortIdi           = $magaSortId ? $magaSortId : 2760;  //2760 其它 ID
                $qdata['consultanter'] = $udid;               //提问人
                $qdata['ip']           = get_client_ip();     //提问人IP
                $qdata['is_robot_learning'] = 1;              //是机器学习，-1不是
                $qdata['article_type_id']   = $magaSortIdi;
                $qid = $qlist->filter('strip_tags')->add($qdata);
                if ( $qid )
                {
                    //接口获取答案
                    $urls    = "http://public.dooland.com/v1/QAArticle/getArticleResult/q/";
                    $content = curl_https_get($urls.$schkey);
                    $datas['question_id']       = $qid;
                    $datas['question_title']    = $schkey;
                    $datas['add_time']          = $times;
                    $datas['article_type_id']   = $magaSortIdi;
                    $datas['is_robot_learning'] = 1;      //是机器学习，-1不是
                    $strs                       = array();
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
                        $datas['contents'] = json_encode($strs);
                    }
                    //答案入库，入库后直接返回机器人学习答案
                    if ( $questions->add($datas) )
                    {
                        if ( $strs )
                        {
                            die($this->ajaxReturn(array('msg'=>C('SJHQCG_STRING'),'status'=>'1','data'=>$strs,'remark'=>'')));
                        }
                    }
                }

                //问题与标题匹配
                foreach ( $words as $item )
                {
                    $result = $questions->field($fields)
                                        ->where('MATCH(question_title) AGAINST("*' . $item . '*" IN BOOLEAN MODE)')
                                        ->order($orders)
                                        ->find();
                    //找到结果马上返回
                    if ( $result['contents'] )
                    {
                        die($this->ajaxReturn(array('msg'=>C('SJHQCG_STRING'),'status'=>'1','data'=>json_decode($result['contents'],true),'remark'=>(is_null($result['remark']) ? "" : $result['remark']))));
                    }
                }
                //匹配分类查找
                $magaSortId   = $magaSortIdi = array();
                if ($questionType)
                {
                    foreach($questionType as $k => $v)
                    {
                        foreach ( $words as $item )
                        {
                            if ( stripos('"'.$item.'"',$v['type_name']) ) $magaSortId[] = $v['id'];
                            if ( stripos('"'.$v['type_name'].'"',$item) ) $magaSortId[] = $v['id'];
                            if ( $item == $v['type_name'] ) $magaSortId[] = $v['id'];
                        }
                    }
                }
                if ($magaSortId)
                {
                    $wheres['article_type_id'] = array('IN',array_unique($magaSortId));
                    $result = $questions->field($fields)
                                        ->where($wheres)
                                        ->order($orders)
                                        ->find();
                    if ( $result['contents'] )
                    {
                        die( $this->ajaxReturn(array('msg'=>C('SJHQCG_STRING'),'status'=>'1','data'=>json_decode($result['contents'],true),'remark'=>(is_null($result['remark']) ? "" : $result['remark']))) );
                    }
                    else
                    {
                        $questi = $this->getQuestionResults(2);

                        foreach($questi as $k => $v)
                        {
                            foreach ( $words as $item )
                            {
                                if (stripos('"' . $item . '"',$v['question_title']))  $magaSortIdi[] = $v['id'];
                                if (stripos('"' . $v['question_title'] . '"',$item) ) $magaSortIdi[] = $v['id'];
                            }
                        }

                        if ($magaSortIdi)
                        {
                            $where['id'] = array('IN',array_unique($magaSortIdi));
                            $result = $questions->field($fields)
                                                ->where($where)
                                                ->order($orders)
                                                ->find();
                        }

                    }

                }
                else
                {
                    $questi = $this->getQuestionResults(2);

                    foreach($questi as $k => $v)
                    {
                        foreach ( $words as $item )
                        {
                            if (stripos($item,$v['question_title'])) $magaSortIdi[] = $v['id'];
                            if (stripos($v['question_title'],$item)) $magaSortIdi[] = $v['id'];
                        }
                    }
                    if ($magaSortIdi)
                    {
                        $where['id'] = array('IN',array_unique($magaSortIdi));
                        $result = $questions->field($fields)
                                            ->where($where)
                                            ->order($orders)
                                            ->find();
                    }
                }

                if ( $result['contents'] )
                    die($this->ajaxReturn(array('msg'=>C('SJHQCG_STRING'),'status'=>'1','data'=>json_decode($result['contents'],true),'remark'=>(is_null($result['remark']) ? "" : $result['remark']))));
                else
                    $this->ajaxReturn(array('error'=>C('MYSJ_STRING'),'status'=>'-1'));
            }
            else
            {
                if ( $result['contents'] )
                    die($this->ajaxReturn(array('msg'=>C('SJHQCG_STRING'),'status'=>'1','data'=>json_decode($result['contents'],true),'remark'=>(is_null($result['remark']) ? "" : $result['remark']))));
                else
                    $this->ajaxReturn(array('error'=>C('MYSJ_STRING'),'status'=>'-1'));
            }

        }
        else
        {
            $this->ajaxReturn(array('error'=>C('FFCZ_STRING'),'status'=>'-1'));
        }

    }
    /**
     * (笔记问答版)Ajax方式返回数据到客户端方法一
     * @access public
     * @param  mixed $data 要返回的数据
     * @author wepean<2050301456@qq.com>
     * @return json
     */
    public function nurseNotesStudy()
    {
        if (IS_POST)
        {
            $msgss  = file_get_contents("php://input");
            $msgs   = json_decode($msgss,true);
            $osType = $msgs['osType'];
            $udid   = $msgs['udid'];
            $schkey = str_filter($msgs['q']); //安全过滤
            if ( ! $osType  OR ! $schkey OR ! $udid) $this->ajaxReturn(array('error'=>C('CSCW_STRING'),'status'=>'-1'));
            $questions   = M('NurseQuestionAnswer');
            $orders      = ' id desc ';
            $fields      = 'question_title,contents,options';
            $limit       = 10;
            $nurseResult = array();
            $words       = $this->getWords($schkey); //百度分析词句
            //问题与标题匹配
            foreach ( $words as $key => $item )
            {

                $where[$key]['question_title'] = array('like',array('%'.$item.'',''.$item.'%','%'.$item.'%'),'OR');
                $result[$key] = $questions->field($fields)
                                          //->where('MATCH(question_title) AGAINST("*' . $item . '*" IN BOOLEAN MODE)')
                                          ->WHERE($where[$key])
                                          ->order($orders)
                                          ->limit($limit)
                                          ->select();

                //找到结果马上返回
                if ( $result[$key] )
                {
                    foreach ( $result[$key] as $ke => $va )
                    {
                        $result[$key][$ke]['contents'] = json_decode($va['contents'],true);
                        $result[$key][$ke]['options']  = json_decode($va['options'],true);
                        if ( $va['options'] != "[]" && $va['options'] )
                        {
                            foreach ( $result[$key][$ke]['contents'] as $keys => $vals )
                            {
                                foreach ( $result[$key][$ke]['options'] as $k => $v )
                                {
                                    if ( $vals['value'] == $v['id'] OR  $vals['value'] == $v['value'] OR ! $v['value'])
                                    {
                                        $nurseResult[$key][$ke]['title'] = $va['question_title'];
                                        $nurseResult[$key][$ke]['values'][$keys]['value'] = $v['value'];
                                    }
                                }
                            }
                        }

                        if ( $va['options'] == "[]" )
                        {
                            foreach ( $result[$key][$ke]['contents'] as $keys => $vals )
                            {
                                $nurseResult[$key][$ke]['title'] = $va['question_title'];
                                $nurseResult[$key][$ke]['values'][$keys]['value'] = $vals['value'];
                            }
                        }

                    }
                }
            }

            if ( $nurseResult )
            {
                $this->ajaxReturn(array('msg'=>C('SJHQCG_STRING'),'status'=>'1','data'=>$nurseResult));
            }
            else
            {
                $wherei['question_title'] = array('like',array('%'.$schkey.'',''.$schkey.'%','%'.$schkey.'%'),'OR');
                //问题直接匹配
                $resulti = $questions->field($fields)
                                     ->where($wherei)
                                     //->where('MATCH(question_title) AGAINST("*' . $schkey . '*" IN BOOLEAN MODE)')
                                     ->order($orders)
                                     ->limit($limit)
                                     ->select();
                if ( $resulti )
                {
                    foreach ( $resulti as $ke => $va )
                    {
                        $resulti[$ke]['contents'] = json_decode($va['contents'],true);
                        $resulti[$ke]['options']  = json_decode($va['options'],true);
                        if ( $va['options'] != "[]" && $va['options'] )
                        {
                            foreach ( $resulti[$ke]['contents'] as $keys => $vals )
                            {
                                foreach ( $resulti[$ke]['options'] as $k => $v )
                                {
                                    if ( $vals['value'] == $v['id'] OR  $vals['value'] == $v['value'])
                                    {
                                        $nurseResult[$ke]['title'] = $va['question_title'];
                                        $nurseResult[$ke]['values'][$keys]['value'] = $v['value'];
                                    }
                                }
                            }
                        }

                        if ( $va['options'] == "[]" )
                        {
                            foreach ( $resulti[$ke]['contents'] as $keys => $vals )
                            {
                                $nurseResult[$ke]['title'] = $va['question_title'];
                                $nurseResult[$ke]['values'][$keys]['value'] = $vals['value'];
                            }
                        }

                    }

                    if ( $nurseResult )
                        $this->ajaxReturn(array('msg'=>C('SJHQCG_STRING'),'status'=>'1','data'=>array($nurseResult)));
                    else
                        $this->ajaxReturn(array('error'=>C('MYSJ_STRING'),'status'=>'-1'));
                }
                else
                {
                    $this->ajaxReturn(array('error'=>C('MYSJ_STRING'),'status'=>'-1'));
                }
            }
        }
        else
        {
            $this->ajaxReturn(array('error'=>C('FFCZ_STRING'),'status'=>'-1'));
        }

    }
    /**
     * (笔记文章版)Ajax方式返回数据到客户端方法一
     * @access public
     * @param  mixed $data 要返回的数据
     * @author wepean<2050301456@qq.com>
     * @return json
     */
    public function nurseArticleStudy()
    {
        if ( IS_POST )
        {
            $msgss  = file_get_contents("php://input");
            $msgs   = json_decode($msgss,true);
            $osType = $msgs['osType'];
            $udid   = $msgs['udid'];
            $schkey = str_filter($msgs['q']); //安全过滤
            if ( ! $osType  OR ! $schkey OR ! $udid ) $this->ajaxReturn(array('error'=>C('CSCW_STRING'),'status'=>'-1'));

            //机器自己学习问题并获取答案，完成后返回答案
            $qlist     = M('NurseArticleQuestionList');
            $questions = M('NurseArticleQuestionAnswer');
            //相似问题是否存在若已经存在，则不会再录入
            $orders    = ' id desc ';
            $fields    = 'contents,remark';
            $result    =  $questions->field($fields)
                                    ->where('MATCH(question_title) AGAINST("*' . $schkey . '*" IN BOOLEAN MODE) OR question_title = "' . $schkey . '"')
                                   // ->where('question_title like "' . $schkey . '%" OR question_title = "' . $schkey . '" OR question_title like "%' . $schkey . '" OR question_title like "%' . $schkey . '%"')
                                    ->order($orders)
                                    ->find();

            if ( ! $result )
            {
                $times                   = time();
                $qdata['add_time']       = $times;
                $qdata['question_title'] = $schkey;
                //自动获取分类
                $words        = $this->getWords($schkey); //百度分析词句
                $questionType = $this->getTypes(4);       //分类库
                $magaSortId   = '';
                if ( $questionType )
                {
                    foreach ( $questionType as $k => $v )
                    {
                        foreach ( $words as $item )
                        {
                            if ( stripos('"'.$item.'"',$v['type_name']) OR '"'.$item.'"' == $v['type_name']) $magaSortId = $v['id'];
                        }
                    }
                }
                $magaSortIdi                = $magaSortId ? $magaSortId : 2;  //2 其它 ID
                $qdata['consultanter']      = $udid;               //提问人
                $qdata['ip']                = get_client_ip();     //提问人IP
                $qdata['is_robot_learning'] = 1;              //是机器学习，-1不是
                $qdata['article_type_id']   = $magaSortIdi;
                $qid = $qlist->filter('strip_tags')->add($qdata);

                if ( $qid )
                {
                    //获取答案
                    $content = $this->getNurseArticleQuestionAnswer($schkey);

                    $datas['question_id']       = $qid;
                    $datas['question_title']    = $schkey;
                    $datas['add_time']          = $times;
                    $datas['article_type_id']   = $magaSortIdi;
                    $datas['is_robot_learning'] = 1;      //是机器学习，-1不是
                    if ( $content )
                    {
                        $datas['contents'] = json_encode($content);
                    }

                    //答案入库，入库后直接返回机器人学习答案
                    if ( $questions->add($datas) )
                    {
                        if ( $content )
                        {
                            die($this->ajaxReturn(array('msg'=>C('SJHQCG_STRING'),'status'=>'1','data'=>$content,'remark'=>'')));
                        }
                    }
                }

                //问题与标题匹配
                foreach ( $words as $item )
                {
                    $result = $questions->field($fields)
                                        ->where('MATCH(question_title) AGAINST("*' . $item . '*" IN BOOLEAN MODE)')
                                        ->order($orders)
                                        ->find();
                    //找到结果马上返回
                    if ( $result['contents'] )
                    {
                        $contents = json_decode($result['contents'],true);

                        die($this->ajaxReturn(array('msg'=>C('SJHQCG_STRING'),'status'=>'1','data'=>is_null($contents) ? "" : $contents,'remark'=>(is_null($result['remark']) ? "" : $result['remark']))));
                    }
                }

                if ( ! $result )
                {
                    $questi = $this->getQuestionResults(4);

                    foreach ( $questi as $k => $v )
                    {
                        foreach ( $words as $item )
                        {
                            if ( stripos($item,$v['question_title']) ) $magaSortIdi[] = $v['id'];
                            if ( stripos($v['question_title'],$item) ) $magaSortIdi[] = $v['id'];
                        }
                    }

                    if ( ! empty($magaSortIdi) )
                    {
                        $where['id'] = array('IN',array_unique($magaSortIdi));
                        $result = $questions->field($fields)
                                            ->where($where)
                                            ->order($orders)
                                            ->find();
                    }
                }

                if (  $result['contents'] )
                {
                    $contents = json_decode($result['contents'],true);
                    die($this->ajaxReturn(array('msg'=>C('SJHQCG_STRING'),'status'=>'1','data'=>is_null($contents) ? "" : $contents,'remark'=>(is_null($result['remark']) ? "" : $result['remark']))));
                }
                else
                {
                    $this->ajaxReturn(array('error'=>C('MYSJ_STRING'),'status'=>'-1'));
                }
            }
            else
            {

               if ( $result['contents'] )
               {
                   $contents = json_decode($result['contents'],true);
                   $this->ajaxReturn(array('msg'=>C('SJHQCG_STRING'),'status'=>'1','data'=>is_null($contents) ? "" : $contents,'remark'=>(is_null($result['remark']) ? "" : $result['remark'])));
               }
               else
               {
                   $this->ajaxReturn(array('error'=>C('MYSJ_STRING'),'status'=>'-1'));
               }
            }
        }
        else
        {
            $this->ajaxReturn(array('error'=>C('FFCZ_STRING'),'status'=>'-1'));
        }

    }
    /**
     * 获取百度access_token
     * @access protected
     * @param  string    $url
     * @param  array     $data
     * @author wepean <2050301456@qq.com>
     * return  string
     */
    protected function getAccessToken()
    {
        $keys        = "getAccessToken";
        $accessToken = S($keys);
        if ( ! $accessToken)
        {
            $url  = 'https://aip.baidubce.com/oauth/2.0/token';
            //百度应用--对话机器人语句分析i
            $data = array(
                        'grant_type'     =>  'client_credentials',                //grant_type： 必须参数，固定为client_credentials；
                        'client_id'      =>  'kLvanswUQVfp6jTti8m4A7GP',          //应用的API Key；
                        'client_secret'  =>  'kO0fvz7MP6rOSYAoD7x6m8GK4rjdlgyA', //必须参数，应用的Secret Key；
                        );

            //POST
            $result      = curl_request($url,$data,'POST');
            $results     = json_decode($result,true);
            $accessToken = $results['access_token'];
            if ($accessToken) S($keys,$accessToken,30*60); //20分钟
        }

        return $accessToken;
    }
    /**
     * 获取百度词法分析后结果
     * @access protected
     * @param  string $q
     * @author Wepean <2050301456@qq.com>
     * return  array
     */
    public function  getWords($q = NULL)
    {
        if ( ! isset($q) OR  ! $q ) return false;
        $token   = $this->getAccessToken();
        $wordArr = array();
        if ( $token )
        {
            $url     = 'https://aip.baidubce.com/rpc/2.0/nlp/v1/lexer?access_token='.$token; //词法分析
            //$url   = 'https://aip.baidubce.com/rpc/2.0/nlp/v1/depparser?access_token='.$token;//依存句法分析
            $data    = array("text" => $q);
            //POST获取词法分析信息
            $result  = curl_request($url,json_encode($data),'POST');
            $results = mb_convert_encoding($result, 'utf-8', 'GBK,UTF-8,ASCII'); //中文乱码处理
            $res     = json_decode($results,true);
            $resArr  = $res['items'];
            if ( $resArr )
            {
                foreach ( $resArr as $item )
                {
                    //去空
                    if ( trim($item['item']))
                    {
                        $strs = $this->isChinese($item['item']);

                        //为中文必须大于一个汉字
                        if ( ( $strs == 2  && strlen($item['item']) > 3 ) OR  $strs == 1) $wordArr[] = $item['item'];
                    }
                }
            }
        }
       return array_unique($wordArr);   //去重复
    }

    /**
     * 获取百度词法分析后结果
     * @access protected
     * @param  string $q
     * @author Wepean <2050301456@qq.com>
     * return  array
     */
    public function  getWordss($q = NULL)
    {
            $url     = 'http: //users.dooland.com/11244bf15870d8567b41d99b908544ed/user/login'; //词法分析
            //$url   = 'https://aip.baidubce.com/rpc/2.0/nlp/v1/depparser?access_token='.$token;//依存句法分析
            $data    = array("username" => 'ipadtest','password'=>'e10adc3949ba59abbe56e057f20f883e');
            //POST获取词法分析信息
            $result  = curl_request($url,$data,'POST');
            dump($result);
            $results = mb_convert_encoding($result, 'utf-8', 'GBK,UTF-8,ASCII'); //中文乱码处理
            $res     = json_decode($results,true);


        return array_unique($wordArr);   //去重复
    }

    /**
     * 判断是否是中文
     * @access protected
     * @param  string $c
     * @author Wepean <2050301456@qq.com>
     * return  string 2为中文
     */
    protected function isChinese($s)
    {
        if ( ! $s ) return false;
        $allcn = preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $s);
        $str   = 1;
        if ( $allcn ) $str = 2; //为中文
        return $str;
    }

    /**
     * 获取所有护士笔记文章版问量答案
     * @access protected
     * @param  $key string 搜索关键词
     * @author wepean<2050301456@qq.com>
     * return  array
     */
    public function getNurseArticleQuestionAnswer($key)
    {
        if ( ! trim($key) ) die($this->ajaxReturn(array('error'=>C('CSCW_STRING'),'status'=>'-1')));
        $words  = $this->getWords($key);     //百度分析词句
        $maga   = M('NurseArticle');
        //变量初始化
        $limits = '10';
        $orders = 'id desc';
        $fields = 'id,title';
        $magaResult = $magaResultii = array();

        //相关文章
        foreach ( $words as $ke => $item )
        {
            //相关文章
            $magaResultii[$ke]  =  $maga->field($fields)
                                        ->where('title like "' . $item . '%" OR title like "%' . $item . '%" OR title like "%' . $item . '"')
                                        ->order($orders)
                                        ->limit($limits)
                                        ->select();

            //去空
            if ( $magaResultii[$ke] ) $magaResult[] = $magaResultii[$ke];
        }


        $magaResulti = $magaResult ? $magaResult : '';

        return $this->arrayUniques($magaResulti);
    }
    /**
     * 数组去重
     * @access protected
     * @param  $arr array
     * @author wepean<2050301456@qq.com>
     * return  array
     */
    public function arrayUniques($arr)
    {
        $newarr = array();
        if ( is_array($arr))
        {
            foreach ( $arr as $v )
            {
                foreach ( $v as $item )
                {
                    if ( ! in_array($item,$newarr,true) )
                    {
                        $newarr[] = $item;
                    }
                }

            }
        }
        else
        {
            return false;
        }

        return $newarr;
    }
    /**
     * 获取所有杂志分类
     * @access public
     * @author wepean <2050301456@qq.com>
     * return  array
     */
    public function getQuestionType()
    {
        $keys  = "magSort";
        $sorts = S($keys);
        if ( ! $sorts)
        {
            $sorts = D('questionType')->field('id,type_name')->where('status=1')->order('id desc')->select();
            if ($sorts)
            {
                S($keys,$sorts,12*60*60); //缓存12个小时
            }
        }

        return $sorts;
    }
    /**
     * 获取所有文章分类
     * @access public
     * @author wepean <2050301456@qq.com>
     * return  array
     */
    public function getArticleQuestionType()
    {
        $keys  = "magSorta";
        $sorts = S($keys);
        if ( ! $sorts)
        {
            $sorts = D('ArticleQuestionType')->field('id,type_name')->where('status=1')->order('id desc')->select();
            if ($sorts)
            {
                S($keys,$sorts,12*60*60); //缓存12个小时
            }
        }

        return $sorts;
    }
    /**
     * 获取所有问题
     * @access protected
     * @author wepean<2050301456@qq.com>
     * return  array
     */
    protected function getQuestionResult()
    {
        $keys  = "questions";
        $sorts = S($keys);
        if ( ! $sorts)
        {
            $sorts = M('QuestionAnswer')->field('id,question_title')->where('status=1')->order('id desc')->select();
            if ($sorts)
            {
                S($keys,$sorts,20*60); //20分钟
            }
        }
        //S($keys,NULL);
        return $sorts;
    }
    /**
     * 获取所有文章版问题答案
     * @access protected
     * @author wepean<2050301456@qq.com>
     * return  array
     */
    protected function getArticleQuestionResult()
    {
        $keys  = "questionsi";
        $sorts = S($keys);
        if ( ! $sorts)
        {
            $sorts = M('AritcleQuestionAnswer')->field('id,question_title')->where('status=1')->order('id desc')->select();
            if ($sorts)
                S($keys,$sorts,20*60); //20分钟

        }

        return $sorts;
    }
}