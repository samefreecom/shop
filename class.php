<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>点餐 - 开心铺</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/ico;base64,aWNv">
    <link rel="stylesheet" href="https://cdn.bootcss.com/weui/1.1.2/style/weui.min.css">
    <link rel="stylesheet" href="https://cdn.bootcss.com/jquery-weui/1.2.0/css/jquery-weui.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/aui.css">
    <link rel="stylesheet" href="css/main.css?v<?php echo time() ?>">
    <link rel="stylesheet" href="css/cart.css?v<?php echo time() ?>"">
    <link rel="stylesheet" href="css/theme-color.css">
    <link rel="stylesheet" href="css/classify.css?v<?php echo time() ?>">
    <style>
        html,body{height: 100%;max-width: 640px;margin:0 auto;}
        h1 {
            margin-top: 12px;
        }
        .btn {
            padding-top: 10px;
        }
        .aui-bar-nav{border:none;background: #f5f5f5;}
        .main{height: calc(100% - 4.5rem);}

        #tab1-con1{padding-bottom: 3rem;}
        #tab1-con1,#tab1-con2,#tab1-con3{height: 100%;}
        .aui-tab{border-bottom: solid 1px #eee;background: #fff;height: 42px;}
        .aui-tab-item{color: #666;width: 25%; height: 36px; line-height: 36px}
        .aui-tab-item.aui-active{border:none;color: #000;position: relative;}
        .aui-tab-item.aui-active:after{position: absolute;left: 40%;width: 20%;height: 2px;background: #00ae66;content: '';bottom:0;}
        .zyw-container.content, .zyw-container.content .con {
            height: 100%;
        }
        .zyw-container.content * {
            font-size: 16px!important;
        }
        .settle_box .total_amount dt p {
            font-size: 16px;
        }
        .settle_box .total_amount dd {
            font-size: 14px;
        }
        #total_price {
            width: 200px;
        }
        .settle_box .all_check {
            margin-top: 0px;
        }
        .settle_box .settle_btn {
            line-height: 26px;
        }
        .settle_box .total_amount dt span {
            color: #f34347;
            font-size: 16px;
            line-height: 20px;
        }
        .main .tab-con {
            display: block;
            left: 25%;
        }
    </style>
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
<body>
    <header class="zyw-header">
        <div class="zyw-container white-color">
            <div class="head-l"><a href="javascript:window.history.back(-1)" target="_self"><img src="img/svg/head-return.svg" alt=""></a></div>
            <h1>共乐社区店 - 菜单</h1>
            <div class="head-r"><a href="">切换门店</a></div>
        </div>
    </header>
    <footer class="zyw-footer">
        <div class="zyw-container white-bgcolor">
            <div class="settle_box">
                <dl class="all_check select">
                    <dt>
                        <div class="count_num"><span id="totalcountshow">0</span></div>
                    </dt>
                </dl>
                <dl class="total_amount">
                    <dt>合计：
                        <span id="total_price">
                            ¥ <b id="totalpriceshow">0</b>
                        </span>
                    </dt>
                    <dd>不含运费</dd>
                </dl>
                <a class="settle_btn" href="javascript:void(0);" id="confirm_cart">去结算</a>
            </div>
        </div>
    </footer>
    <section class="zyw-container content">
        <div class="aui-tab" id="tab">
            <div class="aui-tab-item aui-active">全部</div>
            <div class="aui-tab-item">最近点餐</div>
            <div class="aui-tab-item">最近收藏</div>
        </div>
        <div class="main">
            <div class="aui-item" id="tab1-con1">
                <div class="left-menu" id="left">
                    <ul>
                        <li><span>营养套餐</span></li>
                        <li><span>实惠炒菜</span></li>
                    </ul>
                </div>
                <div class="con">
                    <div class="right-con con-active" style="display: none;">
                        <ul>
                            <li>
                                <div class="menu-img"><img src="image/img.jpg"></div>
                                <div class="menu-txt">
                                    <h4 data-icon="00">珍珠奶茶</h4>
                                    <p class="list1">月销量：123</p>
                                    <p class="list2">
                                        <b>￥</b><b>2.00</b>
                                    </p>
                                    <div class="btn">
                                        <button class="minus"></button>
                                        <i>0</i>
                                        <button class="add"></button>
                                        <i class="price">2.00</i>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="menu-img"><img src="image/img.jpg"></div>
                                <div class="menu-txt">
                                    <h4 data-icon="01">辣子鸡</h4>
                                    <p class="list1">月销量：123</p>
                                    <p class="list2">
                                        <b>￥</b><b>3.00</b>
                                    </p>
                                    <div class="btn">
                                        <button class="minus"></button>
                                        <i>0</i>
                                        <button class="add"></button>
                                        <i class="price">3.00</i>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="right-con" style="display: none;">
                        <ul>
                            <li>
                                <div class="menu-img"><img src="image/img.jpg"></div>
                                <div class="menu-txt">
                                    <h4 data-icon="10">宫保鸡丁</h4>
                                    <p class="list1">月销量：123</p>
                                    <p class="list2">
                                        <b>￥</b><b>4.00</b>
                                    </p>
                                    <div class="btn">
                                        <button class="minus"></button>
                                        <i>0</i>
                                        <button class="add"></button>
                                        <i class="price">4.00</i>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="menu-img"><img src="image/img.jpg"></div>
                                <div class="menu-txt">
                                    <h4 data-icon="11">回锅肉</h4>
                                    <p class="list1">月销量：123</p>
                                    <p class="list2">
                                        <b>￥</b><b>168.00</b>
                                    </p>
                                    <div class="btn">
                                        <button class="minus"></button>
                                        <i>0</i>
                                        <button class="add"></button>
                                        <i class="price">5.00</i>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="up1"></div>
                <div class="shopcart-list fold-transition">
                    <div class="list-header">
                        <h1 class="title">已点</h1>
                        <span class="empty">清空所有</span>
                    </div>
                    <div class="list-content">
                        <ul></ul>
                    </div>
                </div>
            </div>
            <div class="aui-item aui-hide" id="tab1-con2">
                <div class="right-con tab-con">
                    <ul>
                        <li>
                            <div class="menu-img"><img src="image/img.jpg"></div>
                            <div class="menu-txt">
                                <h4 data-icon="10">宫保鸡丁</h4>
                                <p class="list1">月销量：123</p>
                                <p class="list2">
                                    <b>￥</b><b>4.00</b>
                                </p>
                                <div class="btn">
                                    <button class="minus"></button>
                                    <i>0</i>
                                    <button class="add"></button>
                                    <i class="price">4.00</i>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="menu-img"><img src="image/img.jpg"></div>
                            <div class="menu-txt">
                                <h4 data-icon="11">回锅肉</h4>
                                <p class="list1">月销量：123</p>
                                <p class="list2">
                                    <b>￥</b><b>168.00</b>
                                </p>
                                <div class="btn">
                                    <button class="minus"></button>
                                    <i>0</i>
                                    <button class="add"></button>
                                    <i class="price">5.00</i>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="up1"></div>
                <div class="shopcart-list fold-transition">
                    <div class="list-header">
                        <h1 class="title">已点</h1>
                        <span class="empty">清空所有</span>
                    </div>
                    <div class="list-content">
                        <ul></ul>
                    </div>
                </div>
            </div>
            <div class="aui-item aui-hide" id="tab1-con3">
                <div class="right-con tab-con">
                    <ul>
                        <li>
                            <div class="menu-img"><img src="image/img.jpg"></div>
                            <div class="menu-txt">
                                <h4 data-icon="00">珍珠奶茶</h4>
                                <p class="list1">月销量：123</p>
                                <p class="list2">
                                    <b>￥</b><b>2.00</b>
                                </p>
                                <div class="btn">
                                    <button class="minus"></button>
                                    <i>0</i>
                                    <button class="add"></button>
                                    <i class="price">2.00</i>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="menu-img"><img src="image/img.jpg"></div>
                            <div class="menu-txt">
                                <h4 data-icon="01">辣子鸡</h4>
                                <p class="list1">月销量：123</p>
                                <p class="list2">
                                    <b>￥</b><b>3.00</b>
                                </p>
                                <div class="btn">
                                    <button class="minus"></button>
                                    <i>0</i>
                                    <button class="add"></button>
                                    <i class="price">3.00</i>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="up1"></div>
                <div class="shopcart-list fold-transition">
                    <div class="list-header">
                        <h1 class="title">已点</h1>
                        <span class="empty">清空所有</span>
                    </div>
                    <div class="list-content">
                        <ul></ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
<script type="text/javascript" src="js/api.js" ></script>
<script src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/add.js"></script>
<script type="text/javascript" src="js/aui-tab.js" ></script>
<script type="text/javascript">
    apiready = function() {
        api.parseTapmode();
    }
    var tab = new auiTab({
        element: document.getElementById("tab"),

    }, function(ret) {
        if (ret) {
            //定义获取对象的所有兄弟节点的函数，
            function siblings(elm) {
                var a = [];
                var p = elm.parentNode.children;
                for (var i = 0, pl = p.length; i < pl; i++) {
                    if (p[i] !== elm) a.push(p[i]);
                }
                return a;
            }
            var index = ret.index;
            var activeC = document.getElementById("tab1-con" + index);
            activeC.className = "";
            for (var i = 0; i < siblings(activeC).length; i++) {
                siblings(activeC)[i].className = "aui-hide";
            }
        }
    });
</script>
</body>
</html>