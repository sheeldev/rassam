jQuery(document).ready(function() {
	var moreinfotracker = new Array();
	var moreinfonumber;

	if (jQuery('.dropdown-menu').length) {
		var $menu = jQuery(".dropdown-menu");
	        $menu.menuAim({
			activateCallback: activateSubmenu,
			deactivateCallback: deactivateSubmenu,
			exitMenu: exitMenu
	        });
	        function activateSubmenu(row) {
			if (typeof(jQuery("img").lazyload) === "function") {
				if (jQuery("img.nav-promo.lazy-nav").length) {
					jQuery("img.nav-promo.lazy-nav").lazyload();
				}
			}
			var $row = $(row),
			submenuId = $row.data("submenuId"),
			$submenu = jQuery("#" + submenuId),
			height = $menu.outerHeight(),
			width = $menu.outerWidth(),
			dir = ((iL['LTR'] == '1') ? 'left' : 'right');
			if (dir == 'left') {
				$submenu.css({
					display: "block",
					top: -1,
					left: width - 3,  // main should overlay submenu
					height: height - 4  // padding for main dropdown's arrow
				});
			}
			else {
				$submenu.css({
					display: "block",
					top: -1,
					right: width - 3,  // main should overlay submenu
					height: height - 4  // padding for main dropdown's arrow
				});
			}
			$row.find("a").addClass("maintain-hover");
	        }
	        function deactivateSubmenu(row) {
			var $row = $(row),
			submenuId = $row.data("submenuId"),
			$submenu = jQuery("#" + submenuId);
			$submenu.css("display", "none");
			$row.find("a").removeClass("maintain-hover");
	        }
		function exitMenu() { // fade white
		}
		if ((is_touch_device() && is_mobile_device()) || jQuery(window).width() < 759) {
			jQuery(".dropdown-menu").find("a.arrow").prop('href', 'javascript:;');
		}
		jQuery(".dropdown-menu li").click(function(e) {
			e.stopPropagation();
	        });
		jQuery(".top-link #nav-hover").hover(
			function(){
				var menuTrigger = $(this);
				setTimeout(function() {
					if (menuTrigger.is(':hover')) {
						$(this).addClass('open');
					}
				}, 500);
			},
			function(){
				setTimeout(function() {
					//bodyOverlay.hide();
					$(this).removeClass('open');
					jQuery(".popover").css("display", "none");
					jQuery("a.maintainHover").removeClass("maintain-hover");
				}, 500);
			}
	        );
	}
	jQuery(document).click(function() {
		jQuery(".popover").css("display", "none");
		jQuery("a.maintainHover").removeClass("maintain-hover");
		if (jQuery('.search-menu-list a').length) {
			jQuery('.search-menu-list').hide();
			jQuery('.search-menu').removeClass('active');
		}
		if (jQuery('.info-link1').length) {
			if (moreinfotracker.length > 0) {
				moreinfonumber = moreinfotracker.pop();
				while (moreinfonumber != undefined) {
					jQuery('#box1-' + moreinfonumber).addClass('closeb');
					jQuery('#box1-' + moreinfonumber).removeClass('open');
					moreinfonumber = moreinfotracker.pop();
				}
			}
		}
	});
	if (jQuery('.setting').length) {
		jQuery(".setting").click(function() {
			jQuery('#search-setting-panel').slideToggle("fast");
			jQuery('.setting').toggleClass("active");
			return false;
		});
	}
	if (jQuery('.setting-block').length) {
		jQuery(".setting-block").click(function() {
			jQuery('#' + jQuery(this).attr('data-id')).slideToggle("fast");
			jQuery('.setting-block').toggleClass("active");
			return false;
		});
	}
	if (jQuery('.menu-link').length) {
		jQuery(".menu-link").click(function() {
			jQuery('.search-menu-list').toggle();
			jQuery('.search-menu').toggleClass('active');
			return false;
		});
	}
	if (jQuery('.search-menu-list a').length) {
		jQuery(".search-menu-list a").click(function() {
			jQuery('.search-menu-list').hide();
			jQuery('.search-menu').removeClass('active');
			return false;
		});
	}
	if (jQuery('#search-menu ul li a').length) {
		orgurl = iL['BASEURL'] + 'search';
		jQuery('#search-menu ul li a').click(function(){
			val = jQuery(this).text();
			catid = jQuery(this).attr("data-catid");
			seourl = jQuery(this).attr("data-seo");
			if (catid == 'stores') {
				jQuery('#globalsearch').attr('action', seourl);
				jQuery('#globalsearch').attr('method', 'get');
				jQuery('#searchmode').val('stores');
				jQuery("#cidfield").val(catid);
				jQuery('#search-menu-selected').text(jQuery.trim(val));
				jQuery('#search_keywords_id').focus();
				jQuery('#itemnumber').attr('name', '');
				jQuery('#cidfield').attr('name', '');
				jQuery('#searchmode').attr('name', '');
			}
			else if (catid == 'brands') {
				jQuery('#globalsearch').attr('action', seourl);
				jQuery('#globalsearch').attr('method', 'get');
				jQuery('#searchmode').val('brands');
				jQuery("#cidfield").val(catid);
				jQuery('#search-menu-selected').text(jQuery.trim(val));
				jQuery('#search_keywords_id').focus();
				jQuery('#itemnumber').attr('name', '');
				jQuery('#cidfield').attr('name', '');
				jQuery('#searchmode').attr('name', '');
			}
			else if (catid == 'auctions') {
				jQuery('#globalsearch').attr('action', seourl);
				jQuery('#globalsearch').attr('method', 'get');
				jQuery('#searchmode').val('auctions');
				jQuery("#cidfield").val(catid);
				jQuery('#search-menu-selected').text(jQuery.trim(val));
				jQuery('#search_keywords_id').focus();
				jQuery('#itemnumber').attr('name', '');
				jQuery('#cidfield').attr('name', '');
				jQuery('#searchmode').attr('name', '');
			}
			else {
				jQuery("#cidfield").val(catid);
				jQuery('#search-menu-selected').text(jQuery.trim(val));
				jQuery('#globalsearch').attr('action', orgurl);
				jQuery('#globalsearch').attr('method', 'post');
				jQuery('#searchmode').val('product');
				jQuery('#search_keywords_id').focus();
				jQuery('#itemnumber').attr('name', 'itemid');
				jQuery('#cidfield').attr('name', 'cid');
				jQuery('#searchmode').attr('name', 'mode');
			}
		});
	}
	if (jQuery('.top-dropdown2 .txtb').length) {
		jQuery('.top-dropdown2 .txtb').hide();
		jQuery('.top-dropdown2 h4:first').addClass('active').next().show();
		jQuery('.top-dropdown2 h4').click(function() {
			if (jQuery(this).next().is(':hidden')) {
				jQuery('.top-dropdown2 h4').removeClass('active').next().slideUp();
				jQuery(this).toggleClass('active').next().slideDown();
			}
			return false;
		});
	}
	jQuery('.favorite-hover').one('mouseenter', function() {
		if (parseInt(iL['UID']) > 0)
		{
			setTimeout("print_favourite_items(5)", 500);
		}
		return false;
	});


	jQuery('*[id*=viewrequest_details]').on('click', function() {
		rid = jQuery(this).attr('data-id');
		dview = jQuery(this).attr('data-view');
		if (jQuery("#requestdetails").hasClass("open")) {
			setTimeout("print_request_details(" + rid + ",'" + dview + "')", 500);
			
		} else {
			setTimeout("print_request_details_div()", 500);
		}
		return false;
	});
	jQuery('#request_details_close').click(function() {
		setTimeout("print_request_details_div()", 500);
		return false;
	});

	jQuery('.hamburger-menu').on('click', function () {
		jQuery(this).toggleClass('animates');
		setTimeout(function () {
			var side = jQuery('#main-content').find('.sidebar');
			side.toggleClass('active');
			jQuery('body,html').css('overflow', 'visible');
		}, 100)



	})
	jQuery(".arrow-link").click(function () {
		jQuery('.product-list').slideToggle("fast");
		jQuery('.arrow-link').toggleClass("active");
		return false;
	});
	jQuery('.arrow-link').one('click', function() {
		if (jQuery('.arrow-link').hasClass('active'))
		{
			print_recently_viewed_items('load', 3, iL['PAGEURL']);
		}
		return false;
	});
	jQuery('.product-list .close').click(function() {
		jQuery('.product-list').slideUp();
		return false;
	});
	jQuery('.info-link1').click(function () {
		setTimeout('print_search_result_moreinfo(' + this.id + ')', 500);
		if (moreinfotracker.length > 0) {
			moreinfonumber = moreinfotracker.pop();
			while (moreinfonumber != undefined) {
				jQuery('#box1-' + moreinfonumber).addClass('closeb');
				jQuery('#box1-' + moreinfonumber).removeClass('open');
				moreinfonumber = moreinfotracker.pop();
			}
			jQuery('#box1-' + this.id).removeClass('closeb');
			jQuery('#box1-' + this.id).addClass('open');
			moreinfotracker.length = 0;
		}
		else {
			jQuery('#box1-' + this.id).removeClass('closeb');
			jQuery('#box1-' + this.id).addClass('open');
		}
		moreinfotracker.push(this.id);
                return false;
        });
	jQuery('.info-box1-close').click(function () {
		jQuery('#box1-' + this.id).removeClass('open');
		jQuery('#box1-' + this.id).addClass('closeb');
		moreinfotracker.length = 0;
		return false;
	});
	jQuery('.info-link4').click(function () {
		if (moreinfotracker.length > 0) {
			moreinfonumber = moreinfotracker.pop();
			while (moreinfonumber != undefined) {
				jQuery('.info-box4-' + moreinfonumber).hide();
				moreinfonumber = moreinfotracker.pop();
			}
			jQuery('.info-box4-' + this.id).slideToggle();
			moreinfotracker.length = 0;
		}
		else {
			jQuery('.info-box4-' + this.id).slideToggle();
		}
		moreinfotracker.push(this.id);
                return false;
        });
	jQuery('.info-box4-close').click(function () {
		jQuery('.info-box4-' + this.id).hide();
		moreinfotracker.length = 0;
		return false;
	});
	jQuery(".phpdebugbar-close-btn").click(function () {
		jQuery('.phpdebugbar-tab').removeClass("phpdebugbar-active");
		jQuery('.phpdebugbar-body').slideToggle();
		return false;
	});
	jQuery(".phpdebugbar-tab").click(function () {
		jQuery('.phpdebugbar-body').show();
		jQuery('.phpdebugbar-tab').removeClass("phpdebugbar-active");
		jQuery('#' + this.id + '.phpdebugbar-tab').addClass("phpdebugbar-active");
		var str = this.id;
		str = str.substring(0, str.length - 4);
		jQuery('.phpdebugbar-panel').removeClass("phpdebugbar-active");
		jQuery('#' + str + '.phpdebugbar-panel').addClass("phpdebugbar-active");
		return false;
	});
	jQuery("#accept-cookie-usage").click(function() {
		jQuery.post(iL['AJAXURL'] + '?do=consent&type=cookieconsent&token=' + iL['TOKEN'], function(data){
			jQuery('#bottom-fixed').slideToggle("fast");
	        });
	});
	// hero slider responsive fix
	(function() {
		jQuery().checktzoffset();
		jQuery().tnshoppingcart();
		jQuery().checkimgmaps();
		jQuery().herosliderhidebuttons();
	}());
});
(function($){
	$.fn.checkimgmaps = function() {
		if (jQuery(".slider").length && jQuery(".slides_container").length && jQuery("[id^=hero]").length && jQuery('img[usemap]').length) {
			if (typeof(jQuery("img[usemap]").rwdImageMaps) === "function") {
				setTimeout("jQuery('img[usemap]').rwdImageMaps();", 3000);
			}
		}
	};
	$.fn.tnshoppingcart = function() {
		if (jQuery('.shoppingcart').length) {
			jQuery('.shoppingcart').one('mouseenter', function() {
				jQuery('#shopping-cart-list').removeClass('hide');
				if (jQuery('#cart-checkout-button').length && jQuery('#cart-checkout-button').length) {
					if ((jQuery('#cart-count').text()*1) <= 0)
					{
						jQuery('#cart-checkout-button').addClass('hide');
					}
					else
					{
						jQuery('#cart-checkout-button').removeClass('hide');
					}
				}
				print_shopping_cart(10, 'shopping-cart');
				return false;
			});
		}
	};
	$.fn.hpaupload = function(image, id, theurl){
		var clone = $('#drop-area-hpa' + id).children().clone();
		$('#drop-area-hpa' + id).html('<div style="margin:50% 50%"><img src="' + iL['CDNIMG'] + 'v5/ico_working.gif" border="0" /></div>');
		var formImage = new FormData();
		formImage.append('userImage', image[0]);
		$.ajax({
			url: iL['AJAXURL'] + '?do=hpadragdropupload&id=' + id + '&url=' + theurl,
			type: 'POST',
			data: formImage,
			contentType: false,
			cache: false,
			processData: false,
			dataType: 'json',
			success: function(data)
			{
				if (data.error == '0')
				{
					$('#drop-area-hpa' + id).html(data.ad);
				}
				else
				{
					$('#drop-area-hpa' + id).html(clone);
					alert(data.note);
				}
			}
		});
	};
	$.fn.categorythumbupload = function(image, cid){
		var clone = $('#drop-area-' + cid).children().clone();
		$('#drop-area-' + cid).html('<div style="margin:50% 50%"><img src="' + iL['CDNIMG'] + 'v5/ico_working.gif" border="0" /></div>');
		var formImage = new FormData();
		formImage.append('userImage', image[0]);
		$.ajax({
			url: iL['AJAXURL'] + '?do=thumbdragdropupload&cid=' + cid,
			type: 'POST',
			data: formImage,
			contentType: false,
			cache: false,
			processData: false,
			dataType: 'json',
			success: function(data) {
				if (data.error == '0')
				{
					$('#drop-area-' + cid).html(data.note);
				}
				else
				{
					$('#drop-area-' + cid).html(clone);
					alert(data.note);
				}
			}
		});
	};
	$.fn.herosliderhidebuttons = function() {
		if (jQuery('.slides_container').length && jQuery('.slides_control').length) {
			if (jQuery('#slider-prev').length) {
				if (jQuery(window).width() <= 913) {
					jQuery('#slider-prev, #slider-next').css('visibility', 'hidden');
				}
				else {
					jQuery('#slider-prev, #slider-next').css('visibility', 'visible');
				}
			}
		}
	};
	$.fn.herosliderresize = function() {
		if (jQuery('.slides_container').length && jQuery('.slides_control').length) {
			if (jQuery('.slides_control').height() > 0) {
				jQuery('.slides_container').css('max-height', jQuery('.slides_control').height());
			}
			if (jQuery('#slider-prev').length) {
				if (jQuery(window).width() <= 913) {
					jQuery('#slider-prev, #slider-next').css('visibility', 'hidden');
				}
				else {
					jQuery('#slider-prev, #slider-next').css('visibility', 'visible');
				}
			}
		}
	};
	$.fn.checktzoffset = function() {
		if (iL['STZ'] != '' && iL['CTZ'] != '' && iL['STZ'] != iL['CTZ'] && iL['CTZO'] >= 0)
		{ // creates tzoffset cookie
			$.post(iL['AJAXURL'] + '?do=tzoffset&ctz=' + iL['CTZ'] + '&token=' + iL['TOKEN'], function(data){});
		}
	};
})(jQuery);
jQuery(function(){
	if (jQuery('#feescart').length) {
		var sidebar = jQuery('#feescart');
		var top = sidebar.offset().top - parseFloat(sidebar.css('margin-top'));
		jQuery(window).scroll(function (event) {
			var y = jQuery(this).scrollTop();
			if (y >= top) {
				sidebar.addClass('fixed');
			}
			else {
				sidebar.removeClass('fixed');
			}
		});
	}
});
(function(){
	var backTop = document.getElementsByClassName('js-cd-top')[0],
	// browser window scroll (in pixels) after which the "back to top" link is shown
	offset = 300,
	//browser window scroll (in pixels) after which the "back to top" link opacity is reduced
	offsetOpacity = 1200,
	scrollDuration = 700
	scrolling = false;
	if ( backTop ) {
		//update back to top visibility on scrolling
		window.addEventListener("scroll", function(event) {
			if( !scrolling ) {
				scrolling = true;
				(!window.requestAnimationFrame) ? setTimeout(checkBackToTop, 250) : window.requestAnimationFrame(checkBackToTop);
			}
		});
		//smooth scroll to top
		backTop.addEventListener('click', function(event) {
			event.preventDefault();
			(!window.requestAnimationFrame) ? window.scrollTo(0, 0) : scrollTop(scrollDuration);
		});
	}
	function checkBackToTop() {
		var windowTop = window.scrollY || document.documentElement.scrollTop;
		( windowTop > offset ) ? addClass(backTop, 'cd-top--show') : removeClass(backTop, 'cd-top--show', 'cd-top--fade-out');
		( windowTop > offsetOpacity ) && addClass(backTop, 'cd-top--fade-out');
		scrolling = false;
	}
	function scrollTop(duration) {
	    var start = window.scrollY || document.documentElement.scrollTop,
	        currentTime = null;

	    var animateScroll = function(timestamp){
	    	if (!currentTime) currentTime = timestamp;
	        var progress = timestamp - currentTime;
	        var val = Math.max(Math.easeInOutQuad(progress, start, -start, duration), 0);
	        window.scrollTo(0, val);
	        if(progress < duration) {
	            window.requestAnimationFrame(animateScroll);
	        }
	    };

	    window.requestAnimationFrame(animateScroll);
	}
	Math.easeInOutQuad = function (t, b, c, d) {
 		t /= d/2;
		if (t < 1) return c/2*t*t + b;
		t--;
		return -c/2 * (t*(t-2) - 1) + b;
	};
	//class manipulations - needed if classList is not supported
	function hasClass(el, className) {
	  	if (el.classList) return el.classList.contains(className);
	  	else return !!el.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'));
	}
	function addClass(el, className) {
		var classList = className.split(' ');
	 	if (el.classList) el.classList.add(classList[0]);
	 	else if (!hasClass(el, classList[0])) el.className += " " + classList[0];
	 	if (classList.length > 1) addClass(el, classList.slice(1).join(' '));
	}
	function removeClass(el, className) {
		var classList = className.split(' ');
	  	if (el.classList) el.classList.remove(classList[0]);
	  	else if(hasClass(el, classList[0])) {
	  		var reg = new RegExp('(\\s|^)' + classList[0] + '(\\s|$)');
	  		el.className=el.className.replace(reg, ' ');
	  	}
	  	if (classList.length > 1) removeClass(el, classList.slice(1).join(' '));
	}
})();
jQuery(window).resize(function() {
	if (jQuery('#side_panel .show')) {
		if (jQuery(window).width() < 872) {
			var width = jQuery(window).width() - 40;
			jQuery("#full-dialog-container-js").width(width);
		}
	}
	if (jQuery('.profile-right-column').length) {
		if (jQuery(window).width() >= 300) {
			jQuery('body').css("overflow", "");
			setTimeout("jQuery('.profile-left-column').removeClass('open')", 300);
			setTimeout("jQuery('.profile-left-column').removeClass('w0plr0')", 300);
			setTimeout("jQuery('.profile-left-column').removeClass('opentb')", 300);
		}
	}
	if (jQuery('.jobprofile-right-column').length) {
		if (jQuery(window).width() >= iL['BOX']) {
			jQuery('body').css("overflow", "");
			setTimeout("jQuery('.jobprofile-right-column').removeClass('fade-40')", 300);
			setTimeout("jQuery('.jobprofile-left-column').removeClass('open')", 300);
			setTimeout("jQuery('.jobprofile-left-column').removeClass('w0plr0')", 300);
			setTimeout("jQuery('.jobprofile-left-column').removeClass('opentb')", 300);
		}
	}
	(function() {
		jQuery().herosliderresize();
	}());
});
jQuery(window).scroll(function() {});
jQuery(window).on('orientationchange', function(event) {
	console.log('Changed orientation: ' + event.orientation);
	if (event.orientation == 'portrait') {
	}
	else if (event.orientation == 'landscape') {
	}
	else {
	}
	jQuery().herosliderresize();
});
