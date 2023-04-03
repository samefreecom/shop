<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>点餐系统 - 开心铺</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/ico;base64,aWNv">
    <link rel="stylesheet" href="css/weui.min.css">
    <link rel="stylesheet" href="css/jquery-weui.min.css">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/swiper.min.css">
    <link rel="stylesheet" href="css/main.css?v<?php echo time() ?>">
    <link rel="stylesheet" href="css/index.css?v<?php echo time() ?>">
    <link rel="stylesheet" href="css/theme-color.css">
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<header class="zyw-header">
    <div class="zyw-container white-color">
        <div class="head-l"><i class="head-l-svg" aria-hidden="true"></i> 共乐社区店</div>
        <div class="head-search">
            <i class="fa fa-search" aria-hidden="true"></i>
            <input type="text" placeholder="输入要搜索的菜品或商品" class="white-color">
        </div>
        <div class="head-r"><a href="javascript:;"><i class="head-r-svg" aria-hidden="true"></i></a></div>
    </div>
</header>
<footer class="zyw-footer">
    <div class="zyw-container white-bgcolor clearfix">
        <div class="weui-tabbar">
            <a href="index.php" class="weui-tabbar__item weui-bar__item--on">
                <div class="weui-tabbar__icon">
                    <img src="./img/svg/foot-1-1.svg" alt="">
                </div>
                <p class="weui-tabbar__label">首页</p>
            </a>
            <a href="class.php" class="weui-tabbar__item">
                <div class="weui-tabbar__icon">
                    <img src="./img/svg/foot-2.svg" alt="">
                </div>
                <p class="weui-tabbar__label">点餐</p>
            </a>
            <a href="find.html" class="weui-tabbar__item">
                <span class="weui-badge" style="position: absolute;top: -.4em;right: 1em;">0</span>
                <div class="weui-tabbar__icon">
                    <img src="./img/svg/foot-3.svg" alt="">
                </div>
                <p class="weui-tabbar__label">商品</p>
            </a>
            <a href="home.html" class="weui-tabbar__item">
                <div class="weui-tabbar__icon">
                    <img src="./img/svg/foot-5.svg" alt="">
                </div>
                <p class="weui-tabbar__label">我的</p>
            </a>
        </div>
    </div>
