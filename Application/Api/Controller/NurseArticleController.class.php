<?php
namespace Api\Controller;
use Think\Controller;
class NurseArticleController extends Controller
{
	/**
	 * 初始化
	 */
	public function _initialize()
	{
		//跨域设置
		header('Content-Type:application/json,charset=UTF-8');
		header('Access-Control-Allow-Origin:*');
		set_time_limit(0);
	}

	/**
	 * 获取笔记文章详情
	 * @access public
	 * @author wepean<2050301456@qq.com>
	 * return  array
	 */
	public function getNurseArticleInfo()
	{
		$msgss  = file_get_contents("php://input");
		$msgs   = json_decode($msgss,true);
		$osType = $msgs['osType'];
		$udid   = $msgs['udid'];
		$id     = $msgs['id'];
		//提交数据验证
		if ( ! $osType  OR ! $id OR ! $udid OR ! is_int($id)) $this->ajaxReturn(array('error'=>C('CSCW_STRING'),'status'=>'-1'));

		$infos =  M('NurseArticle')->field('id,title,author,description,content,publish_time')->where('id='.$id)->find();

		if ( $infos )
			$this->ajaxReturn(array('msg'=>C('SJHQCG_STRING'),'data'=>$infos,'status'=>'1'));
		else
			$this->ajaxReturn(array('error'=>C('MYSJ_STRING'),'status'=>'-1'));
	}
}
?>