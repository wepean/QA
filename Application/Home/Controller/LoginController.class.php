<?php
namespace Home\Controller;
use Think\Controller;
use Think\Verify;
class LoginController extends Controller {

	//登录首页
    public function index()
    {
        $login = get_admin_name();
        if ($login) redirect(__ROOT__."/index.php?s=/home/");
        $this->display();
    }

    //登录提交数据校验
    public function checkLogin()
    {
        if (IS_POST)
        {
            $username = I('post.username');
            $pwd      = I('post.password');
            $code     = I('post.verification');
            //相关验证
            if( ! $username OR ! $pwd) $this->error('用户名或密码不能为空', '',3);
            if( ! $code) $this->error('验证码不能为空', '',3);
            $pwd_len   = strlen($pwd);
            if( $pwd_len<6 OR $pwd_len>20) $this->error('密码长度不能超过20或短于6个字符', '',3);
            $user      = D('admin');
            $user_info = $user->field('id,salt,pwd,uname,tname,status')->where('uname = "'.$username.'"')->find();
            if ( ! $user_info) $this->error('用户不存在', '',3);
            if ($user_info['status'] == '-1') $this->error('此用户已被禁止登录，请联系管理员解封···', '',3);
            $pwds = md5($user_info['salt'].$pwd);

            if ($pwds == $user_info['pwd'])
            {
                session('user',$username);
                session('tuser',$user_info['tname']);
                session('id',$user_info['id']);
                $data['logintime'] = time();
                $data['loginip']   = get_client_ip();
                if ($user->where('uname = "'.$username.'"')->save($data))
                redirect(U('Index/index'));
            }
            else
            {
                $this->error('密码错误···');
            }
        }
        else
        {
            redirect(U('Login/index'));
        }

    }

    //退出登录
    public function loginOut()
    {
    	 session(null);
        redirect(U('Login/index'));
    }

    //验证码
    public function verify()
    {
        $config = [
            'fontSize' => 50, // 验证码字体大小
            'length'   => 4,  // 验证码位数
            'useCurve' => false
        ];
        $Verify = new Verify($config);
        $Verify->entry();
    }

    //验证验证码是否正确
    public function checkVerity()
    {
        $code = I('post.verification');
        $Verify = new Verify();
        $res = $Verify->check($code,'');
        $this->ajaxReturn($res, 'json');
    }
}
?>