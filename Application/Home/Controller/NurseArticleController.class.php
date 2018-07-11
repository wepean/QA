<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 护士笔记文章版问题管理控制器
 * @author wepean
 */
class NurseArticleController extends CommonController
{
    /**
     * 初始化
     */
    public function _initialize()
    {
        set_time_limit(0);
    }

    /**
     * 护士笔记文章版问题分类添加
     * @author wepean
     * @return string
     */
    public function nurseArticleQuestionsType()
    {
        if (IS_POST)
        {
            //防止重复提交 如果重复提交跳转至相关页面
            if (!checkToken(I('post.TOKEN')))
            {
                redirect(U('NurseArticle/nurseArticleQuestions'));
                return;
            }
            $typeName    = I('post.type_name');
            $description = I('post.description');
            $datas       = array();
            if ($typeName)
            {
                foreach ($typeName as $k => $v)
                {
                    $datas[] = array('type_name'      => $v,
                                        'description' => $description[$k],
                                        'add_time'    => time(),
                                        'admin_id'    => get_admin_id()
                                    );
                }
                //数据批量录入
                $dataid = M('NurseArticleQuestionType')->addAll($datas);
                if ($dataid)
                    $this->success(C('SUB_STRING'), U('NurseArticle/nurseArticleTypeList'));
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
     * 添加新护士笔记文章版问题
     * @author wepean
     * @return string
     */
    public function nurseArticleQuestionsAdd()
    {
        if (IS_POST)
        {
            //防止重复提交 如果重复提交跳转至相关页面
            if (!checkToken(I('post.TOKEN')))
            {
                redirect(U('NurseArticle/nurseArticleQuestionsAdd'));
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
                    $datas[] = array('question_title'  => $v,
                                        'description'  => $description[$k],
                                        'add_time'     => time(),
                                        'admin_id'     => get_admin_id(),
                                        'ip'           => get_client_ip(),
                                        'consultanter' => get_admin_tname(),
                                        'article_type_id' => $type_id[$k]
                                    );
                }
                //数据批量录入
                $dataid = M('NurseArticleQuestionList')->addAll($datas);
                if ($dataid)
                    $this->success(C('SUB_STRING'), U('NurseArticle/nurseArticleQuestionsList'));
                else
                    $this->error(C('ERR_STRING'));
            }
            else
            {
                $this->error(C('ERR_STRING'));
            }

        }
        //护士笔记文章版问题分类
        $typeInfo = M('NurseArticleQuestionType')->field('id,type_name')->order('id desc')->where('status=1')->select();
        $strings .= '<select name="type_id[]" lay-verify="required" style="width:100%"  class="form-control">';
        foreach ($typeInfo as $k => $v)
        {
            $strings .= '<option value="' . $v['id'] . '">' . $v['type_name'] . '</option>';
        }
        $strings .= '</select>';
        $this->assign('typeInfo', $strings);
        creatToken();  //创建token
        $this->display();
    }

    /**
     * 护士笔记文章版问题列表
     * @author wepean
     * @return array
     */
    public function nurseArticleQuestionsList()
    {
        import('Org.Util.Page');   //导入分页类
        $listRows = I('get.pagesize', '30', 'intval');
        $page     = I('get.p') ? I('get.p') : 0;
        $param    = '&pagesize=' . $listRows;
        $oid      = I('get.oid');
        $where    = array();
        $left     = '';
        if ( $oid == 1 OR $oid == 8 )   $left = 'LEFT JOIN ';               //全部
        if ($oid == 2) $where['a.contents'] = array('exp', 'IS NULL');       //无答案
        if ($oid == 3) $where['a.contents'] = array('exp', 'IS NOT NULL'); //有答案
        if ($oid == 4) $where['l.article_status'] = array('eq', '-1');
        if ($oid == 5) $where['l.article_status'] = array('eq', '1');
        if ($oid == 6) $where['l.is_robot_learning'] = array('eq', '1');     //机器人学习
        //if ($oid == 7) $where['l.article_type_id'] = array('eq', '0');
        if ( $oid == 8 )$where['l.is_robot_learning'] = array('eq','-1');

        if ($oid) $this->assign('oid', $oid);
        //搜索
        if ( IS_POST )
        {
            $qid    = I('post.questionsId', '', 'intval');
            $kyword = I('post.keywords');
            if ($qid) $where['l.id'] = $qid;
            if ($kyword) $where['l.question_title'] = array('like', array('%' . $kyword . '%', '' . $kyword . '%'), 'OR');
            $this->assign('qid', $qid);
            $this->assign('kyword', $kyword);
        }

        $admin = M('NurseArticleQuestionList');
        //总数
        $count =  $admin->alias('l')
                        ->field('l.id')
                        ->where($where)
                        ->join('' . $left . '__NURSE_ARTICLE_QUESTION_ANSWER__ a ON a.question_id=l.id')
                        ->count();

        if (($page - 1) * $listRows >= $count && ($page > 1 OR !is_numeric($page))) $this->error('该分页暂无记录!');
        $Page = new \Page($count, $listRows, $param);
        $show = $Page->PageShow();
        $admin_list = $admin->alias('l')
                            ->field('l.id,l.question_title,l.consultanter,l.add_time,l.article_status,l.is_robot_learning,l.admin_id,l.article_type_id,a.contents')
                            ->join('' . $left . '__NURSE_ARTICLE_QUESTION_ANSWER__ a ON a.question_id=l.id')
                            ->where($where)
                            ->limit($listRows)
                            ->page($page)
                            ->order('l.id desc')
                            ->select();
        foreach ( $admin_list as $k => $v )
        {
            $adminInfo = M('admin')->field('tname')->where('id=' . $admin_list[$k]['admin_id'])->find();
            $admin_list[$k]['admin_id'] = $adminInfo['tname'] ? $adminInfo['tname'] : '--';
            $typeInfo = M('NurseArticleQuestionType')->field('type_name')->where('id=' . $v['article_type_id'])->find();
            $admin_list[$k]['article_type_id'] = $typeInfo['type_name'] ? $typeInfo['type_name'] : '--';
        }

        $this->assign('pagesize', $listRows);
        $this->assign('page', $show);
        $this->assign('admin_list', $admin_list);
        $this->display();
    }


    /**
     * 护士笔记文章版分类列表
     * @author wepean
     * @return string
     */
    public function nurseArticleTypeList()
    {
        import('Org.Util.Page');// 导入分页类
        $listRows = I('get.pagesize', '30', 'intval');
        $page     = I('get.p') ? I('get.p') : 0;
        $param    = '&pagesize=' . $listRows;
        $admin    = M('NurseArticleQuestionType');
        $where    = array();
        //搜索
        if (IS_POST)
        {
            $kyword = I('post.keywords');
            if ($kyword) $where['type_name'] = array('like', array('%' . $kyword . '%', '' . $kyword . '%'), 'OR');
            $this->assign('kyword', $kyword);
        }

        $count = $admin->where($where)->count();
        if (($page - 1) * $listRows >= $count && ($page > 1 OR !is_numeric($page))) $this->error('该分页暂无记录!');
        $Page = new \Page($count, $listRows, $param);
        $show = $Page->PageShow();
        $admin_list = $admin->field('id,type_name,description,add_time,status,is_new,admin_id')
                            ->where($where)
                            ->limit($listRows)
                            ->page($page)
                            ->order('id desc')
                            ->select();
        foreach ( $admin_list as $k => $v )
        {
            $adminInfo = M('admin')->field('tname')->where('id=' . $admin_list[$k]['admin_id'])->find();
            $admin_list[$k]['admin_id'] = $adminInfo['tname'];
        }
        $this->assign('pagesize', $listRows);
        $this->assign('page', $show);
        $this->assign('admin_list', $admin_list);

        $this->display();
    }

    /**
     * 删除分类
     * @author wepean
     * @return json
     */
    public function nurseArticleTypeDel()
    {
        if (IS_AJAX)
        {
            $id = I('id');
            if (!$id) $this->ajaxReturn(array('status' => '-1', 'error' => C('CSCW_STRING')));
            $admin = M("NurseArticleQuestionType");
            $admin_info = $admin->where('id=' . $id)->find();
            if (!$admin_info) $this->ajaxReturn(array('status' => '-1', 'error' => '分类不存在！'));
            $admin_del = $admin->where('id=' . $id)->delete();
            if ($admin_del)
                $this->ajaxReturn(array('status' => '1', 'error' => C('DSUB_STRING')));
            else
                $this->ajaxReturn(array('status' => '-1', 'error' => C('DERR_STRING')));
        }
        else
        {
            redirect(U('NurseArticle/nurseArticleTypeList'), 3, C('FFCZ_STRING'));
        }
    }

    /**
     * 删除护士笔记文章版问题
     * @author wepean
     * @return json
     */
    public function nurseArticleQuestionsDel()
    {
        if ( IS_AJAX )
        {
            $id = I('post.id');
            if (!$id) $this->ajaxReturn(array('status' => '-1', 'error' => C('CSCW_STRING')));
            $ids = '';
            //批量删除的护士笔记文章版问题ID
            if (stripos('"' . $id . '"', ',')) $ids = array_filter(explode(',', $id)); //合并值并去空

            if ( $ids )
            {
                $where['id'] = array('IN', $ids);
                $awhere['question_id'] = array('IN', $ids); //杂志版答案
            }
            else
            {
                $where['id'] = $id;
                $awhere['question_id'] = $id;
            }

            $admin = M("NurseArticleQuestionList");
            $admin_info = $admin->where($where)->getField('id', true);
            if (!$admin_info) $this->ajaxReturn(array('status' => '-1', 'error' => '护士笔记文章版问题不存在！'));
            //$admin->startTrans();
            $admin_del = $admin->where($where)->delete();

            if ($admin_del)
            {
                M('NurseArticleQuestionAnswer')->where($awhere)->delete();  //删除护士笔记文章版问题答案
                $this->ajaxReturn(array('status' => '1', 'error' => C('DSUB_STRING')));
            }
            else
            {
                $this->ajaxReturn(array('status' => '-1', 'error' => C('DERR_STRING')));
            }

        }
        else
        {
            redirect(U('NurseArticle/nurseArticleQuestionsList'), 3, C('FFCZ_STRING'));
        }

    }

    /**
     * 启用|禁用 分类
     * @author wepean
     * @return json
     */
    public function nurseArticleTypeStatus()
    {
        if ( IS_AJAX OR IS_POST )
        {
            $id   = I('id');
            if ( ! $id ) $this->ajaxReturn(array('status' => '-1', 'error' => C('CSCW_STRING')));
            $admin      = M("NurseArticleQuestionType");
            $admin_info = $admin->where('id=' . $id)->find();
            if ( ! $admin_info ) $this->ajaxReturn(array('status' => '-1', 'error' => '分类不存在！'));
            $status = $admin_info['status'];
            if ( $status == 1 )
            {
                $admin_up = $admin->where('id=' . $id)->save(array('status' => '-1'));
                if ( $admin_up )
                    $msg = array('status' => '1', 'error' => C('SUB_STRING'));
                else
                    $msg = array('status' => '-1', 'error' => C('ERR_STRING'));
            }
            else
            {
                $admin_up = $admin->where('id=' . $id)->save(array('status' => '1'));
                if ( $admin_up )
                    $msg = array('status' => '1', 'error' => C('SUB_STRING'));
                else
                    $msg = array('status' => '-1', 'error' => C('ERR_STRING'));
            }

            $this->ajaxReturn($msg);
        }
        else
        {
            redirect(U('NurseArticle/nurseArticleTypeList'), 3, C('FFCZ_STRING'));
        }

    }

    /**
     * 启用|禁用 问题
     * @author wepean
     * @return json
     */
    public function nurseArticleQuestionsStatus()
    {
        if ( IS_AJAX OR IS_POST )
        {
            $id = I('id');
            if ( ! $id ) $this->ajaxReturn(array('status' => '-1', 'error' => C('CSCW_STRING')));
            $admin      = M("NurseArticleQuestionList");
            $questions  = M("NurseArticleQuestionAnswer");
            $admin_info = $admin->where('id=' . $id)->find();
            if ( ! $admin_info ) $this->ajaxReturn(array('status' => '-1', 'error' => '护士笔记文章版问题不存在！'));
            $status     = $admin_info['article_status'];
            $str        = '1';
            if ($status == 1) $str = '-1';
            $qaStr      = array('article_status' => $str);
            $admin->startTrans(); //启用回滚事务
            $admin_up  = $admin->where('id=' . $id)->save($qaStr);

            if ( $admin_up )
            {
                $qresult = $questions->where('question_id=' . $id)->find();

                if ( $qresult )
                {
                    $answer_up = $questions->where('question_id=' . $id)->save($qaStr);

                    if ( $answer_up )
                        $admin->commit();
                    else
                        $admin->rollback(); //回滚
                }
                else
                {
                    $admin->commit();
                }

                $msg = array('status' => '1', 'error' => C('SUB_STRING'));
            }
            else
            {
                $admin->rollback();//回滚
                $msg = array('status' => '-1', 'error' => C('ERR_STRING'));
            }

            $this->ajaxReturn($msg);
        }
        else
        {
            redirect(U('NurseArticle/nurseArticleQuestionsList'), 3, C('FFCZ_STRING'));
        }

    }

    /**
     * 修改分类资料
     * @author wepean
     * @return string
     */
    public function nurseArticleTypeEdit()
    {
        $id = I('id');
        if (!$id) $this->error(C('CSCW_STRING'), '', 3);
        $admin = M('NurseArticleQuestionType');
        //判断是否POST提交
        if ( IS_AJAX )
        {
            $id                  = I('post.id');
            $data['type_name']   = I('post.typeName');
            $data['description'] = I('post.description');
            $data['is_new']      = 1;
            //$data['status']      = 1;
            $updataID = $admin->where('id = ' . $id)->save($data);
            if ($updataID)
                $this->ajaxReturn(array('error' => C('SUB_STRING'), 'status' => 1));
            else
                $this->ajaxReturn(array('error' => C('ERR_STRING'), 'status' => -1));
        }
        else
        {
            $adminfo = $admin->where('id=' . $id)->find();
            $this->assign('adminInfo', $adminfo);
            $this->display();
        }
    }

    /**
     * 修改护士笔记文章版问题
     * @author wepean
     * @return string
     */
    public function nurseArticleQuestionsEdit()
    {
        $id = I('id');
        if (!$id ) $this->error(C('CSCW_STRING'), '', 3);
        $admin      = M('NurseArticleQuestionList');
        $questions  = M("NurseArticleQuestionAnswer");
        $admin_info = $admin->where('id=' . $id)->find();
        if ( ! $admin_info ) $this->error('护士笔记文章版问题不存在！');
        //判断是否POST提交
        if ( IS_AJAX )
        {
            $data['description']     = I('post.description');
            $data['is_new']          = 1;
            $data['question_title']  = I('post.typeName');
            $data['article_status']  = I('post.status');
            $data['article_type_id'] = I('post.type_id');

            $updataID  = $admin->where('id = ' . $id)->save($data);
            $answer_up = '';
            $qresult   = $questions->where('question_id=' . $id)->find();
            //是否有护士笔记文章版问题
            if ( $qresult )
            {
                $data['remark'] = I('post.remark');
                $answer_up = $questions->field('is_new,question_title,article_status,article_type_id,remark')->where('question_id=' . $id)->save($data);
            }

            if ( $updataID OR $answer_up )
                $this->ajaxReturn(array('error' => C('SUB_STRING'), 'status' => 1));
            else
                $this->ajaxReturn(array('error' => C('ERR_STRING'), 'status' => -1));
        }
        else
        {
            $answere  = $questions->where('question_id=' . $id)->find();
            $typeInfo = M('NurseArticleQuestionType')->field('id,type_name')->where('status=1')->order('id desc')->select();
            $this->assign('adminInfo', $answere);
            $this->assign('typeInfo', $typeInfo);
            $this->display();
        }
    }

    /**
     * 批量获取护士笔记文章版问题答案
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
            if (!$id) $this->ajaxReturn(array('error' => C('CSCW_STRING'), 'status' => -1));
            $admin = M('NurseArticleQuestionList');
            $ids   = array_filter(explode(',', $id)); //合并值并去空
            $title = array();
            $tstr = '';
            if ($ids)
            {
                foreach ($ids as $k => $v)
                {
                    $qtitle = $admin->field('id,question_title')->where('id=' . $v . ' and article_status=1')->find();
                    if ($qtitle)
                    {
                        $title[$qtitle['id']] = $qtitle['question_title'];
                    }
                }

                if ($title)
                {
                    $times      = time();
                    $qamod      = M('NurseArticleQuestionAnswer');
                    $strs       = array();
                    $apiArticle = A('Api/Index');
                    foreach ($title as $ke => $va)
                    {
                        $content = $apiArticle->getNurseArticleQuestionAnswer(trim($va)); //获取答案
                        $datas[$ke]['question_id']    = $ke;
                        $datas[$ke]['question_title'] = $title[$ke];
                        $datas[$ke]['add_time']       = $times;
                        $strs                         = array();

                        if ( $content )
                        {
                            $datas[$ke]['contents'] = json_encode($content);
                        }

                        //获取分类
                        $qres = $admin->field('article_type_id')->where('id=' . $ke)->find();
                        $datas[$ke]['article_type_id'] = $qres['article_type_id'];
                        //此护士笔记文章版问题是否已经有答案，若有则删除，然后再录入新答案
                        $qresult = $qamod->field('id,remark')->where('question_id=' . $ke)->find();
                        if ( $qresult )
                        {
                            $datas[$ke]['remark'] = $qresult['remark'];

                            if ( $qamod->where('question_id=' . $ke)->delete() )
                            {
                                if ( $qamod->add($datas[$ke]) )
                                    $tstr .= '护士笔记文章版问题：' . $ke . '添加成功！';
                                else
                                    $tstr .= '护士笔记文章版问题：' . $ke . '添加失败！';
                            }
                            else
                            {
                                $tstr .= '护士笔记文章版问题：' . $ke . '删除失败！';
                            }

                        }
                        else
                        {

                            if ( $qamod->add($datas[$ke]) )
                                $tstr .= '护士笔记文章版问题：' . $ke . '添加成功！';
                            else
                                $tstr .= '护士笔记文章版问题：' . $ke . '添加失败！！';

                        }
                    }
                }
            }
            if ( $tstr )
                $this->ajaxReturn(array('error' => $tstr, 'status' => 1));
            else
                $this->ajaxReturn(array('error' => C('ERR_STRING'), 'status' => -1));
        }
        else
        {
            redirect(U('NurseArticle/nurseArticleQuestionsList'), 3, C('FFCZ_STRING'));
        }
    }

    /**
     * 添加新护士笔记文章
     * @author wepean
     * @return string
     */
    public function nurseArticleAdd()
    {
        if ( IS_POST )
        {
            $title        = I('post.title');
            $description  = I('post.description');
            $publishTime  = I('post.publishtime');
            $author       = I('post.author');
            $content      = I('post.content');
            $typeId       = I('post.type_id');

            if ( $title && $content )
            {
                $times                = time();
                $data['title']        = $title;
                $data['description']  = $description;
                $data['publish_time'] = $publishTime ? $publishTime : date('Y-m-d H:i');
                $data['author']       = $author;
                $data['content']      = $content;
                $data['type_id']      = $typeId;
                $data['add_time']     = $times;
                $data['admin_id']     = get_admin_id();
                $data['admin_name']   = get_admin_tname();

                //数据批量录入
                $dataid = M('NurseArticle')->add($data);

                if ( $dataid )
                    $arr = array('error' => C('SUB_STRING'), 'status' => 1,'urls'=>U('NurseArticle/nurseArticleList'));
                else
                    $arr = array('error' => C('ERR_STRING'), 'status' => -1);
            }
            else
            {
                $arr = array('error' => C('CSCW_STRING'), 'status' => -1);
            }

            $this->ajaxReturn($arr);

        }
        else
        {
            //护士笔记文章版问题分类
            $typeInfo = M('NurseArticleQuestionType')->field('id,type_name')->order('id desc')->where('status=1')->select();
            $this->assign('typeInfo', $typeInfo);
           // creatToken();  //创建token
            $this->display();
        }

    }
    /**
     * 护士笔记文章列表
     * @author wepean
     * @return string
     */
    public function nurseArticleList()
    {
        import('Org.Util.Page');// 导入分页类
        $listRows = I('get.pagesize', '30', 'intval');
        $page     = I('get.p') ? I('get.p') : 0;
        $param    = '&pagesize=' . $listRows;
        $admin    = M('NurseArticle');
        $where    = array();
        //搜索
        if (IS_POST)
        {
            $kyword = I('post.keywords');
            if ( $kyword ) $where['title'] = array('like', array('%' . $kyword . '%', '' . $kyword . '%'), 'OR');
            $this->assign('kyword', $kyword);
        }

        $count = $admin->where($where)->count();
        if (($page - 1) * $listRows >= $count && ($page > 1 OR !is_numeric($page))) $this->error('该分页暂无记录!');
        $Page  = new \Page($count, $listRows, $param);
        $show  = $Page->PageShow();
        $admin_list = $admin->field('id,title,author,description,add_time,publish_time,is_status,admin_id')
                            ->where($where)
                            ->limit($listRows)
                            ->page($page)
                            ->order('id desc')
                            ->select();

        foreach ( $admin_list as $k => $v )
        {
            $adminInfo = M('admin')->field('tname')->where('id=' . $admin_list[$k]['admin_id'])->find();
            $admin_list[$k]['admin_id'] = $adminInfo['tname'];
        }

        $this->assign('pagesize', $listRows);
        $this->assign('page', $show);
        $this->assign('admin_list', $admin_list);

        $this->display();
    }

    /**
     * 笔记文章修改
     * @author wepean
     * @return string
     */
    public function nurseArticleEdit()
    {
        $id = I('id');
        if (  ! $id ) $this->ajaxReturn(array('error' => C('CSCW_STRING'), 'status' => -1));
        $article     = M('NurseArticle');
        $articleInfo = $article->field('id,title,description,publish_time,author,content,type_id')->where('id=' . $id)->find();
        if ( ! $articleInfo ) $this->ajaxReturn(array('error' => '此文章不存在', 'status' => -1));

        if ( IS_POST )
        {
            $title        = I('post.title');
            $description  = I('post.description');
            $publishTime  = I('post.publishtime');
            $author       = I('post.author');
            $content      = I('post.content');
            $typeId       = I('post.type_id');

            if ( $title && $content )
            {
                $times                = time();
                $data['title']        = $title;
                $data['description']  = $description;
                $data['publish_time'] = $publishTime ? $publishTime : date('Y-m-d H:i');
                $data['author']       = $author;
                $data['content']      = $content;
                $data['type_id']      = $typeId;
                $data['add_time']     = $times;
                $data['admin_id']     = get_admin_id();
                $data['admin_name']   = get_admin_tname();

                //数据批量录入
                $dataid = M('NurseArticle')->where('id='.$id)->save($data);

                if ( $dataid )
                    $arr = array('error' => C('SUB_STRING'), 'status' => 1,'urls'=>U('NurseArticle/nurseArticleList'));
                else
                    $arr = array('error' => C('ERR_STRING'), 'status' => -1);
            }
            else
            {
                $arr = array('error' => C('CSCW_STRING'), 'status' => -1);
            }

            $this->ajaxReturn($arr);

        }
        else
        {
            //护士笔记文章版问题分类
            $typeInfo = M('NurseArticleQuestionType')->field('id,type_name')->order('id desc')->where('status=1')->select();
            $this->assign('typeInfo', $typeInfo);
            $this->assign('articleInfo',$articleInfo);
            $this->display();
        }

    }

    /**
     * 删除文章
     * @author wepean
     * @return json
     */
    public function nurseArticleDel()
    {
        if ( IS_AJAX OR IS_POST )
        {
            $id = I('id');
            if ( ! $id ) $this->ajaxReturn(array('status' => '-1', 'error' => C('CSCW_STRING')));
            $admin      = M("NurseArticle");
            $admin_info = $admin->where('id=' . $id)->find();
            if ( ! $admin_info ) $this->ajaxReturn(array('status' => '-1', 'error' => '文章不存在！'));
            $admin_del  = $admin->where('id=' . $id)->delete();

            if ( $admin_del )
                $arr = array('status' => '1', 'error' => C('DSUB_STRING'));
            else
                $arr = array('status' => '-1', 'error' => C('DERR_STRING'));

            $this->ajaxReturn($arr);
        }
        else
        {
            redirect(U('NurseArticle/nurseArticleList'), 3, C('FFCZ_STRING'));
        }
    }

    /**
     * 启用|禁用 文章
     * @author wepean
     * @return json
     */
    public function nurseArticleStatus()
    {
        if ( IS_AJAX OR IS_POST )
        {
            $id = I('id');
            if ( ! $id ) $this->ajaxReturn(array('status' => '-1', 'error' => C('CSCW_STRING')));
            $admin      = M("NurseArticle");
            $admin_info = $admin->where('id=' . $id)->find();
            if ( ! $admin_info ) $this->ajaxReturn(array('status' => '-1', 'error' => '文章不存在！'));
            $status     = $admin_info['is_status'];
            if ( $status == 1 )
            {
                $admin_up = $admin->where('id=' . $id)->save(array('is_status' => '-1'));
                if ( $admin_up )
                    $msg = array('status' => '1', 'error' => C('SUB_STRING'));
                else
                    $msg = array('status' => '-1', 'error' => C('ERR_STRING'));
            }
            else
            {
                $admin_up = $admin->where('id=' . $id)->save(array('is_status' => '1'));
                if ( $admin_up )
                    $msg = array('status' => '1', 'error' => C('SUB_STRING'));
                else
                    $msg = array('status' => '-1', 'error' => C('ERR_STRING'));
            }

            $this->ajaxReturn($msg);
        }
        else
        {
            redirect(U('NurseArticle/nurseArticleList'), 3, C('FFCZ_STRING'));
        }

    }
    /**
     * 批量导入文章
     * return json
     */
    public function nurseArticleImport()
    {
        if (IS_POST)
        {
            $ids          = I('post.ids');
            $qestionList  = M('NurseArticle'); //文章
            $questions    = A('Questions');
            $questionType = $questions->getSorts(4);     //获取文章分类
            $qt           = 2;                           //表其它 ID
            $urls         = U('NurseArticle/nurseArticleList');
            $uploadFiles  = 'NurseArticle';

            $uploadsFile = $questions->uploads($uploadFiles);
            if ( ! is_array($uploadsFile)) $this->error($uploadsFile);
            $qresults    = $addQuestion = array();
            if ( ! empty($uploadsFile['files']))
            {
                $qresults = $this->questionsImportAction($uploadsFile['files'],$uploadsFile['exts']);
            }
            if ($qresults)
            {
                $times  = time();

                if ($qresults[1][0] != '标题' OR $qresults[1][1] != '内容' OR $qresults[1][2] != '作者')
                {
                    $this->error('导入内容有误，请确定导航栏目是否为标题、内容、作者，且顺序正确！','',6);
                }


                unset($qresults[1]);
                $word        = A('Api/Index');
                $magaSortId  =  $str = $stri = $strl = $qre = '';
                foreach ( $qresults as $j => $v )
                {
                    if ( (trim($v[0]) OR ! empty(trim($v[0]))) && (trim($v[1]) OR ! empty(trim($v[1]))) )
                    {
                        //相同问题不再插入数据库
                       /* $qre = $qestionList->field('id')
                                         // ->where('MATCH(title) AGAINST("*' . str_filteri($v[0]) . '*" IN BOOLEAN MODE)')
                                           ->where('title="' . str_filteri($v[0]) . '"')
                                           ->find();*/
                        $qre = '';
                        if ( ! $qre )
                        {
                            $addQuestion[$j]['title']        = str_filteri($v[0]);
                            $addQuestion[$j]['content']      = $v[1];
                            $addQuestion[$j]['author']       = $v[2];
                            $addQuestion[$j]['publish_time'] = date('Y-m-d H:i',$times);
                            $addQuestion[$j]['add_time']     = $times;
                            $addQuestion[$j]['admin_id']     = get_admin_id();
                            $addQuestion[$j]['ip']           = get_client_ip();

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
                            $qids        = $qestionList->add($addQuestion[$j]);

                            if ( ! $qids )
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

            if ( $str OR $stri OR $strl )
            {
                if ( $stri )  $this->error('问题：'. $stri . '已经存在！',$urls,10);
                if ( $strl )  $this->error('问题：'. $strl . '等文章导入失败！',$urls,10);
                if ( $str )   $this->error('问题：'. $str  . '等文章导入失败(原因：问题名不能这空)！',$urls,10);
            }
            else
                $this->success(C('SUB_STRING'),$urls);

        }
        else
        {
            $this->display();
        }

    }

}