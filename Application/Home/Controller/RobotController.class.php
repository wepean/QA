<?php
namespace Home\Controller;
use Think\Controller;
class RobotController extends CommonController
{
    /**
     * 初始化
     */
    public function _initialize()
    {
        set_time_limit(0);
    }
    /**
     * 问题列表
     * @author wepean
     * @return array
     */
    public function tranCentre()
    {
        import('Org.Util.Page');// 导入分页类
        $listRows  = I('get.pagesize','20','intval');
        $page      = I('get.p') ? I('get.p'):0;
        $param     = '&pagesize='.$listRows;
        $where     = array();
        //搜索
        if (IS_POST)
        {
            $qid    = I('post.questionsId','','intval');
            $kyword = I('post.keywords');
            if ($qid) $where['id'] = $qid;
            if ($kyword) $where['question_title'] = array('like',array('%'.$kyword.'%',''.$kyword.'%'),'OR');
            $this->assign('qid',$qid);
            $this->assign('kyword',$kyword);
        }

        $admin     = M('RobotAnswer');
        $count     = $admin->where($where)->count();
        if(($page-1)*$listRows >= $count && ($page>1 OR !is_numeric($page))) $this->error('该分页暂无记录!');
        $Page = new \Page($count,$listRows,$param);
        $show = $Page->PageShow();
        $admin_list = $admin->where($where)
                            ->limit($listRows)
                            ->page($page)
                            ->order('id desc')
                            ->select();
        $this->assign('pagesize',$listRows);
        $this->assign('page',$show);
        $this->assign('admin_list',$admin_list);
        $this->display();
    }

    /**
     * 修改问题
     * @author wepean
     * @return string
     */
    public function robotQuestionsEdit()
    {
        $id  = I('id');
        $rid = I('rid');
        if ( ! $id OR ! $rid) $this->error(C('CSCW_STRING'),'',3);
        $questions = M("QuestionAnswer");
        //判断是否AJAX提交
        if (IS_AJAX)
        {
            $contents = I('post.contents');
            if ($contents)
            {
                $data['contents']    =  $contents;
                $data['is_new']      =  1;
                $data['version_id']  += 1;
                //开启事务
                $questions->startTrans();
                $updataID = $questions->where('question_id = '.$id)->save($data);
                if ($updataID)
                {
                    //$datas     = array('id'=>'59ababfc-7d47-4abc-a617-196ac7eff0dc','a'=>$contents);
                    $datas     = array('id'=>$rid,'a'=>$contents);
                    $backRobot = curl_https_post('http://elk.dooland.com/v1/search',json_encode($datas));
                    //$backRobot = json_decode($robotUp,true);
                    //var_dump($backRobot);
                    if ($backRobot)
                    {
                        if ($backRobot['status'] == 1)
                        {
                            $questions->commit();
                            $this->ajaxReturn(array('error'=>C('SUB_STRING'),'status'=>1));
                        }
                        else
                        {
                            $questions->rollback();
                            $this->ajaxReturn(array('error'=>C('ERR_STRING'),'status'=>-1));
                        }
                    }
                    else
                    {
                        $questions->rollback();
                        $this->ajaxReturn(array('error'=>'与对话机器人对话失败！','status'=>-1));
                    }
                }
            }
            else
            {
                $this->ajaxReturn(array('error'=>'内容不能为空！','status'=>-1));
            }
        }
        else
        {
            $answere  = $questions->field('id,question_title,question_id,contents')->where('question_id='.$id)->find();
            $this->assign('adminInfo',$answere);
            $this->assign('id',$id);
            $this->assign('rid',$rid);
            $this->display();
        }
    }


