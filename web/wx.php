<?php
require_once dirname(__FILE__) . '/db/mongodb/db_handler.php';

/* 
     * 微信公众号服务接口  
    */
define('TOKEN', 'here is wechats token');

$apiHandler = new wxApiHandler();
$apiHandler->RunApi(true);

class wxApiHandler
{
    protected $media_collec = 'mediascover';
    public function RunApi($is_signed = false)
    {
        if ($is_signed) {
            $this->switchService();
        } else {
            $this->doSign();
        }
    }

    function switchService()
    {
        $xml = $this->loadResXml();
        $msgtype = $xml->MsgType;
        if ($msgtype == "text") {
            $to_user_name = $xml->ToUserName;
            $from_user_name = $xml->FromUserName;
            $content = $xml->Content;

            if($content == '886699'){
                echo sprintf($this->_msg_template["text"], 
                    $from_user_name, 
                    $to_user_name, 
                    time(), 
                    "<a data-miniprogram-appid='wxf6d2e30cdd448764' data-miniprogram-path='pages/886699/todnew/todnew' href='http://dy.ayioz.com'>点我查看今日更新</a>"
                    . "\n------\n<a href='http://jd.3.cn/17QvBW'>优觅小店:京东优惠券</a>");
                // echo sprintf($this->_msg_template["todaynew"], 
                //     $from_user_name, 
                //     $to_user_name, 
                //     time(), 
                //     '今天要看啥？',
                //     '发送指令"886699"可随时查看每天新鲜影视资讯',
                //     'http://mmbiz.qpic.cn/mmbiz_jpg/dKe1WTSiaJo0sAIw7Z8XmCnna4aRykIVdiceu2X3oTUsnWL9LZAia9g3E7lh1Dky8jWp0WRvAZ0UDsbr6glzOs28g/0?wx_fmt=jpeg',
                //     'https://mp.weixin.qq.com/s?__biz=MzU3MTc0NDUyNg==&mid=100000340&idx=1&sn=0f191ce945abb46a7509c332af2a70e7&chksm=7cdaca354bad4323e09d2460687cf3cb529c89755cd25ef8495cc55788ab774bf6a2451f8d34#rd',
                //     '欢迎关注“优觅Lu电影”, 公众号使用指南',
                //     '及时获取国内外最新的即将上映的电影及预告片、电影剪辑，以及电影资讯',
                //     'https://mmbiz.qpic.cn/mmbiz_jpg/dKe1WTSiaJo1vKXzvtsxBtcUEgOk1cef3k8jpqGvEqZka5tdJmLpxhBEgg54xfVTA65KZBn9fKnOKF0urySMoSA/0?wx_fmt=jpeg',
                //     'https://mp.weixin.qq.com/s?__biz=MzU3MTc0NDUyNg==&mid=2247483970&idx=1&sn=b0d628231ea70bab3d5543783197f2ea&chksm=fcdaca23cbad4335f526cb778eb4ed7c9f84bb3813b2686f2203e58b7a5ae14c28f20583469f&token=1329523789&lang=zh_CN#rd'
                // );
            } else {
                $moInfo = $this->queryMoToken($content);
                $motoken_arr = array();
                foreach ($moInfo as $mi => $mi_i) {
                    $t = trim($mi_i->title);
                    $code = strtolower(trim($mi_i->mocode));
                    $year = trim($mi_i->year);
                    if (strlen($t) > 0 && strlen($code) > 0) {
                        array_push($motoken_arr, ($mi + 1) . "-" . $t . "(" . $year . ")\n" . "=> http://m.ayioz.com/p/" . $code);
                    }
                }
                $msg_content = count($motoken_arr) > 0 ? implode("\n", $motoken_arr) . "\n-----------------\n  多个关键字用空格隔开，如“大爆炸 十二”\n<a data-miniprogram-appid='wxf6d2e30cdd448764' data-miniprogram-path='pages/886699/todnew/todnew' href='http://dy.ayioz.com'> 今日更新</a>\n------\n<a href='http://jd.3.cn/17QvBW'>优觅小店:京东优惠券</a>" : "暂未查询到相关影片资讯。\n---------------\n=> 可尝试使用影片别名关键字查询，\n=> 多个关键字用空格隔开，如“大爆炸 十二” \n未收录影片一般将在24小时内收录。\n=> <a data-miniprogram-appid='wxf6d2e30cdd448764' data-miniprogram-path='pages/886699/todnew/todnew' href='http://dy.ayioz.com'>点击这里查看今日更新</a>\n------\n<a href='http://jd.3.cn/17QvBW'>优觅小店:京东优惠券</a>";
                echo sprintf($this->_msg_template["text"], $from_user_name, $to_user_name, time(), $msg_content);
    
                $this->logWxRequest($from_user_name, $msgtype, $content, $xml->CreateTime, count($motoken_arr) > 0);
            }
        } elseif ($msgtype == "event") {
            $to_user_name = $xml->ToUserName;
            $from_user_name = $xml->FromUserName;
            $content = $xml->Event;
            if ($content == 'subscribe') {
                echo sprintf(
                    $this->_msg_template["news"],
                    $from_user_name,
                    $to_user_name,
                    time(),
                    '欢迎关注“优觅Lu电影”, 公众号使用指南',
                    '及时获取国内外最新的即将上映的电影及预告片、电影剪辑，以及电影资讯',
                    'https://mmbiz.qpic.cn/mmbiz_jpg/dKe1WTSiaJo0sAIw7Z8XmCnna4aRykIVdtN3Q3vNPh2EU9lwep2G7bfEUxYa4kuVeXiaep2Dwp8wLORtYUWw7cTQ/640?wx_fmt=jpeg',
                    'https://mp.weixin.qq.com/s?__biz=MzU3MTc0NDUyNg==&mid=2247484124&idx=2&sn=724de8a941619eeda82c8afe9de6f1b0&chksm=fcdacabdcbad43abd9b05897d528c393f33af55666954e19003625dd1fa27d3a2dbd03ce9793&token=1092713316&lang=zh_CN#rd'
                );
            } elseif ($content == 'unsubscribe') {
                echo sprintf($this->_msg_template["text"], $from_user_name, $to_user_name, time(), '再见，期待您再次关注！');
            } elseif ($content == 'CLICK') {
                $event_key = $xml -> EventKey;
                if ($event_key == 'TODAY_NEW_UPDATE'){
                    echo sprintf($this->_msg_template["todaynew"], 
                        $from_user_name, 
                        $to_user_name, 
                        time(), 
                        '今天要看啥？',
                        '新鲜影视资讯每天送上',
                        'https://mmbiz.qpic.cn/mmbiz_jpg/dKe1WTSiaJo1Fx23PfT2sWDGxPnEopOPJDHaQk3iamCyJkHPQgqnNJqOG502OxAPuRRpdicJkXCbJZ1NsH2w1pm3w/640?wx_fmt=jpeg',
                        'https://mp.weixin.qq.com/s?__biz=MzU3MTc0NDUyNg==&mid=100000340&idx=1&sn=0f191ce945abb46a7509c332af2a70e7&chksm=7cdaca354bad4323e09d2460687cf3cb529c89755cd25ef8495cc55788ab774bf6a2451f8d34#rd',
                        '欢迎关注“优觅Lu电影”, 公众号使用指南',
                        '及时获取国内外最新的即将上映的电影及预告片、电影剪辑，以及电影资讯',
                        'https://mmbiz.qpic.cn/mmbiz_jpg/dKe1WTSiaJo0sAIw7Z8XmCnna4aRykIVdtN3Q3vNPh2EU9lwep2G7bfEUxYa4kuVeXiaep2Dwp8wLORtYUWw7cTQ/640?wx_fmt=jpeg',
                        'https://mp.weixin.qq.com/s?__biz=MzU3MTc0NDUyNg==&mid=2247484124&idx=2&sn=724de8a941619eeda82c8afe9de6f1b0&chksm=fcdacabdcbad43abd9b05897d528c393f33af55666954e19003625dd1fa27d3a2dbd03ce9793&token=1092713316&lang=zh_CN#rd'
                        );
                    
                }
            }

            $this->logWxRequest($from_user_name, $msgtype, $content, $xml->CreateTime);
        } else {
            die('success');
        }
    }


