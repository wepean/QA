<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 问题管理控制器
 * @author wepean
 */
class QuestionsController extends CommonController
{
    /**
     * 初始化
     */
    public function _initialize()
    {
        set_time_limit(0);
        //ini_set('memory_limit', '500M');
        ini_set('max_execution_time','900');
    }
    /**
     * 问题分类添加
     * @author wepean
     * @return string
     */
    public function questionsType()
    {
        if (IS_POST)
        {
            //防止重复提交 如果重复提交跳转至相关页面
            if ( ! checkToken(I('post.TOKEN')))
            {
                redirect(U('Questions/questionsType'));
                return;
            }
            $typeName    = I('post.type_name');
            $description = I('post.description');
            $datas       = array();
            if ($typeName)
            {
                foreach ($typeName as $k => $v)
                {
                    $datas[] = array( 'type_name'   => $v,
                        'description' => $description[$k],
                        'add_time'    => time(),
                        'admin_id'    => get_admin_id()
                    );
                }
                //数据批量录入
                $dataid = M('QuestionType')->addAll($datas);
                if ($dataid)
                    $this->success(C('SUB_STRING'),U('Questions/typeList'));
                else
                    $this->error(C('ERR_STRING'));
            }
            else
            {
                $this->error(C('ERR_STRING'));
            }


        }
        creatToken();  //创建token
        $this->display();
    }

