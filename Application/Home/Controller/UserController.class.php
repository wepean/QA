<?php
namespace Home\Controller;
use Think\Controller;
class UserController extends CommonController
{
    public function userAdd()
    {
    	
    	$this->display();
    }
    /**
     * 添加新用户
     * @author wepean
     * @return string
     */
    public function userSaveAdd()
    {
       if (IS_POST)
       {
           $uname = I('post.uname');
           $tname = I('post.tname');
           $tinfo = M('admin')->field('id')->where('uname = "'.$uname.'"')->find();
           if($tinfo) $this->error('账号已经存在!','',3);
           $pwd   = I('post.oldpwd');
           $email = I('post.email');
           $salt  = rand_string(4);
           $pwds  = md5($salt.$pwd);
           if ( ! $uname OR ! $pwd) $this->error('用户名/密码不能为空!','',3);
           if ( ! $tname) $this->error('真实姓名不能为空!','',3);
           $data['salt']    = $salt;
           $data['pwd']     = $pwds;
           $data['uname']   = $uname;
           $data['tname']   = $tname;
           $data['email']   = $email;
           $data['regtime'] = time();
           $dataid          = M('admin')->add($data);
           if ($dataid)
               $this->success('操作成功','/index.php?s=/Home/User/userList',3);
           else
               $this->error('操作失败','',3);
       }
       else
           $this->error('非法操作','/index.php?s=/Home/',3);

    }

    /**
     * 重置密码
     * @author wepean
     * @return string
     */
    public function resetPassword()
    {
    	$id   = get_admin_id();
       $name = get_admin_name();
    	$this->assign('admininfo',$name);
       $this->assign('adminId',$id);
        if (IS_POST)
        {
            $info   = I('post.');
            $id     = $info['id'];
            $pwd    = M('admin')->field('salt,pwd')->where('id='.$id)->find();
            $oldpwd = md5($pwd['salt'].trim($info['oldpwd']));
            if ($oldpwd != $pwd['pwd']) $this->error('当前密码错误','',3);
            if ($info['password'] != $info['pwd']) $this->error('两次密码输入不一致','',3);
            $salt   = rand_string(4);
            $uppwd  = md5($salt.$info['pwd']);
            $data['salt'] = $salt;
            $data['pwd']  = $uppwd;
            $updataID     = M('admin')->data($data)->where('id = '.$id)->save();
            if ($updataID)
            {
                session(null);
                $this->success('密码修改操作成功','/index.php?s=/home/login/');
            }
            else
            {
                $this->error('操作失败','',3);
            }

        }
    	$this->display();
    }
    /**
     * 修改个人资料
     * @author wepean
     * @return string
     */
    public function userEdit()
    {
       $admin   = M('admin');
       $id      = get_admin_id();
       $adminfo = $admin->find($id);
       $this->assign('adminInfo',$adminfo);
        //判断是否POST提交
       if(IS_POST)
       {
           $id            = I('post.id');
           $data['tname'] = I('post.tname');
           $data['email'] = I('post.email');
           $updataID      = $admin->data($data)->where('id = '.$id)->save();
           if ($updataID)
               $this->success('修改成功，下一次登录生效！','',3);
           else
               $this->error('操作失败','',3);
       }
       else
           $this->display();

    }
    /**
     * 添加的用户列表
     * @author wepean
     * @return string
     */
    public function userList()
    {
        import('Org.Util.Page');// 导入分页类
        //import("Org.Page");
        $listRows  = I('get.pagesize','20','intval');
        $page      = I('get.p') ? I('get.p'):0;
        $param     = '&pagesize='.$listRows;
        $admin     = M("admin");
        $count     = $admin->count();
        if(($page-1)*$listRows >= $count && ($page>1 OR !is_numeric($page))) $this->error('该分页暂无记录!');
        $Page = new \Page($count,$listRows,$param);
        $show = $Page->PageShow();
        $admin_list = $admin->field('id,uname,tname,email,logintime,status')->limit($listRows)->page($page)->select();
        $this->assign('pagesize',$listRows);
        $this->assign('page',$show);
        $this->assign('admin_list',$admin_list);
        $this->display();
    }

    /**
     * 删除用户
     * @author wepean
     * @return json
     */
    public function userDel()
    {
        $id = I('id');
        if ( ! $id) $this->ajaxReturn(array('status'=>'-1','error'=>'参数错误！'));
        $admin      = M("admin");
        $admin_info = $admin->where('id='.$id)->find();
        if ( ! $admin_info) $this->ajaxReturn(array('status'=>'-1','error'=>'用户不存在！'));
        $admin_del  = $admin->where('id='.$id)->delete();
        if ($admin_del)
            $this->ajaxReturn(array('status'=>'1','error'=>'删除成功！'));
        else
            $this->ajaxReturn(array('status'=>'-1','error'=>'删除失败！'));
    }
    /**
     * 启用|禁用
     * @author wepean
     * @return json
     */
    public function userStatus()
    {
        $id = I('id');
        if ( ! $id) $this->ajaxReturn(array('status'=>'-1','error'=>'参数错误！'));
        $admin      = M("admin");
        $admin_info = $admin->where('id='.$id)->find();
        if ( ! $admin_info) $this->ajaxReturn(array('status'=>'-1','error'=>'用户不存在！'));
        $status = $admin_info['status'];
        if ($status == 1)
        {
            $admin_up  = $admin->where('id='.$id)->save(array('status'=>'-1'));
            if ($admin_up)
                $msg = array('status'=>'1','error'=>'操作成功！');
            else
                $msg = array('status'=>'-1','error'=>'操作失败！');
        }
        else
        {
            $admin_up  = $admin->where('id='.$id)->save(array('status'=>'1'));
            if ($admin_up)
                $msg = array('status'=>'1','error'=>'操作成功！');
            else
                $msg = array('status'=>'-1','error'=>'操作失败！');
        }

        $this->ajaxReturn($msg);
    }
}
?>