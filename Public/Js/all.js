$(function(){
	
	
	
	$('.subnav').css('height',$(window).height() );	//侧边栏高度
	$('.wrap02 > .content').css('width',$(window).width()-265);//内容块宽度
	$('.login').css('height',$(window).height());//登录高度

	$(window).resize(function(){
		$('.subnav').css('height',$(window).height()-43);	//侧边栏高度
		$('.wrap02 > .content').css('width',$(window).width()-265);//内容块宽度
		$('.login').css('height',$(window).height());//登录高度
	});
	$('.login span').click(function(){//logincheck
		$('.login span').toggleClass('cur');
		if($('.login span').hasClass('cur')==true){
			$('.login .checkval').val('1');
		}else{
			$('.login .checkval').val('0');
		}

	});
	$('.wrap01 h2').click(function(){//user下拉框
		$(".nav_r").toggle();
	});
	//侧边栏
	$('.nav > li > a').click(function(){
		$(".sub-menu").slideUp('linear');
		if($(this).parent().hasClass('cur') || $(this).parent().hasClass('second') ){
			$(this).parent().removeClass('cur');

		}else{
			$(this).parent().addClass('cur').siblings().removeClass('cur');
			$(this).parent().children(".sub-menu").slideDown('linear');
		}

	});

	$('.sub-menu > li').click(function(){//
		$(this).addClass('cur').siblings().removeClass('cur');
	});

	$('.cate_img a').magnificPopup({//zoom
			type: 'image',
			closeOnContentClick: true,
			closeBtnInside: false,
			fixedContentPos: true,
			mainClass: 'mfp-no-margins mfp-with-zoom', // class to remove default margin from left and right side
			image: {
			verticalFit: true
			},
			zoom: {
			enabled: true,
			duration: 300 // don't foget to change the duration also in CSS
			}



	});
	
});

function subnav(i){//侧边状态

	$('.nav li:eq('+i+')').children(".sub-menu").show();
	$('.nav li:eq('+i+')').addClass('cur');
}

