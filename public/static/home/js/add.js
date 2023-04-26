$(function () {

    $("#left li:first-child").addClass("active");
    var e;
	//商品点击增加


	$(".JP_seat .add").click(function(){
		var n = $(this).prev().text();
		var num = parseFloat(n)+1;
		e = $(this).prev();//当前数量
		var ms = e.text(num-1);
		if(ms!=0){      //判断是否显示减号及数量
			e.css("display","inline-block");
			e.prev().css("display","inline-block")
		}
		e.text(num); //设置数量
		var parent = $(this).parent();
		var m=parent.parent().children("h4").text(); //当前商品名称
		var danjia=$(this).next().text(); //获取单价
		var a = $("#totalpriceshow").html();  //获取当前所选总价
		$("#totalpriceshow").html((a * 1 + danjia * 1).toFixed(2));//计算当前所选总价
		var nm = $("#totalcountshow").html(); //获取数量
		$("#totalcountshow").html(nm*1+1);
		jss();   //改变按钮样式
	});

    $(".JP_food_admin .add").click(function(){
        var n = $(this).prev().text();
        var num = parseFloat(n)+1;
        e = $(this).prev();//当前数量
		var ms = e.text(num-1);
	    if(ms!=0){      //判断是否显示减号及数量
	        e.css("display","inline-block");
	        e.prev().css("display","inline-block")
	    }
        e.text(num); //设置数量
        var parent = $(this).parent();
        var m=parent.parent().children("h4").text(); //当前商品名称
        var danjia=$(this).next().text(); //获取单价
	    var a = $("#totalpriceshow").html();  //获取当前所选总价
	    $("#totalpriceshow").html((a * 1 + danjia * 1).toFixed(2));//计算当前所选总价
	    var nm = $("#totalcountshow").html(); //获取数量
	    $("#totalcountshow").html(nm*1+1);
	    jss();   //改变按钮样式
    });
	
    $(".JP_seat .minus").click(function(){
		var e;
		var m = $(this).parent().parent().find(".accountName").text();  //当前商品名字
		var a = parseFloat($(this).siblings(".price").text());  //当前商品单价
		var n = parseInt($(this).next().text())-1;  //当前商品数量
		var s = parseFloat($("#totalpriceshow").text());  //总金额
		if (n<=0) {
			n = 0;
		}
		$('.JP_seat .right-con').each(function () {
			$(this).find('ul li').each(function(){
				if(m==$(this).find('h4').text()){
					e=$(this).find('.add').prev();
					e.text(n);    //赋值给商品列表的数量
					if (n >= 1) {
						e.css("display","inline-block");
						e.prev().css("display","inline-block")
					} else {
						e.css("display","none");
						e.prev().css("display","none")
					}
				}
			});
		});
		if (n >= 0) {
			$(this).parent().prev().children("span:nth-child(2)").text((a*n).toFixed(2));
			$("#totalcountshow").text(parseInt($("#totalcountshow").text())-1);
			$("#totalpriceshow").text((s-a).toFixed(2));
		}
    });
	$(".JP_food_admin .minus").click(function(){
		var e;
		var m = $(this).parent().parent().find(".accountName").text();  //当前商品名字
		var a = parseFloat($(this).siblings(".price").text());  //当前商品单价
		var n = parseInt($(this).next().text())-1;  //当前商品数量
		$('.right-con ul li').each(function(){
			if(m==$(this).find('h4').text()){
				e=$(this).find('.add').prev();
			}
		});
		if (n<=0) {
			n = 0;
		}
		$(this).next().text(n);
		e.text(n);
	});
	//购物车 - 加
	$(document).on('click','.ad2',function(){
		var n = parseInt($(this).prev().text())+1;
		$(this).prev().text(n);    //当前商品数量+1
		e.text(n);    //赋值给商品列表的数量
		var p = parseFloat($(this).next().text());    //隐藏的价格
		$(this).parent().prev().children("span.accountPrice").text((p*n).toFixed(2));  //计算该商品规格的总价值
	   
		$("#totalcountshow").text(parseFloat($("#totalcountshow").text())+1);   //总数量＋1
		$("#totalpriceshow").text((parseFloat($("#totalpriceshow").text())+p).toFixed(2));   //总价加上该商品价格
	});
	
	//购物车 - 减
	$('.list-content').on('click','.ms2',function(){
		var e;
		var m = $(this).parent().parent().find(".accountName").text();  //当前商品名字
		var a = parseFloat($(this).siblings(".price").text());  //当前商品单价
		var n = parseInt($(this).next().text())-1;  //当前商品数量
		var s = parseFloat($("#totalpriceshow").text());  //总金额
		var znum=0;

		$(".list-content ul li").each(function(){
			znum = znum + parseInt($(this).find('.li_acount').text());
		})
		znum = znum-1;

		$('.right-con ul li').each(function(){
			if(m==$(this).find('h4').text()){
				e=$(this).find('.add').prev();
			}
		})
		if(n == 0){
			$(this).parent().parent().remove();

			e.css("display","none");
            e.prev().css("display","none")

            if(znum==0){
            	$(".up1").hide();
            }
		}
		$(this).next().text(n);
	    e.text(n);    //赋值给商品列表的数量
		$(this).parent().prev().children("span:nth-child(2)").text((a*n).toFixed(2));
		
		$("#totalcountshow").text(parseInt($("#totalcountshow").text())-1);
		$("#totalpriceshow").text((s-a).toFixed(2));
		if(parseFloat($("#totalcountshow").text())==0){
			$(".shopcart-list").hide();
		}
	});

    function jss() {
        var m = $("#totalcountshow").html();
        if (m > 0) {
            $(".right").find("a").removeClass("disable");
        } else {
            $(".right").find("a").addClass("disable");
        }
    };
	
    //选项卡
    $(".con>div").hide();
    $(".con>div:eq(0)").show();
    $(".left-menu li").click(function(){
        $(this).addClass("active").siblings().removeClass("active");
        var n = $(".left-menu li").index(this);
        $(".left-menu li").index(this);
        $(".con>div").hide();
        $(".con>div:eq("+n+")").show();
    });
});
