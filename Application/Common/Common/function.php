<?php
/**
 * 获取用户ID
 * @author	wepean
 * @return	int
 */
function get_admin_id()
{
    if (session('id')) return session('id');
}

/**
 * 获取用户uname
 * @author	wepean
 * @return	string
 */
function get_admin_name()
{
    if (session('user')) return session('user');
}

/**
 * 获取用户真实姓名
 * @author	wepean
 * @return	string
 */
function get_admin_tname()
{
    if (session('tuser')) return session('tuser');
}

/**
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
 * @return string
 */
function rand_string($len=6,$type='',$addChars='')
{
    $str ='';
    switch($type) {
        case 0:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        case 1:
            $chars= str_repeat('0123456789',3);
            break;
        case 2:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ'.$addChars;
            break;
        case 3:
            $chars='abcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        case 4:
            $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借".$addChars;
            break;
        default :
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars='ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'.$addChars;
            break;
    }
    if($len>10 ) {//位数过长重复字符串一定次数
        $chars= $type==1? str_repeat($chars,$len) : str_repeat($chars,5);
    }
    if($type!=4) {
        $chars   =   str_shuffle($chars);
        $str     =   substr($chars,0,$len);
    }else{
        // 中文随机字
        for($i=0;$i<$len;$i++){
            $str.= msubstr($chars, floor(mt_rand(0,mb_strlen($chars,'utf-8')-1)),1);
        }
    }
    return $str;
}
/**
 * 创建TOKEN
 * @author	wepean
 * @return	session
 */
function creatToken()
{
    $code = chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE));
    session('TOKEN', authcode($code));
}

/**
 * 判断TOKEN
 * @author	wepean
 * @return	session
 */
function checkToken($token)
{
    if ($token == session('TOKEN'))
    {
        session('TOKEN', NULL);
        return TRUE;
    }
    else
    {
        return FALSE;
    }
}

/**
 * 加密TOKEN
 * @author	wepean
 * @return	string
 */
function authcode($str)
{
    $key = "ANDIAMON";
    $str = substr(md5($str), 8, 10);
    return md5($key . $str);
}


//CURL 获此网页内容
function curl_get_contents($url,$data = array(), $https = FALSE)
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $curl 		  = curl_init(); 								// 启动一个CURL会话

    if( !empty($data) && is_array($data) )
    {
        curl_setopt($curl, CURLOPT_POST, 1); 						// 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); 				// Post提交的数据包
    }
    if( $https )
    {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 				// 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); 				// 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); 				// 使用自动跳转
    }

    curl_setopt($curl, CURLOPT_URL, trim($url)); 						// 要访问的地址
    curl_setopt($curl, CURLOPT_TIMEOUT, 60); 					// 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0); 						// 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 				// 获取的信息以文件流的形式返回
    curl_setopt($curl, CURLOPT_USERAGENT,$user_agent); 			// 模拟用户使用的浏览器
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); 				// 自动设置Referer
    $data 	= curl_exec($curl);									// 执行操作
 /*   if (curl_errno($curl))
    {
        $error = curl_errno($curl);
        throw new Exception("curl出错，错误码:$error");
        return array();
    }*/
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE); // 获取返回的状态码
    curl_close($curl); 					// 关闭CURL会话
    return json_decode($data,true); 	// 返回数据
}

//CURL 获此网页内容I
function curl_request($url,$data,$method = 'PUT')
{
    $headers = array('Accept: application/json', 'Content-Type: application/json');
    // 启动一个CURL会话
    $handle  = curl_init();
    curl_setopt($handle, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($handle,CURLOPT_HEADER,0); // 是否显示返回的Header区域内容
    curl_setopt($handle, CURLOPT_HTTPHEADER, $headers); //设置请求头
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true); // 获取的信息以文件流的形式返回
    curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查

    switch($method)
    {
        case 'POST':
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($handle, CURLOPT_POSTFIELDS, $data); //设置请求体，提交数据包
            break;
        case 'PUT':
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($handle, CURLOPT_POSTFIELDS, $data); //设置请求体，提交数据包
            break;
        case 'DELETE':
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
    }

    $response = curl_exec($handle); // 执行操作
    $code     = curl_getinfo($handle, CURLINFO_HTTP_CODE); // 获取返回的状态码

    curl_close ($handle); // 关闭CURL会话
    return $response;
}