    private $_msg_template = array(
        "text" => "<xml><ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content></xml>",
        "news" => "<xml><ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[news]]></MsgType>
            <ArticleCount>1</ArticleCount>
            <Articles>
            <item><Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url></item></Articles></xml>",
        "todaynew" => "<xml><ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[news]]></MsgType>
            <ArticleCount>2</ArticleCount>
            <Articles>
            <item><Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url></item>
            <item><Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url></item></Articles></xml>"
    );

    /*
         * 根据电影名称抓取相关的MoCode（即MoToken）
         */
    function queryMoToken($title)
    {
        $title = trim($title);
        if (strlen($title) == 0) {
            die('success');
        } else {

            $mongo_db = new DBHandler('mobox');

            $options = [
                'projection' => ['title' => 1, 'mocode' => 1, 'year' => 1],
                'sort' => ['year' => -1, 'hot' => -1, 'latime' => -1],
                'skip' => 0,
                'limit' => 20
            ];
            $quer = $title;
            // $title = '.*' . str_replace(' ', '.*', $title) . '.*';
            // $title = new MongoDB\BSON\Regex($title);

            $querarr = explode(' ', $quer);
            $querarr = array_filter($querarr);
            $querarr = array_unique($querarr);
            $quer_filter_year = [];
            $quer_str = '';
            $now_year = intval(date('Y')) + 5;
            foreach ($querarr as $qak => $qav) {
                $tryyear = intval($qav);
                $qav_isyear = 1887 < $tryyear && $tryyear < $now_year;
                if ($qav_isyear) {
                    array_push($quer_filter_year, ['year' => $tryyear]);
                    array_push($quer_filter_year, ['year' => "$tryyear"]);
                } else {
                    $quer_str .= $qav . '.*';
                }
            }

            $filter = [
                '$and' => [
                    ['$or' => [['isoff' => 0], ['isoff' => '0']]],
                    ['subtype' => ['$nin'=>['情色']]]
                ]
            ];

            if (strlen($quer_str)>0) {
                $quer_reg = new MongoDB\BSON\Regex('.*' . $quer_str);
                $quer_filter_title = [];
                array_push($quer_filter_title, ['title' => ['$regex' => $quer_reg, '$options' => 'i']]);
                array_push($quer_filter_title, ['title_en' => ['$regex' => $quer_reg, '$options' => 'i']]);
                array_push($quer_filter_title, ['alias' => ['$regex' => $quer_reg, '$options' => 'i']]);
                array_push($quer_filter_title, ['subtype' => ['$regex' => $quer_reg, '$options' => 'i']]);
                array_push($quer_filter_title, ['actor' => ['$regex' => $quer_reg, '$options' => 'i']]);
                array_push($filter['$and'], ['$or' => $quer_filter_title]);
            }

            if(count($quer_filter_year)>0){
                array_push($filter['$and'], ['$or' => $quer_filter_year]);
            }
            // var_dump(json_encode($filter, JSON_PRETTY_PRINT));
            return $mongo_db->query($this->media_collec, $filter, $options);
        }
    }

