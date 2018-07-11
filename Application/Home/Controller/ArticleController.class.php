<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 文章版问题管理控制器
 * @author wepean
 */
class ArticleController extends CommonController
{
    /**
     * 初始化
     */
    public function _initialize()
    {
        set_time_limit(0);
    }
    /**
     * 文章版问题分类添加
     * @author wepean
     * @return string
     */
    public function articleQuestionsType()
    {
        if (IS_POST)
        {
            //防止重复提交 如果重复提交跳转至相关页面
            if ( ! checkToken(I('post.TOKEN')))
            {
                redirect(U('Article/articleQuestions'));
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
                $dataid = M('ArticleQuestionType')->addAll($datas);
                if ( $dataid )
                    $this->success(C('SUB_STRING'),U('Article/articleTypeList'));
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
     * 添加新文章版问题
     * @author wepean
     * @return string
     */
    public function articleQuestionsAdd()
    {
       if (IS_POST)
       {
           //防止重复提交 如果重复提交跳转至相关页面
           if ( ! checkToken(I('post.TOKEN')))
           {
               redirect(U('Article/articleQuestionsAdd'));
               return;
           }
           $typeName    = I('post.question_title');
           $description = I('post.description');
           $type_id     = I('post.type_id');
           $datas       = array();
           if ( $typeName )
           {
               foreach ($typeName as $k => $v)
               {
                   $datas[] = array( 'question_title' => $v,
                                       'description'  => $description[$k],
                                       'add_time'     => time(),
                                       'admin_id'     => get_admin_id(),
                                       'ip'           => get_client_ip(),
                                       'consultanter' => get_admin_tname(),
                                       'article_type_id'  => $type_id[$k]
                                   );
               }
               //数据批量录入
               $dataid = M('ArticleQuestionList')->addAll($datas);
               if ( $dataid )
                   $this->success(C('SUB_STRING'),U('Article/articleQuestionsList',array('oid'=>8)));
               else
                   $this->error(C('ERR_STRING'));
           }
           else
           {
               $this->error(C('ERR_STRING'));
           }

       }
       //文章版问题分类
       $typeInfo = M('ArticleQuestionType')->field('id,type_name')->order('id desc')->where('status=1')->select();
       $strings .= '<select name="type_id[]" lay-verify="required" style="width:100%"  class="form-control">';
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
     * 文章版问题列表
     * @author wepean
     * @return array
     */
    public function articleQuestionsList()
    {
        import('Org.Util.Page');   //导入分页类
        $listRows  = I('get.pagesize','30','intval');
        $page      = I('get.p') ? I('get.p'):0;
        $param     = '&pagesize='.$listRows;
        $oid       = I('get.oid');
        $where     = array();
        $left      = '';
        if ( $oid == 1 OR $oid == 8 )   $left = 'LEFT JOIN ';
        if ( $oid == 2 ) $where['a.contents'] = array('exp','IS NULL');     //无答案
        if ( $oid == 3 ) $where['a.contents'] = array('exp','IS NOT NULL'); //有答案
        if ( $oid == 4 ) $where['l.article_status']   = array('eq','-1');
        if ( $oid == 5 ) $where['l.article_status']   = array('eq','1');
        if ( $oid == 6 ) $where['l.is_robot_learning'] = array('eq','1');
        if ( $oid == 7 ) $where['l.article_type_id']  = array('eq','0');
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

        $admin = M('ArticleQuestionList');
        //总数
        $count = $admin->alias('l')
                       ->field('l.id')
                       ->where($where)
                       ->join('' . $left . '__ARTICLE_QUESTION_ANSWER__ a ON a.question_id=l.id')
                       ->count();

        if(($page-1)*$listRows >= $count && ($page>1 OR !is_numeric($page))) $this->error('该分页暂无记录!');
        $Page = new \Page($count,$listRows,$param);
        $show = $Page->PageShow();
        $admin_list = $admin->alias('l')
                            ->field('l.id,l.question_title,l.consultanter,l.add_time,l.article_status,l.is_robot_learning,l.admin_id,l.article_type_id,a.contents')
                            ->join('' . $left . '__ARTICLE_QUESTION_ANSWER__ a ON a.question_id=l.id')
                            ->where($where)
                            ->limit($listRows)
                            ->page($page)
                            ->order('l.id desc')
                            ->select();
        foreach ($admin_list as $k => $v)
        {
            $adminInfo = M('admin')->field('tname')->where('id='.$admin_list[$k]['admin_id'])->find();
            $admin_list[$k]['admin_id'] =  $adminInfo['tname'] ? $adminInfo['tname'] : '--';
            $typeInfo = M('ArticleQuestionType')->field('type_name')->where('id='.$v['article_type_id'])->find();
            $admin_list[$k]['article_type_id'] =  $typeInfo['type_name'] ? $typeInfo['type_name'] : '--';
        }

        $this->assign('pagesize',$listRows);
        $this->assign('page',$show);
        $this->assign('admin_list',$admin_list);
        $this->display();
    }


    /**
     * 文章版分类列表
     * @author wepean
     * @return string
     */
    public function articletypeList()
    {
        import('Org.Util.Page');// 导入分页类
        $listRows  = I('get.pagesize','30','intval');
        $page      = I('get.p') ? I('get.p'):0;
        $param     = '&pagesize='.$listRows;
        $admin     = M('ArticleQuestionType');
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
    public function articleTypeDel()
    {
        if (IS_AJAX)
        {
            $id = I('id');
            if ( ! $id) $this->ajaxReturn(array('status'=>'-1','error'=>C('CSCW_STRING')));
            $admin      = M("ArticleQuestionType");
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
             redirect(U('Article/articleTypeList'),3,C('FFCZ_STRING'));
        }
    }
    /**
     * 删除文章版问题
     * @author wepean
     * @return json
     */
    public function articleQuestionsDel()
    {
        if (IS_AJAX)
        {
            $id = I('post.id');
            if ( ! $id) $this->ajaxReturn(array('status'=>'-1','error'=>C('CSCW_STRING')));
            $ids = '';
            //批量删除的文章版问题ID
            if ( stripos('"' . $id . '"',',') ) $ids = array_filter(explode(',',$id)); //合并值并去空

            if ( $ids )
            {
                $where['id']           = array('IN',$ids);
                $awhere['question_id'] = array('IN',$ids); //杂志版答案
            }
            else
            {
                $where['id'] = $id;
                $awhere['question_id'] = $id;
            }

            $admin      = M("ArticleQuestionList");
            $admin_info = $admin->where($where)->getField('id',true);
            if ( ! $admin_info) $this->ajaxReturn(array('status'=>'-1','error'=>'文章版问题不存在！'));
            //$admin->startTrans();
            $admin_del  = $admin->where($where)->delete();

            if ( $admin_del )
            {
                M('ArticleQuestionAnswer')->where($awhere)->delete();  //删除文章版问题答案
                $this->ajaxReturn(array('status'=>'1','error'=>C('DSUB_STRING')));
            }
            else
                $this->ajaxReturn(array('status'=>'-1','error'=>C('DERR_STRING')));
        }
        else
        {
            redirect(U('Article/articleQuestionsList'),3,C('FFCZ_STRING'));
        }

    }
    /**
     * 启用|禁用 分类
     * @author wepean
     * @return json
     */
    public function articleTypeStatus()
    {
        $id = I('id');
        if ( ! $id) $this->ajaxReturn(array('status'=>'-1','error'=>C('CSCW_STRING')));
        $admin      = M("ArticleQuestionType");
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
     * 启用|禁用 问题
     * @author wepean
     * @return json
     */
    public function articleQuestionsStatus()
    {
        $id = I('id');
        if ( ! $id) $this->ajaxReturn(array('status'=>'-1','error'=>C('CSCW_STRING')));
        $admin      = M("ArticleQuestionList");
        $questions  = M("ArticleQuestionAnswer");
        $admin_info = $admin->where('id='.$id)->find();
        if ( ! $admin_info) $this->ajaxReturn(array('status'=>'-1','error'=>'文章版问题不存在！'));
        $status = $admin_info['article_status'];
        $str    = '1';
        if ($status == 1)$str = '-1';
        $qaStr  = array('article_status'=>$str);
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
    public function articleTypeEdit()
    {
        $id = I('id');
        if ( ! $id) $this->error(C('CSCW_STRING'),'',3);
        $admin = M('ArticleQuestionType');
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
     * 修改文章版问题
     * @author wepean
     * @return string
     */
    public function articleQuestionsEdit()
    {
        $id = I('id');
        if ( ! $id) $this->error(C('CSCW_STRING'),'',3);
        $admin     = M('ArticleQuestionList');
        $questions = M("ArticleQuestionAnswer");
        //判断是否POST提交
        if (IS_AJAX)
        {
            $id  = I('post.id');
            $admin_info = $admin->where('id='.$id)->find();
            if ( ! $admin_info ) $this->ajaxReturn(array('status'=>'-1','error'=>'文章版问题不存在！'));
            $data['description']     = I('post.description');
            $data['is_new']          = 1;
            $data['question_title']  = I('post.typeName');
            $data['article_status']  = I('post.status');
            $data['article_type_id'] = I('post.type_id');

            $updataID  = $admin->where('id = '.$id)->save($data);
            $answer_up = '';
            $qresult   = $questions->where('question_id='.$id)->find();
            //是否有文章版问题
            if ( $qresult )
            {
                unset( $data['description']);
                $data['remark']   = I('post.remark');
                $answer_up = $questions->where('question_id='.$id)->save($data);
            }

            if ( $updataID OR $answer_up )
                $this->ajaxReturn(array('error'=>C('SUB_STRING'),'status'=>1));
            else
                $this->ajaxReturn(array('error'=>C('ERR_STRING'),'status'=>-1));
        }
        else
        {
            $adminfo  = $admin->where('id='.$id)->find();
            $answere  = $questions->field('contents,remark')->where('question_id='.$id)->find();
            $adminfo['contents'] = $answere['contents'];
            $adminfo['remark']   = $answere['remark'];
            $typeInfo = M('ArticleQuestionType')->field('id,type_name')->where('status=1')->order('id desc')->select();
            $this->assign('adminInfo',$adminfo);
            $this->assign('typeInfo',$typeInfo);
            $this->display();
        }
    }
    /**
     * 批量获取文章版问题答案
     * @author wepean
     * @return string
     */
    public function getArticleQuestionsAnswer()
    {
        //判断是否POST提交
        if (IS_AJAX)
        {
            $id = I('post.id');
            //$id = I('id');
            if ( ! $id) $this->ajaxReturn(array('error'=>C('CSCW_STRING'),'status'=>-1));
            $admin = M('ArticleQuestionList');
            $ids   = array_filter(explode(',',$id)); //合并值并去空
            //接口获取答案
            $urls  = "http://public.dooland.com/v1/QAArticle/getArticleResult/q/";
            $title = array();
            $tstr  = '';
            if ($ids)
            {
                foreach ($ids as $k => $v)
                {
                    $qtitle = $admin->field('id,question_title')->where('id='.$v .' and article_status=1')->find();
                    if ($qtitle)
                    {
                        $title[$qtitle['id']] = $qtitle['question_title'];
                    }
                }

                if ($title)
                {
                    $times = time();
                    $qamod = M('ArticleQuestionAnswer');
                    $strs  = array();
                    foreach ($title as $ke => $va)
                    {
                        $content = curl_https_get($urls.trim($va));
                        $datas[$ke]['question_id']    = $ke;
                        $datas[$ke]['question_title'] = $title[$ke];
                        $datas[$ke]['add_time']       = $times;
                        $strs                         = array();
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

                            $datas[$ke]['contents'] = json_encode($strs);
                        }


                        //获取分类
                        $qres = $admin->field('article_type_id')->where('id='.$ke)->find();
                        $datas[$ke]['article_type_id'] = $qres['article_type_id'];
                        //此文章版问题是否已经有答案，若有则删除，然后再录入新答案
                        $qresult = $qamod->field('id,remark')->where('question_id='.$ke)->find();
                        if ( $qresult )
                        {
                            $datas[$ke]['remark'] = $qresult['remark'];
                            if ($qamod->where('question_id='.$ke)->delete())
                            {
                                if($qamod->add($datas[$ke]))
                                    $tstr .= '文章版问题：' . $ke .'添加成功！';
                                else
                                    $tstr .= '文章版问题：' . $ke .'添加失败！';
                            }
                            else
                            {
                                $tstr .= '文章版问题：' . $ke .'删除失败！';
                            }
                        }
                        else
                        {
                            if($qamod->add($datas[$ke]))
                                $tstr .= '文章版问题：' . $ke .'添加成功！';
                            else
                                $tstr .= '文章版问题：' . $ke .'添加失败！！';
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
            redirect(U('Article/articleQuestionsList'),3,C('FFCZ_STRING'));
            return;
        }
    }

    /**
     * 获取所有文章分类（m.dooland.com）
     * return array
     */
    public function getArticleType()
    {
        if (IS_AJAX)
        {
            $sorts = $this->getMagazineSort();
            //杂志分类
            if ($sorts['data'])
            {
                foreach ( $sorts['data'] as $k=>$v )
                {
                    $sorts['data'][$k]['type_name'] = $v['sort_name'];
                    unset($sorts['data'][$k]['id'],$sorts['data'][$k]['sort_name'],$sorts['data'][$k]['s_id']);
                }
                $types = M('ArticleQuestionType');
                $times = time();
                foreach ($sorts['data'] as $ke => $va)
                {
                    $typeResult = $types->field('id')->where('type_name="'.$va['type_name'].'"')->find();
                    if ( ! $typeResult)
                    {
                        $data['type_name'] = $va['type_name'];
                        $data['types']     = $va['types'];
                        $data['add_time']  = $times;
                        $data['admin_id']  = get_admin_id();
                        if ( $va['type_name'] ) $types->add($data);
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
     * 获取所有文章分类（m.dooland.com）
     * return json
     */
    protected function getMagazineSort()
    {
        $url   = 'http://public.dooland.com/v1/QAArticle/getMagazineSort/str/1'; //
        $sorts = curl_https_get($url);
        return $sorts;
    }


}