    /**
     * 添加新问题
     * @author wepean
     * @return string
     */
    public function questionsAdd()
    {
       if (IS_POST)
       {
           //防止重复提交 如果重复提交跳转至相关页面
           if ( ! checkToken(I('post.TOKEN')))
           {
               redirect(U('Questions/questionsAdd'));
               return;
           }
           $typeName    = I('post.question_title');
           $description = I('post.description');
           $type_id     = I('post.type_id');
           $datas       = array();
           if ($typeName)
           {
               foreach ($typeName as $k => $v)
               {
                   $datas[] = array( 'question_title' => $v,
                                       'description'  => $description[$k],
                                       'add_time'     => time(),
                                       'type_id'      => $type_id[$k],
                                       'admin_id'     => get_admin_id(),
                                       'ip'           => get_client_ip(),
                                       'consultanter' => get_admin_tname()
                                   );
               }
               //数据批量录入
               $dataid = M('QuestionList')->addAll($datas);
               if ($dataid)
                   $this->success(C('SUB_STRING'),U('Questions/questionsList',array('oid'=>8)));
               else
                   $this->error(C('ERR_STRING'));
           }
           else
           {
               $this->error(C('ERR_STRING'));
           }

       }
       //问题分类
       $typeInfo = M('QuestionType')->field('id,type_name')->where('status=1')->select();
       $strings .= '<select name="type_id[]" lay-verify="required" style="width:100%" class="form-control">';
        foreach ($typeInfo as $k => $v)
        {
            $strings .= '<option value="'.$v['id'].'">' . $v['type_name'] . '</option>';
        }
       $strings .= '</select>';
       $this->assign('typeInfo',$strings);
       creatToken();  //创建token
       $this->display();
    }
    /**
     * 问题列表
     * @author wepean
     * @return array
     */
    public function questionsList()
    {
        import('Org.Util.Page');   //导入分页类
        $listRows  = I('get.pagesize','20','intval');
        $page      = I('get.p') ? I('get.p'):0;
        $param     = '&pagesize='.$listRows;
        $oid       = I('get.oid');
        $where     = array();
        $left      = '';
        if ( $oid == 1 OR $oid == 8 )   $left = 'LEFT JOIN ';               //全部
        if ( $oid == 2 ) $where['a.contents'] = array('exp','IS NULL');     //无答案
        if ( $oid == 3 ) $where['a.contents'] = array('exp','IS NOT NULL'); //有答案
        if ( $oid == 4 ) $where['l.status']   = array('eq','-1');
        if ( $oid == 5 ) $where['l.status']   = array('eq','1');
        if ( $oid == 6 ) $where['l.is_robot_learning'] = array('eq','1');
        if ( $oid == 7 ) $where['l.type_id']  = array('eq','0');
        if ( $oid == 8 ) $where['l.is_robot_learning'] = array('eq','-1');

        if ( $oid ) $this->assign('oid',$oid);
        //搜索
        if (IS_POST)
        {
            $qid    = I('post.questionsId','','intval');
            $kyword = I('post.keywords');
            if ($qid) $where['l.id'] = $qid;
            if ($kyword) $where['l.question_title'] = array('like',array('%'.$kyword.'%',''.$kyword.'%'),'OR');
            $this->assign('qid',$qid);
            $this->assign('kyword',$kyword);
        }

        $admin = M('QuestionList');
        //总数
        $count = $admin->alias('l')
                       ->field('l.id')
                       ->where($where)
                       ->join('' . $left . '__QUESTION_ANSWER__ a ON a.question_id=l.id')
                       ->count();

        if(($page-1)*$listRows >= $count && ($page>1 OR !is_numeric($page))) $this->error('该分页暂无记录!');
        $Page = new \Page($count,$listRows,$param);
        $show = $Page->PageShow();
        $admin_list = $admin->alias('l')
                            ->field('l.id,l.question_title,l.consultanter,l.add_time,l.status,l.is_robot_learning,l.admin_id,l.type_id,a.contents')
                            ->join('' . $left . '__QUESTION_ANSWER__ a ON a.question_id=l.id')
                            ->where($where)
                            ->limit($listRows)
                            ->page($page)
                            ->order('l.id desc')
                            ->select();


        foreach ($admin_list as $k => $v)
        {
            $adminInfo = M('admin')->field('tname')->where('id='.$admin_list[$k]['admin_id'])->find();
            $admin_list[$k]['admin_id'] =  $adminInfo['tname'] ? $adminInfo['tname'] : '--';
            $typeInfo = M('QuestionType')->field('type_name')->where('id='.$v['type_id'])->find();
            $admin_list[$k]['type_id'] =  $typeInfo['type_name'] ? $typeInfo['type_name'] : '--';
        }
        $this->assign('pagesize',$listRows);
        $this->assign('page',$show);
        $this->assign('admin_list',$admin_list);
        $this->display();
    }
    /**
     * 问题答案列表
     * @author wepean
     * @return array
     */
    public function answerList()
    {
        import('Org.Util.Page');// 导入分页类
        $listRows  = I('get.pagesize','30','intval');
        $page      = I('get.p') ? I('get.p'):0;
        $param     = '&pagesize='.$listRows;
        $admin     = M('QuestionList');
        $count     = $admin->count();
        if(($page-1)*$listRows >= $count && ($page>1 OR !is_numeric($page))) $this->error('该分页暂无记录!');
        $Page = new \Page($count,$listRows,$param);
        $show = $Page->PageShow();
        $admin_list = $admin->field('id,question_title,description,add_time,status,is_new,admin_id,type_id')->limit($listRows)->page($page)->order('id desc')->select();
        foreach ($admin_list as $k => $v)
        {
            $adminInfo = M('admin')->field('tname')->where('id='.$admin_list[$k]['admin_id'])->find();
            $admin_list[$k]['admin_id'] =  $adminInfo['tname'] ? $adminInfo['tname'] : '--';
            $typeInfo = M('QuestionType')->field('type_name')->where('id='.$v['type_id'])->find();
            $admin_list[$k]['type_id'] =  $typeInfo['type_name'] ? $typeInfo['type_name'] : '--';
        }
        $this->assign('pagesize',$listRows);
        $this->assign('page',$show);
        $this->assign('admin_list',$admin_list);
        $this->display();
    }

    /**
     * 分类列表
     * @author wepean
     * @return string
     */
    public function typeList()
    {
        import('Org.Util.Page');// 导入分页类
        $listRows  = I('get.pagesize','30','intval');
        $page      = I('get.p') ? I('get.p'):0;
        $param     = '&pagesize='.$listRows;
        $admin     = M('QuestionType');
        $where     = array();
        //搜索
        if (IS_POST)
        {
            $kyword = I('post.keywords');
            if ($kyword) $where['type_name'] = array('like',array('%'.$kyword.'%',''.$kyword.'%'),'OR');
            $this->assign('kyword',$kyword);
        }

        $count     = $admin->where($where)->count();
        if(($page-1)*$listRows >= $count && ($page>1 OR !is_numeric($page))) $this->error('该分页暂无记录!');
        $Page = new \Page($count,$listRows,$param);
        $show = $Page->PageShow();
        $admin_list = $admin->field('id,type_name,description,add_time,status,is_new,admin_id')
                            ->where($where)
                            ->limit($listRows)
                            ->page($page)
                            ->order('id desc')
                            ->select();
        foreach ($admin_list as $k => $v)
        {
            $adminInfo = M('admin')->field('tname')->where('id='.$admin_list[$k]['admin_id'])->find();
            $admin_list[$k]['admin_id'] =  $adminInfo['tname'];
        }
        $this->assign('pagesize',$listRows);
        $this->assign('page',$show);
        $this->assign('admin_list',$admin_list);

        $this->display();
    }