    /*
         * 记录微信搜索信息，
         * 用于异常报警，信息补录等
         */
    function logWxRequest($from_user, $msgtype, $content, $ctime, $reply = null)
    {
        if (empty($content)) {
            return;
        }
        $from_user = trim($from_user);
        $msgtype = trim($msgtype);
        $content = trim($content);
        $ctime = trim($ctime);
        $mongo_db = new DBHandler('mobox');

        $data['fromuser'] = $from_user;
        $data['msgtype'] = $msgtype;
        $data['content'] = $content;
        $data['ctime'] = $ctime;
        $data['reply'] = $reply;

        $mongo_db->insert($data, "wx_search_log");
    }

    // 读来自微信的xml消息，并解析成对象
    function loadResXml()
    {
        // $post_data = $GLOBALS["HTTP_RAW_POST_DATA"]; 
        $post_data = file_get_contents('php://input');
        // $post_data = "<xml>
        //     <ToUserName><![CDATA[ymlshow]]></ToUserName>
        //      <FromUserName><![CDATA[一号铁粉]]></FromUserName>
        //      <CreateTime>1460537339</CreateTime>
        //      <MsgType><![CDATA[text]]></MsgType>
        //      <Content><![CDATA[ 回到 ]]></Content>
        //      <MsgId>6272960105994287618</MsgId>
        //      </xml>";
        // $post_data = "<xml>
        //     <ToUserName><![CDATA[toUser]]></ToUserName>
        //     <FromUserName><![CDATA[FromUser]]></FromUserName>
        //     <CreateTime>123456789</CreateTime>
        //     <MsgType><![CDATA[event]]></MsgType>
        //     <Event><![CDATA[unsubscribe]]></Event></xml>";
        // $post_data = "<xml>
        //     <ToUserName><![CDATA[toUser]]></ToUserName>
        //     <FromUserName><![CDATA[FromUser]]></FromUserName>
        //     <CreateTime>123456789</CreateTime>
        //     <MsgType><![CDATA[event]]></MsgType>
        //     <Event><![CDATA[CLICK]]></Event>
        //     <EventKey><![CDATA[today new]]></EventKey></xml>";
        if (empty($post_data)) {
            die('no data');
        } else {
            return simplexml_load_string($post_data);
        }
    }

    function doSign()
    {
        if ($this->checkSignation()) {
            $echostr = $_GET['echostr'];
            if ($echostr) {
                echo $echostr;
            }
        }
    }

    function checkSignation()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $ttn = array($token, $timestamp, $nonce);
        sort($ttn);
        $ttnstr = implode($ttn);
        $ttnstr = sha1($ttnstr);

        if ($ttnstr == $signature) {
            return true;
        } else {
            return false;
        }
    }
}
exit;
