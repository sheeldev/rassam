(function($){
	$.fn.dragdropads = function() {
		// drag and drop ads & category thumbnails
		jQuery(".drop-area, .imgb").on('dragenter', function (e) {
			e.preventDefault();
			jQuery(this).css('background', '#BBD5B8');
		});
		jQuery(".drop-area, .imgb").on('dragleave', function (e) {
			e.preventDefault();
			jQuery(this).css('background', '#FFFFFF');
		});
		jQuery(".drop-area, .imgb").on('dragover', function (e) {
			e.preventDefault();
		});
		jQuery(".drop-area").on('drop', function (e) {
			e.preventDefault();
			jQuery(this).css('background', '#fff');
			var image = e.originalEvent.dataTransfer.files;
			var cid = jQuery(this).attr("data-catid");
			jQuery().categorythumbupload(image, cid);
		});
	};
	$.fn.carousels = function(){
		var maxSlides = 5;
		if (jQuery('.carousel_home_requests_product').length)
		{
			var page_requests = 0;
			var slider_requests = jQuery('.carousel_home_requests_product ul.slides').bxSlider({
				minSlides: 1,
				maxSlides: maxSlides,
				slideWidth: 250,
				slideMargin: 10,
				nextSelector: '#c4r',
				prevSelector: '#c4l',
				nextText: 'Next',
				prevText: 'Prev',
				swipe: true,
				mouseWheel: true,
			        onSliderLoad: function () {
					var $sliderImgs = $(".carousel_home_requests_product .slide img");
					var start = 0;
					var stop = maxSlides;
					for (var i = start; i < stop; i++) {
						var selecter = '[data-src="' + $sliderImgs.eq(i).data('src') + '"]';
						$(selecter).attr('src', $(selecter).data('src')).one('load', function(){
				    			$(this).removeClass('lazy');
						});
					}
			        },
			        onSlideBefore: function () {
					page_requests = slider_requests.getCurrentSlide();
					var $sliderImgs = $(".carousel_home_requests_product .slide img");
					var start = page_requests * maxSlides;
					var stop = start + maxSlides;
					for (var i = start; i < stop; i++) {
						var selecter = '[data-src="' + $sliderImgs.eq(i).data('src') + '"]';
						$(selecter).attr('src', $(selecter).data('src')).one('load', function(){
					    		$(this).removeClass('lazy');
						});
					}
			        }
			});
			if (jQuery(".carousel_home_requests_product ul li").length < maxSlides)
			{
				jQuery("#c4r").addClass('disabled');
			}
		}

		if (jQuery('.carousel_home_staffpicks_product').length)
		{
			var page_staffpicks = 0;
			var slider_staffpicks = jQuery('.carousel_home_staffpicks_product ul.slides').bxSlider({
				minSlides: 1,
				maxSlides: maxSlides,
				slideWidth: 150,
				slideMargin: 10,
				nextSelector: '#c5r',
				prevSelector: '#c5l',
				nextText: 'Next',
				prevText: 'Prev',
				swipe: true,
				mouseWheel: true,
			        onSliderLoad: function () {
					var $sliderImgs = $(".carousel_home_staffpicks_product .slide img");
					var start = 0;
					var stop = maxSlides;
					for (var i = start; i < stop; i++) {
						var selecter = '[data-src="' + $sliderImgs.eq(i).data('src') + '"]';
						$(selecter).attr('src', $(selecter).data('src')).one('load', function(){
				    			$(this).removeClass('lazy');
						});
					}
			        },
			        onSlideBefore: function () {
					page_staffpicks = slider_staffpicks.getCurrentSlide();
					var $sliderImgs = $(".carousel_home_staffpicks_product .slide img");
					var start = page_staffpicks * maxSlides;
					var stop = start + maxSlides;
					for (var i = start; i < stop; i++) {
						var selecter = '[data-src="' + $sliderImgs.eq(i).data('src') + '"]';
						$(selecter).attr('src', $(selecter).data('src')).one('load', function(){
					    		$(this).removeClass('lazy');
						});
					}
			        }
			});
			if (jQuery(".carousel_home_staffpicks_product ul li").length < maxSlides)
			{
				jQuery("#c5r").addClass('disabled');
			}
		}
		if (jQuery('.carousel_home_cryptoonly_product').length)
		{
			var page_cryptoonly = 0;
			var slider_cryptoonly = jQuery('.carousel_home_cryptoonly_product ul.slides').bxSlider({
				minSlides: 1,
				maxSlides: maxSlides,
				slideWidth: 150,
				slideMargin: 10,
				nextSelector: '#c6r',
				prevSelector: '#c6l',
				nextText: 'Next',
				prevText: 'Prev',
				swipe: true,
				mouseWheel: true,
			        onSliderLoad: function () {
					var $sliderImgs = $(".carousel_home_cryptoonly_product .slide img");
					var start = 0;
					var stop = maxSlides;
					for (var i = start; i < stop; i++) {
						var selecter = '[data-src="' + $sliderImgs.eq(i).data('src') + '"]';
						$(selecter).attr('src', $(selecter).data('src')).one('load', function(){
				    			$(this).removeClass('lazy');
						});
					}
			        },
			        onSlideBefore: function () {
					page_cryptoonly = slider_cryptoonly.getCurrentSlide();
					var $sliderImgs = $(".carousel_home_cryptoonly_product .slide img");
					var start = page_cryptoonly * maxSlides;
					var stop = start + maxSlides;
					for (var i = start; i < stop; i++) {
						var selecter = '[data-src="' + $sliderImgs.eq(i).data('src') + '"]';
						$(selecter).attr('src', $(selecter).data('src')).one('load', function(){
					    		$(this).removeClass('lazy');
						});
					}
			        }
			});
			if (jQuery(".carousel_home_cryptoonly_product ul li").length < maxSlides)
			{
				jQuery("#c6r").addClass('disabled');
			}
		}
		if (jQuery('.carousel_home_pointsonly_product').length)
		{
			var page_pointsonly = 0;
			var slider_pointsonly = jQuery('.carousel_home_pointsonly_product ul.slides').bxSlider({
				minSlides: 1,
				maxSlides: maxSlides,
				slideWidth: 150,
				slideMargin: 10,
				nextSelector: '#c7r',
				prevSelector: '#c7l',
				nextText: 'Next',
				prevText: 'Prev',
				swipe: true,
				mouseWheel: true,
			        onSliderLoad: function () {
					var $sliderImgs = $(".carousel_home_pointsonly_product .slide img");
					var start = 0;
					var stop = maxSlides;
					for (var i = start; i < stop; i++) {
						var selecter = '[data-src="' + $sliderImgs.eq(i).data('src') + '"]';
						$(selecter).attr('src', $(selecter).data('src')).one('load', function(){
				    			$(this).removeClass('lazy');
						});
					}
			        },
			        onSlideBefore: function () {
					page_pointsonly = slider_pointsonly.getCurrentSlide();
					var $sliderImgs = $(".carousel_home_pointsonly_product .slide img");
					var start = page_pointsonly * maxSlides;
					var stop = start + maxSlides;
					for (var i = start; i < stop; i++) {
						var selecter = '[data-src="' + $sliderImgs.eq(i).data('src') + '"]';
						$(selecter).attr('src', $(selecter).data('src')).one('load', function(){
					    		$(this).removeClass('lazy');
						});
					}
			        }
			});
			if (jQuery(".carousel_home_pointsonly_product ul li").length < maxSlides)
			{
				jQuery("#c7r").addClass('disabled');
			}
		}
		if (jQuery('.carousel_home_featured_product').length)
		{
			var page_featured = 0;
			var slider_featured = jQuery('.carousel_home_featured_product ul.slides').bxSlider({
				minSlides: 1,
				maxSlides: maxSlides,
				slideWidth: 150,
				slideMargin: 10,
				nextSelector: '#c1r',
				prevSelector: '#c1l',
				nextText: 'Next',
				prevText: 'Prev',
				swipe: true,
				mouseWheel: true,
			        onSliderLoad: function () {
					var $sliderImgs = $(".carousel_home_featured_product .slide img");
					var start = 0;
					var stop = maxSlides;
					for (var i = start; i < stop; i++) {
						var selecter = '[data-src="' + $sliderImgs.eq(i).data('src') + '"]';
						$(selecter).attr('src', $(selecter).data('src')).one('load', function(){
				    			$(this).removeClass('lazy');
						});
					}
			        },
			        onSlideBefore: function () {
					page_featured = slider_featured.getCurrentSlide();
					var $sliderImgs = $(".carousel_home_featured_product .slide img");
					var start = page_featured * maxSlides;
					var stop = start + maxSlides;
					for (var i = start; i < stop; i++) {
						var selecter = '[data-src="' + $sliderImgs.eq(i).data('src') + '"]';
						$(selecter).attr('src', $(selecter).data('src')).one('load', function(){
					    		$(this).removeClass('lazy');
						});
					}
			        }
			});
			if (jQuery(".carousel_home_featured_product ul li").length < maxSlides)
			{
				jQuery("#c1r").addClass('disabled');
			}
		}
		if (jQuery('.carousel_home_seller_watchlist').length)
		{
			var page_sellerwatchlist = 0;
			var slider_sellerwatchlist = jQuery('.carousel_home_seller_watchlist ul.slides').bxSlider({
				minSlides: 1,
				maxSlides: maxSlides,
				slideWidth: 150,
				slideMargin: 10,
				nextSelector: '#c2r',
				prevSelector: '#c2l',
				nextText: 'Next',
				prevText: 'Prev',
				swipe: true,
				mouseWheel: true,
			        onSliderLoad: function(){
					var $sliderImgs = $(".carousel_home_seller_watchlist .slide img");
					var start = 0;
					var stop = maxSlides;
					for (var i = start; i < stop; i++) {
						var selecter = '[data-src="' + $sliderImgs.eq(i).data('src') + '"]';
						$(selecter).attr('src', $(selecter).data('src')).one('load', function(){
				    			$(this).removeClass('lazy');
						});
					}
			        },
			        onSlideBefore: function(){
					page_sellerwatchlist = slider_sellerwatchlist.getCurrentSlide();
					var $sliderImgs = $(".carousel_home_seller_watchlist .slide img");
					var start = page_sellerwatchlist * maxSlides;
					var stop = start + maxSlides;
					for (var i = start; i < stop; i++) {
						var selecter = '[data-src="' + $sliderImgs.eq(i).data('src') + '"]';
						$(selecter).attr('src', $(selecter).data('src')).one('load', function(){
					    		$(this).removeClass('lazy');
						});
					}
			        }
			});
			if (jQuery(".carousel_home_seller_watchlist ul li").length < maxSlides)
			{
				jQuery("#c2r").addClass('disabled');
			}
		}
		if (jQuery('.carousel_home_latest_product').length)
		{
			var page_latest = 0;
			var slider_latest = jQuery('.carousel_home_latest_product ul.slides').bxSlider({
				minSlides: 1,
				maxSlides: maxSlides,
				slideWidth: 150,
				slideMargin: 10,
				nextSelector: '#c3r',
				prevSelector: '#c3l',
				nextText: 'Next',
				prevText: 'Prev',
				swipe: true,
				mouseWheel: true,
			        onSliderLoad: function(){
					var $sliderImgs = $(".carousel_home_latest_product .slide img");
					var start = 0;
					var stop = maxSlides;
					for (var i = start; i < stop; i++) {
						var selecter = '[data-src="' + $sliderImgs.eq(i).data('src') + '"]';
						$(selecter).attr('src', $(selecter).data('src')).one('load', function(){
				    			$(this).removeClass('lazy');
						});
					}
			        },
			        onSlideBefore: function(){
					page_latest = slider_latest.getCurrentSlide();
					var $sliderImgs = $(".carousel_home_latest_product .slide img");
					var start = page_latest * maxSlides;
					var stop = start + maxSlides;
					for (var i = start; i < stop; i++) {
						var selecter = '[data-src="' + $sliderImgs.eq(i).data('src') + '"]';
						$(selecter).attr('src', $(selecter).data('src')).one('load', function(){
					    		$(this).removeClass('lazy');
						});
					}
			        }
			});
			if (jQuery(".carousel_home_latest_product ul li").length < maxSlides)
			{
				jQuery("#c3r").addClass('disabled');
			}
		}
		if (jQuery('.carousel_home_endingsoon_product').length)
		{
			var page_endingsoon = 0;
			var slider_endingsoon = jQuery('.carousel_home_endingsoon_product ul.slides').bxSlider({
				minSlides: 1,
				maxSlides: maxSlides,
				slideWidth: 150,
				slideMargin: 10,
				nextSelector: '#c4r',
				prevSelector: '#c4l',
				nextText: 'Next',
				prevText: 'Prev',
				swipe: true,
				mouseWheel: true,
			        onSliderLoad: function(){
					var $sliderImgs = $(".carousel_home_endingsoon_product .slide img");
					var start = 0;
					var stop = maxSlides;
					for (var i = start; i < stop; i++) {
						var selecter = '[data-src="' + $sliderImgs.eq(i).data('src') + '"]';
						$(selecter).attr('src', $(selecter).data('src')).one('load', function(){
				    			$(this).removeClass('lazy');
						});
					}
			        },
			        onSlideBefore: function(){
					page_endingsoon = slider_endingsoon.getCurrentSlide();
					var $sliderImgs = $(".carousel_home_endingsoon_product .slide img");
					var start = page_endingsoon * maxSlides;
					var stop = start + maxSlides;
					for (var i = start; i < stop; i++) {
						var selecter = '[data-src="' + $sliderImgs.eq(i).data('src') + '"]';
						$(selecter).attr('src', $(selecter).data('src')).one('load', function(){
					    		$(this).removeClass('lazy');
						});
					}
			        }
			});
			if (jQuery(".carousel_home_endingsoon_product ul li").length < maxSlides)
			{
				jQuery("#c4r").addClass('disabled');
			}
		}
		if (jQuery('.carousel_home_related_product').length)
		{
			var page_related = 0;
			var slider_related = jQuery('.carousel_home_related_product ul.slides').bxSlider({
				minSlides: 1,
				maxSlides: maxSlides,
				slideWidth: 150,
				slideMargin: 10,
				nextSelector: '#c1rd',
				prevSelector: '#c1ld',
				nextText: 'Next',
				prevText: 'Prev',
				swipe: true,
				mouseWheel: true,
			        onSliderLoad: function(){
					var $sliderImgs = $(".carousel_home_related_product .slide img");
					var start = 0;
					var stop = maxSlides;
					for (var i = start; i < stop; i++) {
						var selecter = '[data-src="' + $sliderImgs.eq(i).data('src') + '"]';
						$(selecter).attr('src', $(selecter).data('src')).one('load', function(){
				    			$(this).removeClass('lazy');
						});
					}
			        },
			        onSlideBefore: function(){
					page_related = slider_related.getCurrentSlide();
					var $sliderImgs = $(".carousel_home_related_product .slide img");
					var start = page_related * maxSlides;
					var stop = start + maxSlides;
					for (var i = start; i < stop; i++) {
						var selecter = '[data-src="' + $sliderImgs.eq(i).data('src') + '"]';
						$(selecter).attr('src', $(selecter).data('src')).one('load', function(){
					    		$(this).removeClass('lazy');
						});
					}
			        }
			});
			if (jQuery(".carousel_home_related_product ul li").length < maxSlides)
			{
				jQuery("#c1rd").addClass('disabled');
			}
		}
	}
	$.fn.sliders = function(){
		if (jQuery('.slider').length)
		{
			jQuery('.slider').slides({
				preload: true,
				preloadImage: iL['CDNIMG'] + 'v5/ico_working.gif',
				effect: 'slide',
				crossfade: true,
				play: 6500,
				slideSpeed: 250,
				fadeSpeed: 400,
				generateNextPrev: true,
				generatePagination: true,
				hoverPause: true,
				autoHeight: false
			});
		}
	}
	$.fn.lazydetect = function(){
		if (typeof(jQuery("img").lazyload) === "function")
		{
			jQuery(".lazy").lazyload();
		}
	}
})(jQuery);
jQuery(document).ready(function () {
	(function(){
		jQuery().carousels();
		jQuery().sliders();
		jQuery().dragdropads();
		jQuery().lazydetect();
	}());
});