    /**
     * 删除分类
     * @author wepean
     * @return json
     */
    public function typeDel()
    {
        if (IS_AJAX)
        {
            $id = I('id');
            if ( ! $id) $this->ajaxReturn(array('status'=>'-1','error'=>C('CSCW_STRING')));
            $admin      = M("QuestionType");
            $admin_info = $admin->where('id='.$id)->find();
            if ( ! $admin_info) $this->ajaxReturn(array('status'=>'-1','error'=>'分类不存在！'));
            $admin_del  = $admin->where('id='.$id)->delete();
            if ($admin_del)
                $this->ajaxReturn(array('status'=>'1','error'=>C('DSUB_STRING')));
            else
                $this->ajaxReturn(array('status'=>'-1','error'=>C('DERR_STRING')));
        }
        else
        {
             redirect(U('Questions/typeList'),3,C('FFCZ_STRING'));
        }
    }
    /**
     * 删除问题
     * @author wepean
     * @return json
     */
    public function questionsDel()
    {
        if (IS_AJAX)
        {
            $id = I('post.id');
            if ( ! $id) $this->ajaxReturn(array('status'=>'-1','error'=>C('CSCW_STRING')));
            $ids = '';
            //批量删除的问题ID
            if ( stripos('"' . $id . '"',',') ) $ids = array_filter(explode(',',$id)); //合并值并去空

            if ( $ids )
            {
                $where['id'] = array('IN',$ids);
                $awhere['question_id'] = array('IN',$ids);
            }
            else
            {
                $where['id'] = $id;
                $awhere['question_id'] = $id;
            }

            $admin      = M("QuestionList");
            $admin_info = $admin->where($where)->getField('id',true);
            if ( ! $admin_info) $this->ajaxReturn(array('status'=>'-1','error'=>'问题不存在！'));
            //$admin->startTrans();
            $admin_del  = $admin->where($where)->delete();

            if ( $admin_del )
            {
                M("QuestionAnswer")->where($awhere)->delete();         //删除杂志版问题答案
                $this->ajaxReturn(array('status'=>'1','error'=>C('DSUB_STRING')));
            }
            else
                $this->ajaxReturn(array('status'=>'-1','error'=>C('DERR_STRING')));
        }
        else
        {
            redirect(U('Questions/questionsList'),3,C('FFCZ_STRING'));
        }

    }
    /**
     * 启用|禁用
     * @author wepean
     * @return json
     */
    public function typeStatus()
    {
        $id = I('id');
        if ( ! $id) $this->ajaxReturn(array('status'=>'-1','error'=>C('CSCW_STRING')));
        $admin      = M("QuestionType");
        $admin_info = $admin->where('id='.$id)->find();
        if ( ! $admin_info) $this->ajaxReturn(array('status'=>'-1','error'=>'分类不存在！'));
        $status = $admin_info['status'];
        if ($status == 1)
        {
            $admin_up  = $admin->where('id='.$id)->save(array('status'=>'-1'));
            if ($admin_up)
                $msg = array('status'=>'1','error'=>C('SUB_STRING'));
            else
                $msg = array('status'=>'-1','error'=>C('ERR_STRING'));
        }
        else
        {
            $admin_up  = $admin->where('id='.$id)->save(array('status'=>'1'));
            if ($admin_up)
                $msg = array('status'=>'1','error'=>C('SUB_STRING'));
            else
                $msg = array('status'=>'-1','error'=>C('ERR_STRING'));
        }

        $this->ajaxReturn($msg);
    }
    /**
     * 启用|禁用
     * @author wepean
     * @return json
     */
    public function questionsStatus()
    {
        $id = I('id');
        if ( ! $id) $this->ajaxReturn(array('status'=>'-1','error'=>C('CSCW_STRING')));
        $admin      = M("QuestionList");
        $questions  = M("QuestionAnswer");
        $admin_info = $admin->where('id='.$id)->find();
        if ( ! $admin_info) $this->ajaxReturn(array('status'=>'-1','error'=>'问题不存在！'));
        $status = $admin_info['status'];
        $str    = '1';
        if ($status == 1)$str = '-1';
        $qaStr  = array('status'=>$str);
        $admin->startTrans(); //启用回滚事务
        $admin_up  = $admin->where('id='.$id)->save($qaStr);
        if ($admin_up)
        {
            $qresult  = $questions->where('question_id='.$id)->find();
            if ($qresult)
            {
                $answer_up = $questions->where('question_id='.$id)->save($qaStr);
                if ($answer_up)
                    $admin->commit();
                else
                    $admin->rollback(); //回滚
            }
            else
            {
                $admin->commit();
            }

            $msg = array('status'=>'1','error'=>C('SUB_STRING'));
        }
        else
        {
            $admin->rollback();//回滚
            $msg = array('status'=>'-1','error'=>C('ERR_STRING'));
        }

        $this->ajaxReturn($msg);
    }
    /**
     * 修改分类资料
     * @author wepean
     * @return string
     */
    public function typeEdit()
    {
        $id = I('id');
        if ( ! $id) $this->error(C('CSCW_STRING'),'',3);
        $admin = M('QuestionType');
        //判断是否POST提交
        if (IS_AJAX)
        {
            $id                  = I('post.id');
            $data['type_name']   = I('post.typeName');
            $data['description'] = I('post.description');
            $data['is_new']      = 1;
            //$data['status']      = 1;
            $updataID            = $admin->where('id = '.$id)->save($data);
            if ($updataID)
                $this->ajaxReturn(array('error'=>C('SUB_STRING'),'status'=>1));
            else
                $this->ajaxReturn(array('error'=>C('ERR_STRING'),'status'=>-1));
        }
        else
        {
            $adminfo = $admin->where('id='.$id)->find();
            $this->assign('adminInfo',$adminfo);
            $this->display();
        }
    }
    /**
     * 修改问题
     * @author wepean
     * @return string
     */
    public function questionsEdit()
    {
        $id = I('id');
        if ( ! $id) $this->error(C('CSCW_STRING'),'',3);
        $admin     = M('QuestionList');
        $questions = M("QuestionAnswer");
        //判断是否POST提交
        if (IS_AJAX)
        {
            $id                     = I('post.id');
            $data['description']    = I('post.description');
            $data['is_new']         = 1;
            $data['status']         = I('post.status');
            $data['type_id']        = I('post.type_id');
            $data['question_title'] = I('post.typeName');
            $contents               = I('post.contents');
            $updataID  = $admin->where('id = '.$id)->save($data);
            $answer_up = '';
            $qresult   = $questions->where('question_id='.$id)->find();
            //是否有问题
            if ($qresult)$answer_up = $questions->where('question_id='.$id)->save(array('contents'=>$contents));

            if ($updataID OR $answer_up)
                $this->ajaxReturn(array('error'=>C('SUB_STRING'),'status'=>1));
            else
                $this->ajaxReturn(array('error'=>C('ERR_STRING'),'status'=>-1));
        }
        else
        {
            $adminfo  = $admin->where('id='.$id)->find();
            $answere  = $questions->field('contents')->where('question_id='.$id)->find();
            $adminfo['contents'] = $answere ? $answere['contents'] : '';
            $typeInfo = M('QuestionType')->field('id,type_name')->where('status=1')->order('id desc')->select();
            $this->assign('adminInfo',$adminfo);
            $this->assign('typeInfo',$typeInfo);
            $this->display();
        }
    }
    /**
     * 批量获取问题答案
     * @author wepean
     * @return string
     */
    public function getQuestionsAnswer()
    {
        //判断是否POST提交
        if (IS_AJAX)
        {
            $id = I('post.id');
            //$id = I('id');
            if ( ! $id) $this->ajaxReturn(array('error'=>C('CSCW_STRING'),'status'=>-1));
            $admin = M('QuestionList');
            $ids   = array_filter(explode(',',$id)); //合并值并去空
            //接口获取答案
            $urls  = "http://public.dooland.com/v1/QAMagazine/getMagazineResult/q/";
            $title = array();
            $tstr  = '';
            if ($ids)
            {
                foreach ($ids as $k => $v)
                {
                    $qtitle = $admin->field('id,question_title,type_id')->where('id='.$v .' and status=1')->find();
                    if ($qtitle)
                    {
                        $title[$qtitle['id']] = $qtitle['question_title'];
                    }
                }

                if ($title)
                {
                    $times = time();
                    $qamod = M('QuestionAnswer');
                    foreach ($title as $ke => $va)
                    {

                        $content = curl_https_get($urls.trim($va));
                        $datas[$ke]['question_id']    = $ke;
                        $datas[$ke]['question_title'] = $title[$ke];
                        $datas[$ke]['add_time']       = $times;
                        $strBodyi                     = ($strBody[$ke]);
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
                                            $strBodyi .= '<div  style="float:left;margin:2px;font-size:12px;"><div><img src="'.$item['pic_size2'].'" width="130" height="170"/></div><div style=" text-overflow:ellipsis; white-space:nowrap; overflow:hidden; margin:2px 0;color:#0009;max-width:130px"><a style="'.$item['id'].'">'.$item['title'].'</a></div></div>';
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

                            if ( $strBodyi ) $datas[$ke]['contents'] = $strBodyi;
                        }

                         //获取分类
                         $qres = $admin->field('type_id')->where('id='.$ke)->find();
                         $datas[$ke]['type_id'] = $qres['type_id'];

                        //此问题是否已经有答案，若有则删除，然后再录入新答案
                        $qresult = $qamod->field('id')->where('question_id='.$ke)->find();
                        if ($qresult)
                        {
                            if ($qamod->where('question_id='.$ke)->delete())
                            {
                                if($qamod->add($datas[$ke]))
                                    $tstr .= '问题：' . $ke .'添加成功！';
                                else
                                    $tstr .= '问题：' . $ke .'添加失败！';
                            }
                            else
                            {
                                $tstr .= '问题：' . $ke .'删除失败！';
                            }
                        }
                        else
                        {
                            if($qamod->add($datas[$ke]))
                                $tstr .= '问题：' . $ke .'添加成功！';
                            else
                                $tstr .= '问题：' . $ke .'添加失败！！';
                        }

                    }
                }
            }
            if ($tstr)
                $this->ajaxReturn(array('error'=>$tstr,'status'=>1));
            else
                $this->ajaxReturn(array('error'=>C('ERR_STRING'),'status'=>-1));
        }
        else
        {
            redirect(U('Questions/questionsList'),3,C('FFCZ_STRING'));
            return;
        }
    }
    /**
     * 为对答机器人添加问题
     * @author wepean
     * @return json
     */
    public function getRobotQuestion()
    {
        if (IS_AJAX)
        {
            $id = I('post.id');
            if ( ! $id) $this->ajaxReturn(array('error'=>C('CSCW_STRING'),'status'=>-1));
            $admin = M('QuestionAnswer');
            $qamod = M('RobotAnswer');
            $urls  = "http://elk.dooland.com/v1/search";  //接口获取答案
            $ids   = array_filter(explode(',',$id));      //合并值并去空
            $title = array();
            $tstr  = '';
            if ($ids)
            {
                $times = time();
                foreach ($ids as $ke => $va)
                {
                    $qtitle = $admin->field('id,question_title,contents')->where('question_id='.$va .' and status=1')->find();
                    if ($qtitle)
                    {
                       // $title['q'] = array('id'=>$qtitle['id'],'question_id'=>$v,'contents'=>$qtitle['question_title']);
                        $title[$va]['q'] = $qtitle['question_title'];
                        $title[$va]['a'] = $qtitle['contents'];
                        $strBodyi        = ($qaputi.$va);
                        $strHeadi        = ($qaput.$va);
                        if ($qtitle['question_title'] && $qtitle['contents'])
                        {
                            //提交问题给对答机器人
                            $strBodyi        = curl_https_put($urls,json_encode($title[$va]));
                            $strHeadi        = json_decode($strBodyi,true);

                            $datas[$va]['question_id']    = $va;
                            $datas[$va]['question_title'] = $title[$va]['q'];
                            $datas[$va]['add_time']       = $times;
                            $datas[$va]['contents']       = $strBodyi;
                            if ($strHeadi && ! empty($strHeadi['status']))
                            {
                                $datas[$va]['robot_id']     = $strHeadi['data']['id'];
                                $datas[$va]['version_id']   = $strHeadi['data']['result']['_version'];
                                $datas[$va]['robot_type']   = $strHeadi['data']['result']['_type'];
                                $datas[$va]['robot_result'] = $strHeadi['data']['result']['result'];
                            }
                            //此问题是否已经有答案，若有则删除，然后再录入新答案
                            $qresult = $qamod->field('id')->where('robot_id="'.$strHeadi['data']['id'].'"')->find();
                            if ($qresult)
                            {
                                if ($qamod->where('robot_id="'.$strHeadi['data']['id'].'"')->delete())
                                {
                                    if($qamod->add($datas[$va]))
                                        $tstr .= '问题：' . $va .'添加成功！';
                                    else
                                        $tstr .= '问题：' . $va .'添加失败！';
                                }
                                else
                                {
                                    $tstr .= '问题：' . $va .'删除失败！';
                                }
                            }
                            else
                            {
                                if($qamod->add($datas[$va]))
                                    $tstr .= '问题：' . $va .'添加成功！';
                                else
                                    $tstr .= '问题：' . $va .'添加失败！！';
                            }
                        }
                    }
                }
            }

            if ($tstr)
                $this->ajaxReturn(array('error'=>$tstr,'status'=>1));
            else
                $this->ajaxReturn(array('error'=>C('ERR_STRING'),'status'=>-1));
        }
        else
        {
            redirect(U('Questions/questionsList'),3,C('FFCZ_STRING'));
        }
    }
    /**
     * 获取所有图书与杂志分类(dooland.com)
     * return array
     */
    public function getMagazineBookSort()
    {
        if (IS_AJAX)
        {
            $sorts = $this->getMagazineSort();
            //杂志分类
            if ($sorts['data'])
            {
                foreach ( $sorts['data'] as $k=>$v)
                {
                    //if ($v['s_id']) unset($sorts['data'][$k]);  //去除非顶级分类
                }
                foreach ($sorts['data'] as $k=>$v)
                {
                    $sorts['data'][$k]['type_name'] = $v['sort_name'];
                    $sorts['data'][$k]['types']     = 1;
                    unset($sorts['data'][$k]['id'],$sorts['data'][$k]['sort_name'],$sorts['data'][$k]['s_id']);
                }
            }

            //图书分类
            $sortsii = $this->getBookSorti();
            if ($sortsii['data'])
            {
                foreach ( $sortsii['data'] as $k=>$v)
                {
                    //if ($v['s_id']) unset($sortsii['data'][$k]);//去除非顶级分类
                }
                foreach ($sortsii['data'] as $k=>$v)
                {
                    $sortsii['data'][$k]['type_name'] = $v['sort_name'];
                    $sortsii['data'][$k]['types']     = -1;
                    unset($sortsii['data'][$k]['id'],$sortsii['data'][$k]['sort_name'],$sortsii['data'][$k]['p_id']);
                }
            }

            $mabo  = array_merge($sorts['data'],$sortsii['data']); //合并图书与杂志分类
            if ($mabo)
            {
                $types = M('QuestionType');
                $times = time();
                foreach ($mabo as $ke => $va)
                {
                    $typeResult = $types->field('id')->where('type_name="'.$va['type_name'].'" and types='.$va['types'].'')->find();
                    if ( ! $typeResult)
                    {
                        $data['type_name'] = $va['type_name'];
                        $data['types']     = $va['types'];
                        $data['add_time']  = $times;
                        $data['admin_id']  = get_admin_id();
                        if ( $va['type_name'] )  $types->add($data);
                    }
                }
                $this->ajaxReturn(array('status'=>'1','error'=>C('SUB_STRING')));
            }
            else
            {
                $this->ajaxReturn(array('status'=>'-1','error'=>C('ERR_STRING')));
            }
        }
        else
        {
            $this->ajaxReturn(array('status'=>'-1','error'=>C('FFCZ_STRING')));
        }


    }
    /**
     * 获取所有杂志分类(dooland.com)
     * return json
     */
    protected function getMagazineSort()
    {
        $url   = 'http://public.dooland.com/v1/QAMagazine/getMagazineSorti'; //
        $sorts = curl_https_get($url);
        return $sorts;
    }
    /**
     * 获取所有图书分类(dooland.com)
     * return json
     */
    protected function getBookSorti()
    {
        $url   = 'http://public.dooland.com/v1/QAMagazine/getBookSorti'; //
        $sorts = curl_https_get($url);
        return $sorts;
    }
    /**
     * 批量导入
     * return json
     */
    public function questionsImport()
    {
        if (IS_POST)
        {
            $ids  = I('post.ids');
            $oid  = array('oid'=>8);
            if ( $ids == 2 )
            {
                $qestionList  = M('QuestionList');       //杂志
                $qestionListA = M('QuestionAnswer');
                $questionType = $this->getSorts(1);      //获取杂志与图书分
                $qt           = 2869;                    //表其它 ID
                $urls         = U('Questions/questionsList',$oid);
                $fields       = 'question_id,question_title,contents,type_id,add_time';
                $uploadFiles  = 'Magazine';
            }

            if ( $ids == 3 )
            {
                $qestionList  = M('ArticleQuestionList'); //文章
                $qestionListA = M('ArticleQuestionAnswer');
                $questionType = $this->getSorts(2);      //获取文章分类
                $qt           = 2760;                    //表其它 ID
                $urls         = U('Article/ArticleQuestionsList',$oid);
                $fields       = 'question_id,question_title,remark,article_type_id,add_time';
                $uploadFiles  = 'Article';
            }

            if ( $ids == 4 )
            {
                $qestionList  = M('NurseQuestionList');  //护士笔记问题
                $qestionListA = M('NurseQuestionAnswer');
                $questionType = $this->getSorts(3);      //获取笔记问题分类
                $qt           = 1;                       //表其它 ID
                $urls         = U('NurseNotes/NurseNotesQuestionsList',$oid);
                $fields       = 'question_id,question_title,contents,options,types,article_type_id,add_time';
                $uploadFiles  = 'Nurse';
            }

            $uploadsFile = $this->uploads($uploadFiles);
            if ( ! is_array($uploadsFile)) $this->error($uploadsFile);
            $qresults    = $addQuestion = array();
            if ( ! empty($uploadsFile['files']))
            {
                $qresults = $this->questionsImportAction($uploadsFile['files'],$uploadsFile['exts']);
            }
            if ($qresults)
            {
                $times  = time();
                if ( $ids == 4 )
                {
                    if ($qresults[1][0] != '问题名' OR $qresults[1][1] != '问题答案' OR $qresults[1][2] != '问题选项' OR $qresults[1][3] != '题型')
                    {
                        $this->error('导入内容有误，请确定导航栏目是否为问题名、问题选项、问题答案、题型，且顺序正确！','',6);
                    }
                }
                else
                {
                    if ($qresults[1][0] != '问题名' OR $qresults[1][1] != '问题答案' OR $qresults[1][2] != '提问者')
                    {
                        $this->error('导入内容有误，请确定导航栏目是否为问题名、问题答案、提问者，且顺序正确！','',6);
                    }
                }

                unset($qresults[1]);
                $word        = A('Api/Index');
                $magaSortId  =  $str = $stri = $strl = $stra = $qre = '';
                foreach ( $qresults as $j => $v )
                {
                    if ( (trim($v[0]) OR ! empty(trim($v[0]))) && trim($v[0]) != '[]' )
                    {
                        //相同问题不再插入数据库
                      /*  $qre = $qestionList->field('id')
                                          // ->where('MATCH(question_title) AGAINST("*' . str_filteri($v[0]) . '*" IN BOOLEAN MODE)')
                                           ->where('question_title="' . str_filteri($v[0]) . '"')
                                           ->find();*/
                        $qre = '';
                        if ( ! $qre )
                        {
                            $addQuestion[$j]['question_title'] = str_filteri($v[0]);
                            $addQuestion[$j]['add_time']       = $times;
                            $addQuestion[$j]['admin_id']       = get_admin_id();
                            $addQuestion[$j]['ip']             = get_client_ip();

                            if ( $ids == 2 OR $ids == 3 )
                            {
                                $addQuestion[$j]['consultanter']   = ! empty(trim($v[2])) ? trim($v[2]) : get_admin_tname();
                            }
                            //笔记问题
                            if ( $ids == 4 )
                            {
                                $addQuestion[$j]['options'] = trim($v[2]);  //问题选项
                                $addQuestion[$j]['types']   = trim($v[3]);  //题型
                            }

                            //分类匹配
                            if ( $questionType )
                            {
                                $words  = $word->getWords($v[0]);
                                foreach ( $questionType as $k => $ve )
                                {
                                    foreach ( $words as $item )
                                    {
                                        if ( stripos('"'.$item.'"',$ve['type_name']) OR '"'.$item.'"' == $ve['type_name']) $magaSortId = $ve['id'];
                                    }
                                }
                            }
                            $magaSortIdi = $magaSortId ? $magaSortId : $qt;

                            if ( $ids == 2 ) $addQuestion[$j]['type_id'] = $magaSortIdi;
                            if ( $ids == 3 OR $ids == 4 ) $addQuestion[$j]['article_type_id'] = $magaSortIdi;

                            $qids = $qestionList->add($addQuestion[$j]);

                            if ( $qids )
                            {
                                $addQuestion[$j]['question_id'] = $qids;
                                $addQuestion[$j]['contents']    = trim($v[1]);
                                $addQuestion[$j]['remark']      = trim($v[1]);
                                $qad  = $qestionListA->field($fields)->data($addQuestion[$j])->add();

                                if ( ! $qad ) $stra .= '【'.$v[0].'】';
                            }
                            else
                            {
                                $strl .= '【'.$v[0].'】';
                            }

                        }
                        else
                        {
                            $stri .= '【'.$v[0].'】';
                        }
                    }
                    else
                    {
                        $str .= '【'.$v[0].'】';
                    }
                }
            }

            if ( $str OR $stri OR $strl OR $stra)
            {
                if ( $stri )  $this->error('问题：'. $stri . '已经存在！',$urls,10);
                if ( $strl )  $this->error('问题：'. $strl . '等问题导入失败！',$urls,10);
                if ( $stra )  $this->error('问题：'. $strl . '等问题答案导入失败！',$urls,10);
                if ( $str )   $this->error('问题：'. $str . '等问题导入失败(原因：问题名不能这空)！',$urls,10);
            }
            else
                $this->success(C('SUB_STRING'),$urls);

        }
        else
        {
            $uid = I('get.uid');
            if ( $uid == 'a')
                $ud = 3 ;
            elseif ( $uid == 'n')
                $ud = 4 ;
            else
                $ud = 2;
            $this->assign('ud',$ud);
            $this->display();
        }

    }


    /**
     * excel文件上传
     * return  string
     */
    public function uploads($uploadFiles)
    {
        header("Content-Type:text/html;charset=utf-8");
        $upload            =  new \Think\Upload();   // 实例化上传类
        $upload->maxSize   =  20*1024*1024;          // 设置附件上传大小
        $upload->exts      =  array('xls', 'xlsx');  // 设置附件上传类
        $upload->rootPath  =  './Uploadsi/'.$uploadFiles . '/';          // 设置附件上传根目录
        $upload->savePath  =  '';                    // 设置附件上传（子）目录
        if ( !is_dir($upload->rootPath)) mkdir($upload->rootPath);   //目录不存在，就新建

        $info = $upload->upload(); // 上传文件
        if ( empty($info))
        {
            $filename = $upload->getError();// 上传错误提示错误信息
        }
        else
        {
            $file = $upload->rootPath . $info["excelData"]['savepath'] . $info["excelData"]['savename'];
            if ($file == $upload->rootPath) $file = '';
            $exts      = $info["excelData"]['ext'];
            $filename  = array('files'=>$file,'exts'=>$exts);
        }

        return $filename;
    }
    /**
     * 导出分类
     */
    public function questionsTypeExport()
    {
        $admin_list = M('QuestionType')->field('id,type_name,description')->select();
        $files      = '问题分类_';
        $headArr    = array('ID','分类名','分类描述');
        if ($headArr && $admin_list)
            $this->getExcels($files,$headArr,$admin_list);
        else
            $this->error(C('ERR_STRING'));

    }

    /**
     * 获取所有图书与杂志或文章分类
     * @param int $id 1为杂志
     * return array
     */
    public function getSorts( $id = 1 )
    {
        if ( ! $id ) return false;
        $fields = 'id,type_name';
        $where  = 'status=1';
        if ( $id == 1)
            $mType = M('QuestionType');
        elseif ( $id == 3 )
            $mType = M('NurseQuestionType');
        elseif ( $id == 4 )
            $mType = M('NurseArticleQuestionType');
        else
            $mType = M('ArticleQuestionType');

        $sorts = $mType->field($fields)->where($where)->select();
        return $sorts;
    }

}
