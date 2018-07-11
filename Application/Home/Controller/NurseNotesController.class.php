<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 护士笔记版问题管理控制器
 * @author wepean
 */
class NurseNotesController extends CommonController
{
    /**
     * 初始化
     */
    public function _initialize()
    {
        set_time_limit(0);
    }
    /**
     * 护士笔记版问题分类添加
     * @author wepean
     * @return string
     */
    public function NurseNotesQuestionsType()
    {
        if (IS_POST)
        {
            //防止重复提交 如果重复提交跳转至相关页面
            if ( ! checkToken(I('post.TOKEN')))
            {
                redirect(U('NurseNotes/nurseNotesQuestions'));
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
                $dataid = M('NurseQuestionType')->addAll($datas);
                if ( $dataid )
                    $this->success(C('SUB_STRING'),U('NurseNotes/nurseNotesTypeList'));
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
     * 添加新护士笔记版问题
     * @author wepean
     * @return string
     */
    public function NurseNotesQuestionsAdd()
    {
       if (IS_POST)
       {
           //防止重复提交 如果重复提交跳转至相关页面
           if ( ! checkToken(I('post.TOKEN')))
           {
               redirect(U('NurseNotes/nurseNotesQuestionsAdd'));
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
                                       'arcticle_type_id'  => $type_id[$k]
                                   );
               }
               //数据批量录入
               $dataid = M('NurseQuestionList')->addAll($datas);
               if ( $dataid )
                   $this->success(C('SUB_STRING'),U('NurseNotes/nurseNotesQuestionsList'));
               else
                   $this->error(C('ERR_STRING'));
           }
           else
           {
               $this->error(C('ERR_STRING'));
           }

       }
       //护士笔记版问题分类
       $typeInfo = M('NurseQuestionType')->field('type_name,id')->order('id desc')->where('status=1')->select();
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
     * 护士笔记版问题列表
     * @author wepean
     * @return array
     */
    public function NurseNotesQuestionsList()
    {
        import('Org.Util.Page');   //导入分页类
        $listRows  = I('get.pagesize','30','intval');
        $page      = I('get.p') ? I('get.p'):0;
        $param     = '&pagesize='.$listRows;
        $oid       = I('get.oid');
        $where     = array();
        if ( $oid == 2 ) $where['a.contents'] = array('exp','IS NULL');             //无答案
        if ( $oid == 3 ) $where['a.contents'] = array('exp','IS NOT NULL'); //有答案
        if ( $oid == 4 ) $where['l.article_status']    = array('eq','-1');
        if ( $oid == 5 ) $where['l.article_status']    = array('eq','1');
        if ( $oid == 6 ) $where['l.is_robot_learning'] = array('eq','1');
        if ( $oid == 7 ) $where['l.article_type_id']   = array('eq','0');
        if ( $oid ) $this->assign('oid',$oid);
        //搜索
        if ( IS_POST )
        {
            $qid    = I('post.questionsId','','intval');
            $kyword = I('post.keywords');
            if ($qid) $where['l.id'] = $qid;
            if ($kyword) $where['l.question_title'] = array('like',array('%'.$kyword.'%',''.$kyword.'%'),'OR');
            $this->assign('qid',$qid);
            $this->assign('kyword',$kyword);
        }

        $admin = M('NurseQuestionList');
        //总数
        $count = $admin->alias('l')
                       ->field('l.id')
                       ->where($where)
                       ->join('__NURSE_QUESTION_ANSWER__ a ON a.question_id=l.id')
                       ->count();

        if(($page-1)*$listRows >= $count && ($page>1 OR !is_numeric($page))) $this->error('该分页暂无记录!');
        $Page = new \Page($count,$listRows,$param);
        $show = $Page->PageShow();
        $admin_list = $admin->alias('l')
                            ->field('l.id,l.question_title,l.add_time,l.article_status,a.options,l.article_type_id,a.contents')
                            ->join('__NURSE_QUESTION_ANSWER__ a ON a.question_id=l.id')
                            ->where($where)
                            ->limit($listRows)
                            ->page($page)
                            ->order('l.id desc')
                            //->fetchSql(true)
                            ->select();
        /* $sql = $admin->getLastSQL();
       vendor('Sphinx.api.sphinxapi');
       $cl = new \SphinxClient();
       $cl->SetServer('127.0.0.1', 9312); //注意这里的主机
       $index = 'test1';
       $cl->setMatchMode(SPH_MATCH_BOOLEAN);
       $re    = $cl->Query('*我是中华人民共和国公民*');
       dump($re);*/


        foreach ($admin_list as $k => $v)
        {
            $typeInfo = M('NurseQuestionType')->field('type_name')->where('id='.$v['article_type_id'])->find();
            if ( $typeInfo )
                $admin_list[$k]['article_type_id'] = $typeInfo['type_name'] ;
            else
                $admin_list[$k]['article_type_id'] = '--';

            $admin_list[$k]['contents'] = json_decode($v['contents'],true);
            //$admin_list[$k]['options']  = json_decode($v['options'],true);
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
    public function NurseNotestypeList()
    {
        import('Org.Util.Page');// 导入分页类
        $listRows  = I('get.pagesize','30','intval');
        $page      = I('get.p') ? I('get.p'):0;
        $param     = '&pagesize='.$listRows;
        $admin     = M('NurseQuestionType');
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
    public function NurseNotesTypeDel()
    {
        if (IS_AJAX)
        {
            $id = I('id');
            if ( ! $id) $this->ajaxReturn(array('status'=>'-1','error'=>C('CSCW_STRING')));
            $admin      = M("NurseQuestionType");
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
             redirect(U('NurseNotes/nurseNotesTypeList'),3,C('FFCZ_STRING'));
        }
    }
    /**
     * 删除护士笔记版问题
     * @author wepean
     * @return json
     */
    public function NurseNotesQuestionsDel()
    {
        if (IS_AJAX)
        {
            $id = I('post.id');
            if ( ! $id) $this->ajaxReturn(array('status'=>'-1','error'=>C('CSCW_STRING')));
            $ids = '';
            //批量删除的护士笔记版问题ID
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

            $admin      = M("NurseQuestionList");
            $admin_info = $admin->where($where)->getField('id',true);
            if ( ! $admin_info) $this->ajaxReturn(array('status'=>'-1','error'=>'护士笔记版问题不存在！'));
            //$admin->startTrans();
            $admin_del  = $admin->where($where)->delete();

            if ( $admin_del )
            {
                M('NurseQuestionAnswer')->where($awhere)->delete();  //删除护士笔记版问题答案
                $this->ajaxReturn(array('status'=>'1','error'=>C('DSUB_STRING')));
            }
            else
                $this->ajaxReturn(array('status'=>'-1','error'=>C('DERR_STRING')));
        }
        else
        {
            redirect(U('NurseNotes/nurseNotesQuestionsList'),3,C('FFCZ_STRING'));
        }

    }
    /**
     * 启用|禁用 分类
     * @author wepean
     * @return json
     */
    public function NurseNotesTypeStatus()
    {
        $id = I('id');
        if ( ! $id) $this->ajaxReturn(array('status'=>'-1','error'=>C('CSCW_STRING')));
        $admin      = M("NurseQuestionType");
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
    public function NurseNotesQuestionsStatus()
    {
        $id = I('id');
        if ( ! $id) $this->ajaxReturn(array('status'=>'-1','error'=>C('CSCW_STRING')));
        $admin      = M("NurseQuestionList");
        $questions  = M("NurseQuestionAnswer");
        $admin_info = $admin->where('id='.$id)->find();
        if ( ! $admin_info) $this->ajaxReturn(array('status'=>'-1','error'=>'护士笔记版问题不存在！'));
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
    public function NurseNotesTypeEdit()
    {
        $id = I('id');
        if ( ! $id) $this->error(C('CSCW_STRING'),'',3);
        $admin = M('NurseQuestionType');
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
     * 修改护士笔记版问题
     * @author wepean
     * @return string
     */
    public function NurseNotesQuestionsEdit()
    {
        $id = I('id');
        if ( ! $id) $this->error(C('CSCW_STRING'),'',3);
        $admin     = M('NurseQuestionList');
        $questions = M("NurseQuestionAnswer");
        //判断是否POST提交
        if (IS_AJAX)
        {
            $id  = I('post.id');
            $admin_info = $admin->where('id='.$id)->find();
            if ( ! $admin_info ) $this->ajaxReturn(array('status'=>'-1','error'=>'护士笔记版问题不存在！'));
            $data['description']     = I('post.description');
            $data['is_new']          = 1;
            $data['question_title']  = I('post.typeName');
            $data['article_status']  = I('post.status');
            $data['article_type_id'] = I('post.type_id');

            $updataID  = $admin->where('id = '.$id)->save($data);
            $answer_up = '';
            $qresult   = $questions->where('question_id='.$id)->find();
            //是否有护士笔记版问题
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
            $typeInfo = M('NurseQuestionType')->field('id,type_name')->where('status=1')->order('id desc')->select();
            $this->assign('adminInfo',$adminfo);
            $this->assign('typeInfo',$typeInfo);
            $this->display();
        }
    }





}