// CURL https put 方式提交数据
function curl_https_put($url, $data)
{
    return curl_request($url,$data);
}

// CURL https delete 方式提交数据
function curl_https_delete($url)
{
    return curl_request($url,'','DELETE');
}

// CURL https post 方式提交数据
function curl_https_post($url, $data)
{
     // return curl_request($url,$data,'POST');
     return curl_get_contents($url,$data,true);
}

// CURL https get 方式获取网页
function curl_https_get($url)
{
    return curl_get_contents($url,array(),true);
}


/**
 * 判断是数组是几维
 * @author	wepean
 * @param  array $arr
 * @return	 int
 */
function get_max_array($arr)
{
    if ( ! $arr OR ! is_array($arr) )  return false;
    $max1 = 0;
    foreach($arr as $item1)
    {
        $t1 = get_max_array($item1);
        if( $t1 > $max1) $max1 = $t1;
    }
    return $max1 + 1;
}

/**
 * 提取字符串中数字
 * @param  string $str
 * @author wepean<2050301456@qq.com>
 * @return string
 */
function find_num($str = '')
{
    $str = trim($str);
    if ( ! $str ) return '';
    $temp   = array('1','2','3','4','5','6','7','8','9','0');
    $result = '';
    for ( $i=0; $i<strlen($str); $i++ )
    {
        if ( in_array($str[$i],$temp) )
            $result.= $str[$i];
    }
    return $result;
}

/**
 * 判断某个字符串是否在数组中
 * @param  string $p_needle
 * @param  array  $p_haystack
 * @author wepean<2050301456@qq.com>
 * @return true/false
 */
function array_multi_search( $p_needle, $p_haystack )
{
    if(! is_array($p_haystack)) return false;
    if( in_array( $p_needle, $p_haystack ) )  return true;
    foreach( $p_haystack as $row )
    {
        if( array_multi_search( $p_needle, $row ) ) return true;
    }
    return false;
}

/**
 * 安全过滤
 * @param  string $str
 * @author wepean<2050301456@qq.com>
 * return  string
 */
function str_filter($str)
{
    if ( ! $str ) return;
    $str = trim($str);
    $str = str_replace('`', '', $str);
    $str = str_replace('·', '', $str);
    $str = str_replace('~', '', $str);
    $str = str_replace('!', '', $str);
    $str = str_replace('！', '', $str);
    $str = str_replace('@', '', $str);
    $str = str_replace('#', '', $str);
    $str = str_replace('$', '', $str);
    $str = str_replace('￥', '', $str);
    $str = str_replace('^', '', $str);
    $str = str_replace('……', '', $str);
    $str = str_replace('&', '', $str);
    $str = str_replace('*', '', $str);
    $str = str_replace('(', '', $str);
    $str = str_replace(')', '', $str);
    $str = str_replace('（', '', $str);
    $str = str_replace('）', '', $str);
    $str = str_replace('-', '', $str);
    $str = str_replace('——', '', $str);
    $str = str_replace('+', '', $str);
    $str = str_replace('=', '', $str);
    $str = str_replace('|', '', $str);
    $str = str_replace('\\', '', $str);
    $str = str_replace('[', '', $str);
    $str = str_replace(']', '', $str);
    $str = str_replace('【', '', $str);
    $str = str_replace('】', '', $str);
    $str = str_replace('{', '', $str);
    $str = str_replace('}', '', $str);
    $str = str_replace(';', '', $str);
    $str = str_replace('；', '', $str);
    $str = str_replace(':', '', $str);
    $str = str_replace('：', '', $str);
    $str = str_replace('\'', '', $str);
    $str = str_replace('"', '', $str);
    $str = str_replace('“', '', $str);
    $str = str_replace('”', '', $str);
    $str = str_replace(',', '', $str);
    $str = str_replace('，', '', $str);
    $str = str_replace('<', '', $str);
    $str = str_replace('>', '', $str);
    $str = str_replace('《', '', $str);
    $str = str_replace('》', '', $str);
    $str = str_replace('.', '', $str);
    $str = str_replace('。', '', $str);
    $str = str_replace('/', '', $str);
    $str = str_replace('、', '', $str);
    $str = str_replace('?', '', $str);
    $str = str_replace('？', '', $str);
    //防止SQL注入,过滤敏感字符
    $str = str_replace("_","\_",$str);
    $str = str_replace("%","\%",$str);
    $str = str_replace("'","''",$str);
    $str = str_replace("select","sel&#101;ct",$str);
    $str = str_replace("join","jo&#105;n",$str);
    $str = str_replace("union","un&#105;on",$str);
    $str = str_replace("where","wh&#101;re",$str);
    $str = str_replace("insert","ins&#101;rt",$str);
    $str = str_replace("delete","del&#101;te",$str);
    $str = str_replace("update","up&#100;ate",$str);
    $str = str_replace("like","lik&#101;",$str);
    $str = str_replace("drop","dro&#112;",$str);
    $str = str_replace("create","cr&#101;ate",$str);
    $str = str_replace("modify","mod&#105;fy",$str);
    $str = str_replace("rename","ren&#097;me",$str);
    $str = str_replace("alter","alt&#101;r",$str);
    $str = str_replace("cast","ca&#115;",$str);
    //过滤javascript,css,iframes,object等不安全参数
    $str = preg_replace("/(javascript:)?on(click|load|key|mouse|error|abort|move|unload|change|dblclick|move|reset|resize|submit)/i","&111n\\2",$str);
    $str = preg_replace("/(.*?)<\/script>/si","",$str);
    $str = preg_replace("/(.*?)<\/iframe>/si","",$str);
    $str = preg_replace ("//iesU", '', $str);
    return $str;
}
/**
 * 过滤
 * @param  string $str
 * @author wepean<2050301456@qq.com>
 * return  string
 */
