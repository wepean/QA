<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends CommonController
{
    protected $elasticsearch;
    /*
     * 数据初始化
     */
    public function __initialize()
    {
        $this->elasticsearch = new \Think\ElasticsearchService();
    }

    /*
     * 首页
     */
    public function index()
    {

       redirect(U('Questions/questionsList'));
    }


    public function createElast()
    {
        echo $this->elasticsearch->createIndex();
    }

    public function  createmapping()
    {
        $this->elasticsearch = new \Think\ElasticsearchService();
        $field = array();
        $field ['mongo_id'] = array('type' => "string", 'index' => 'test', 'null_value' => '');
        $field ['article_type'] = array('type' => "string", 'index' => 'test', 'null_value' => '');
        echo $this->elasticsearch->createMapping($field);
    }

    public function   sadd()
    {

        $data['mongo_id'] = 'asas';
        $data['article_type'] = "sdsd";
        $data['external_time'] ='20141212'; //采编时间
        $data['create_time'] = '20141212'; //创建时间
        echo $this->elasticsearch->add("ss", $data);

    }
    public  function elasticsrarch()
    {
        $queryData ['query'] ['match'] ['subject'] = "张三 ";
        $result= $this->elasticsearch->search($queryData);
        var_dump($result);
    }

    public function   sdelete()
    {
        echo    $this->elasticsearch->delete(ss);
    }

    public  function update()
    {
        $setData = array ();
        $setData['status'] = '0';
        $setData['content'] = 'hhhhh';
        $this->elasticsearch->update($id, $setData);
    }
}