</footer>
<section class="zyw-container">
    <div class="swiper-container">
        <div class="swiper-wrapper">
            <div class="swiper-slide"><img src="https://img.alicdn.com/imgextra/i4/2590951958/TB2Fz6XfgMPMeJjy1XbXXcwxVXa_!!2590951958.jpg" alt=""></div>
            <div class="swiper-slide"><img src="https://img.alicdn.com/imgextra/i4/2590951958/TB2z.NKbaagSKJjy0FgXXcRqFXa_!!2590951958.jpg" alt=""></div>
            <div class="swiper-slide"><img src="https://img.alicdn.com/imgextra/i4/2590951958/TB2ZxVCbjihSKJjy0FeXXbJtpXa_!!2590951958.jpg" alt=""></div>
        </div>
        <!-- 如果需要分页器 -->
        <div class="swiper-pagination"></div>
    </div>
    <div class="index-class white-bgcolor">
        <div class="weui-flex">
            <div class="weui-flex__item">
                <a href="class.php">
                    <div class="index-class-img">
                        <img src="./img/yqyl.png" alt="">
                    </div>
                    <p class="index-class-text">开始点菜</p>
                </a>
            </div>
            <div class="weui-flex__item">
                <a href="javascript:;">
                    <div class="index-class-img">
                        <img src="./img/sjtx.png" alt="">
                    </div>
                    <p class="index-class-text">语音点菜</p>
                </a>
            </div>
            <div class="weui-flex__item">
                <a href="javascript:;">
                    <div class="index-class-img">
                        <img src="./img/kbsc.png" alt="">
                    </div>
                    <p class="index-class-text">积分兑换</p>
                </a>
            </div>
            <div class="weui-flex__item">
                <a href="javascript:;">
                    <div class="index-class-img">
                        <img src="./img/cjfl.png" alt="">
                    </div>
                    <p class="index-class-text">查看配送</p>
                </a>
            </div>
        </div>
    </div>
    <div class="index-news">
        <div class="news-cont white-bgcolor">
            <strong>最新<em>资讯</em>：</strong>
            <div class="infoBox">
                <ul class="swiper-wrapper">
                    <li class="swiper-slide"><a href="#"><span><i>热</i>点餐系统微信版上线啦！</span></a></li>
                    <li class="swiper-slide"><a href="#"><span>马上就要开始营业了，欢迎预订！</span></a></li>
                </ul>
            </div>
            <ul>
                <li><a href=""></a></li>
            </ul>
            <a href="" class="news-more">更多</a>
        </div>
    </div>
    <div class="index-seckill white-bgcolor">
        <div class="seckill-hd">
            <span class="seckill-hd-title red-color"><i class="fa fa-clock-o" aria-hidden="true"></i> 预订好菜，积分翻倍</span>
            <strong>明日11点后配送</strong>
            <div id="time"></div>
            <div class="seckill-hd-r">新鲜食材，第一食享</div>
        </div>
        <div class="seckill-bd">
            <div class="seckill-wares">
                <div class="swiper-wrapper">
                    <div class="swiper-slide seckill-ware">
                        <a href="item.html">
                            <img src="https://m.360buyimg.com/n1/jfs/t15787/353/109633918/16701/5a8390ef/5a27ae6dNc530b5bb.jpg!q70.jpg" alt="">
                            <p class="red-color">￥<strong>20.00</strong></p>
                            <span>积分 40</span>
                        </a>
                    </div>
                    <div class="swiper-slide seckill-ware">
                        <a href="item.html">
                            <img src="https://m.360buyimg.com/mobilecms/s240x240_jfs/t14287/16/2071526173/41836/766c1953/5a6935d3N17ca68e7.jpg!q70.jpg" alt="">
                            <p class="red-color">￥<strong>18.00</strong></p>
                            <span>积分 36</span>
                        </a>
                    </div>
                    <div class="swiper-slide seckill-ware">
                        <a href="item.html">
                            <img src="https://m.360buyimg.com/mobilecms/s240x240_jfs/t15049/171/1967249015/25679/d554d9a9/5a61d929N027318e3.jpg!q70.jpg" alt="">
                            <p class="red-color">￥<strong>19.00</strong></p>
                            <span>积分 38</span>
                        </a>
                    </div>
                    <div class="swiper-slide seckill-ware">
                        <a href="item.html">
                            <img src="https://m.360buyimg.com//mobilecms/s276x276_jfs/t13030/75/2301251503/262030/ed6dc00d/5a3a09beN645f27a8.jpg!q70.jpg" alt="">
                            <p class="red-color">￥<strong>15.00</strong></p>
                            <span>积分 30</span>
                        </a>
                    </div>
                    <div class="swiper-slide seckill-ware">
                        <a href="item.html">
                            <img src="https://m.360buyimg.com//mobilecms/s276x276_jfs/t11401/34/504898878/183378/5a6a8a48/59f1c3a1N2174c8cc.jpg!q70.jpg" alt="">
                            <p class="red-color">￥<strong>16.00</strong></p>
                            <span>积分 32</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="index-wares">
        <div class="wares-title"><img src="http://gw.alicdn.com/tfs/TB1Aw9JSVXXXXXQXXXXXXXXXXXX-1500-68.png" alt=""></div>
        <div class="wares-cont">
            <ul class="clearfix">
                <li class="col-sm-6 col-xs-6 ware-box">
                    <a href="item.html">
                        <div class="ware-img">
                            <img src="https://m.360buyimg.com/n1/jfs/t15787/353/109633918/16701/5a8390ef/5a27ae6dNc530b5bb.jpg!q70.jpg" alt="">
                            <span class="ware-vip">vip专享</span>
                        </div>
                        <h3 class="ware-title">Apple 苹果 iPhoneX 全新发布</h3>
                        <p class="ware-des">全网通 64G 深空灰</p>
                        <span class="ware-prince red-color">￥8088.00</span>
                    </a>
                </li>
                <li class="col-sm-6 col-xs-6 ware-box">
                    <a href="item.html">
                        <div class="ware-img">
                            <img src="https://m.360buyimg.com/mobilecms/s240x240_jfs/t14125/285/900232321/14466/12dfc3a3/5a163dd1Ne09fad4b.jpg!q70.jpg" alt="">
                            <span class="ware-vip">vip专享</span>
                        </div>
                        <h3 class="ware-title">Apple 苹果 iPhoneX 全新发布</h3>
                        <p class="ware-des">全网通 64G 深空灰</p>
                        <span class="ware-prince red-color">￥8088.00</span>
                    </a>
                </li>
                <li class="col-sm-6 col-xs-6 ware-box">
                    <a href="item.html">
                        <div class="ware-img">
                            <img src="https://m.360buyimg.com/mobilecms/s240x240_jfs/t15811/137/1521616135/14255/a6150285/5a546fadN096d46b4.jpg!q70.jpg" alt="">
                            <span class="ware-vip">vip专享</span>
                        </div>
                        <h3 class="ware-title">Apple 苹果 iPhoneX 全新发布</h3>
                        <p class="ware-des">全网通 64G 深空灰</p>
                        <span class="ware-prince red-color">￥8088.00</span>
                    </a>
                </li>
                <li class="col-sm-6 col-xs-6 ware-box">
                    <a href="item.html">
                        <div class="ware-img">
                            <img src="https://m.360buyimg.com//mobilecms/s276x276_jfs/t5848/205/6830434960/124593/19e83624/596c8b4eN3c8ba6a7.jpg!q70.jpg" alt="">
                            <span class="ware-vip">vip专享</span>
                        </div>
                        <h3 class="ware-title">Apple 苹果 iPhoneX 全新发布</h3>
                        <p class="ware-des">全网通 64G 深空灰</p>
                        <span class="ware-prince red-color">￥8088.00</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</section>
<script src="https://cdn.bootcss.com/jquery/1.11.0/jquery.min.js"></script>
<script src="https://cdn.bootcss.com/jquery-weui/1.2.0/js/jquery-weui.min.js"></script>
<script src="js/swiper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script type="text/javascript">
    // 轮播
    $(document).ready(function () {
        // 顶部轮播图
        var mySwiper = new Swiper ('.swiper-container', {
            // 如果需要分页器
            autoplay:true,
            pagination: {
                el: '.swiper-pagination'
            }
        });
        // 秒杀商品滑动
        var swiper = new Swiper('.seckill-wares', {
            slidesPerView: 3.5,
            spaceBetween: 5,
            freeMode: true
        });
        // 新闻资讯
        var swiper2 = new Swiper('.infoBox', {
            autoplay:true,
            delay: 5000,
            direction: 'vertical'
        });
    })
</script>
</body>
</html>