function str_filteri($str)
{
    if ( ! $str ) return;
    $str = trim($str);
    $str = str_replace('`', '', $str);
    $str = str_replace('·', '', $str);
    $str = str_replace('~', '', $str);
    $str = str_replace('!', '', $str);
    $str = str_replace('！', '', $str);
    $str = str_replace('@', '', $str);
    $str = str_replace('#', '', $str);
    $str = str_replace('$', '', $str);
    $str = str_replace('￥', '', $str);
    $str = str_replace('^', '', $str);
    $str = str_replace('……', '', $str);
    $str = str_replace('&', '', $str);
    $str = str_replace('*', '', $str);
    $str = str_replace('(', '', $str);
    $str = str_replace(')', '', $str);
    $str = str_replace('（', '', $str);
    $str = str_replace('）', '', $str);
    $str = str_replace('-', '', $str);
    $str = str_replace('——', '', $str);
    $str = str_replace('+', '', $str);
    $str = str_replace('=', '', $str);
    $str = str_replace('|', '', $str);
    $str = str_replace('\\', '', $str);
    $str = str_replace('[', '', $str);
    $str = str_replace(']', '', $str);
    $str = str_replace('【', '', $str);
    $str = str_replace('】', '', $str);
    $str = str_replace('{', '', $str);
    $str = str_replace('}', '', $str);
    $str = str_replace(';', '', $str);
    $str = str_replace('；', '', $str);
    $str = str_replace(':', '', $str);
    $str = str_replace('：', '', $str);
    $str = str_replace('\'', '', $str);
    $str = str_replace('"', '', $str);
    $str = str_replace('“', '', $str);
    $str = str_replace('”', '', $str);
    $str = str_replace(',', '', $str);
    $str = str_replace('，', '', $str);
    $str = str_replace('<', '', $str);
    $str = str_replace('>', '', $str);
    $str = str_replace('《', '', $str);
    $str = str_replace('》', '', $str);
    $str = str_replace('.', '', $str);
    $str = str_replace('。', '', $str);
    $str = str_replace('/', '', $str);
    $str = str_replace('、', '', $str);
    $str = str_replace('?', '', $str);
    $str = str_replace('？', '', $str);
    //防止SQL注入,过滤敏感字符
    $str = str_replace("_","\_",$str);
    $str = str_replace("%","\%",$str);
    $str = str_replace("'","''",$str);
    return $str;
}
/**
 * 对象转数组
 * @param  object $object
 * @author wepean<2050301456@qq.com>
 * return  array
 */
function object_to_array($object)
{
    if ( is_object($object))
    {
        foreach ($object as $key => $value)
        {
            $array[$key] = $value;
        }
    }
    else
    {
        $array = $object;
    }
    return $array;
}