    /**
     * 删除对话机器人问题
     * @author wepean
     * @return json
     */
    public function robotQuestionsDel()
    {
        if (IS_AJAX)
        {
            $id  = I('id');
            $rid = I('post.rid');
            if ( ! $id) $this->ajaxReturn(array('status'=>'-1','error'=>C('CSCW_STRING')));
            $d   = I('post.d');
            if ($d == 1)
            {
                $roids = array_filter(explode(',',$id));
                $where['id'] = $roids ? array('in',$roids) : '';
            }
            else
            {
                $where['id'] = $id;
            }

            $questions  = M("robotAnswer");
            $admin_info = $questions->where($where)->field('id,robot_id')->select();
            if ( ! $admin_info) $this->ajaxReturn(array('status'=>'-1','error'=>'问题不存在！'));
            $raid = array();
            foreach ($admin_info as $ke => $va)
            {
                $raid[] = $va['robot_id'];
            }

            $questions->startTrans();
            $admin_del  = $questions->where($where)->delete();

            if ($admin_del)
            {

                foreach ($raid as $k=>$v)
                {

                }

                $robotUp   = curl_https_delete(C('URLS') . '?id=' .$rid);
                $backRobot = json_decode($robotUp,true);

                if ($backRobot)
                {
                    if ($backRobot['status'] == 1)
                    {
                        $questions->commit();
                        $this->ajaxReturn(array('error'=>C('SUB_STRING'),'status'=>1));
                    }
                    else
                    {
                        $questions->rollback();
                        $this->ajaxReturn(array('error'=>C('ERR_STRING'),'status'=>-1));
                    }
                }
                else
                {
                    $questions->rollback();
                    $this->ajaxReturn(array('error'=>'与对话机器人对话失败！','status'=>-1));
                }
            }
            else
            {
                $questions->rollback();
                $this->ajaxReturn(array('error'=>C('ERR_STRING'),'status'=>-1));
            }
        }
        else
        {
            redirect(U('Questions/questionsList'),3,C('FFCZ_STRING'));
        }

    }

    /**
     *  杂志与图书机器人天启/关闭设置
     * @author wepean
     * @return json
     */
    public function robotSet()
    {
        $robot = M("robotSet");
        //开关设置
        if ( IS_AJAX )
        {
            $sets = I('post.sets');
            if ( ! $sets) $this->error(C('CSCW_STRING'),'',3);
            $updataID  = $robot->where('id = 1')->save(array('status'=>$sets));

            if ( $updataID )
            {
                $error  = C('SUB_STRING');
                $status = 1;
            }
            else
            {
                $error  = C('ERR_STRING');
                $status = "-1";
            }

            $this->ajaxReturn(array('error'=>$error,'status'=>$status));
        }
        else
        {
            $where['id'] = array('in','1,2');
            $rres  = $robot->field('status')->where($where)->select();
            $this->assign('statu',$rres[0]['status']);
            $this->assign('statui',$rres[1]['status']);
            $this->display();
        }
    }
    /**
     *  文章版机器人天启/关闭设置
     * @author wepean
     * @return json
     */
    public function robotSeti()
    {
        $robot = M("robotSet");
        //开关设置
        if ( IS_AJAX )
        {
            $sets = I('post.sets');
            if ( ! $sets) $this->error(C('CSCW_STRING'),'',3);
            $updataID  = $robot->where('id = 2')->save(array('status'=>$sets));

            if ( $updataID )
            {
                $error  = C('SUB_STRING');
                $status = 1;
            }
            else
            {
                $error  = C('ERR_STRING');
                $status = "-1";
            }

            $this->ajaxReturn(array('error'=>$error,'status'=>$status));
        }
    }

    /**
     *  学习日志
     * @author wepean
     * @return array
     */
    public function studyLog()
    {
        import('Org.Util.Page');   //导入分页类
        $listRows  = I('get.pagesize','30','intval');
        $page      = I('get.p') ? I('get.p'):0;
        $param     = '&pagesize='.$listRows;
        $lid       = I('get.lid') ? I('get.lid') : 0;

        echo $lid;
        if ( $lid == 1)
            $robotLog = M("RobotStudyArticleLog");  //文章版
        else
            $robotLog = M("RobotStudyLog");         //杂志版

        $where     = array();
        $oid       = I('get.oid');
        if ( $oid == 2 ) $where['contents_status'] = 1;
        if ( $oid == 3 ) $where['contents_status'] = array('neq','1');
        //搜索
        if (IS_POST)
        {
            $qid       = I('post.questionsId');
            $startTime = I('post.start_time');
            $endTime   = I('post.end_time');
            if ( $qid ) $where['question_id'] = $qid;
            if ( $startTime > $endTime ) $this->error('开始时间不能大于结束时间!');
            if ( $startTime OR $endTime ) $where['add_time'] = array('between',array(strtotime($startTime),strtotime($endTime)));
            $this->assign('qid',$qid);
            $this->assign('startTime',$startTime);
            $this->assign('endTime',$endTime);
        }

        $count = $robotLog->where($where)->count();
        if(($page-1)*$listRows >= $count && ($page>1 OR !is_numeric($page))) $this->error('该分页暂无记录!');
        $Page = new \Page($count,$listRows,$param);
        $show = $Page->PageShow();
        $rres = $robotLog->where($where)
                         ->limit($listRows)
                         ->page($page)
                         ->order('id desc')
                         ->select();
        $this->assign('oid',$oid);
        $this->assign('rres',$rres);
        $this->assign('lid',$lid);
        $this->assign('pagesize',$listRows);
        $this->assign('page',$show);
        $this->display();
    }
}
