/*
* rwdImageMaps jQuery plugin v1.6
*
* Allows image maps to be used in a responsive design by recalculating the area coordinates to match the actual image size on load and window.resize
*
* Copyright (c) 2016 Matt Stow
* https://github.com/stowball/jQuery-rwdImageMaps
* http://mattstow.com
* Licensed under the MIT license
*/
;(function(a){a.fn.rwdImageMaps=function(){var c=this;var b=function(){c.each(function(){if(typeof(a(this).attr("usemap"))=="undefined"){return}var e=this,d=a(e);a("<img />").on('load',function(){var g="width",m="height",n=d.attr(g),j=d.attr(m);if(!n||!j){var o=new Image();o.src=d.attr("src");if(!n){n=o.width}if(!j){j=o.height}}var f=d.width()/100,k=d.height()/100,i=d.attr("usemap").replace("#",""),l="coords";a('map[name="'+i+'"]').find("area").each(function(){var r=a(this);if(!r.data(l)){r.data(l,r.attr(l))}var q=r.data(l).split(","),p=new Array(q.length);for(var h=0;h<p.length;++h){if(h%2===0){p[h]=parseInt(((q[h]/n)*100)*f)}else{p[h]=parseInt(((q[h]/j)*100)*k)}}r.attr(l,p.toString())})}).attr("src",d.attr("src"))})};a(window).resize(b).trigger("resize");return this}})(jQuery);
/*
* Slides, A Slideshow Plugin for jQuery
* Intructions: http://slidesjs.com
* By: Nathan Searles, http://nathansearles.com
* Version: 1.1.3
* Updated: February 21th, 2011
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
* http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/
(function ($) {
	$.fn.slides = function (option)
	{
		if (jQuery('#hero_spacer').length)
		{
			jQuery('#hero_spacer').remove();
		}
		option = $.extend({}, $.fn.slides.option, option);
		return this.each(function ()
		{
			$('.' + option.container, $(this)).children().wrapAll('<div class="slides_control"/>');

			var elem = $(this),
			control = $('.slides_control', elem),
			total = control.children().size(),
			width = control.children().outerWidth(),
			height = control.children().outerHeight(),
			start = option.start - 1,
			effect = option.effect.indexOf(',') < 0 ? option.effect : option.effect.replace(' ', '').split(',')[0],
			paginationEffect = option.effect.indexOf(',') < 0 ? effect : option.effect.replace(' ', '').split(',')[1],
			next = 0, prev = 0, number = 0, current = 0, loaded, active, clicked, position, direction, imageParent, pauseTimeout, playInterval;
			function animate(direction, effect, clicked) {
				if (!active && loaded) {
					active = true;
					switch (direction) {
						case 'next':
							prev = current;
							next = current + 1;
							next = total === next ? 0 : next;
							position = width * 2;
							direction = -width * 2;
							current = next;
							break;
						case 'prev':
							prev = current;
							next = current - 1;
							next = next === -1 ? total - 1 : next;
							position = 0;
							direction = 0;
							current = next;
							break;
						case 'pagination':
							next = parseInt(clicked, 10);
							prev = $('.' + option.paginationClass + ' li.current a', elem).attr('href').match('[^#/]+$');
							if (next > prev) {
								position = width * 2;
								direction = -width * 2;
							}
							else {
								position = 0;
								direction = 0;
							}
							current = next;
							break;
					}
					if (effect === 'fade') {
						option.animationStart();
						if (option.crossfade) {
							control.children(':eq(' + next + ')', elem).css({
								zIndex: 10
							}).fadeIn(option.fadeSpeed, option.fadeEasing, function () {
								if (option.autoHeight) {
									control.animate({
										height: control.children(':eq(' + next + ')', elem).outerHeight()
									}, option.autoHeightSpeed, function () {
										control.children(':eq(' + prev + ')', elem).css({
											display: 'none',
											zIndex: 0
										});
										control.children(':eq(' + next + ')', elem).css({
											zIndex: 0
										});
										option.animationComplete(next + 1);
										active = false;
									});
								}
								else {
									control.children(':eq(' + prev + ')', elem).css({
										display: 'none',
										zIndex: 0
									});
									control.children(':eq(' + next + ')', elem).css({
										zIndex: 0
									});
									option.animationComplete(next + 1);
									active = false;
								}
							});
						}
						else {
							option.animationStart();
							control.children(':eq(' + prev + ')', elem).fadeOut(option.fadeSpeed, option.fadeEasing, function() {
								if (option.autoHeight) {
									control.animate({
										height: control.children(':eq(' + next + ')', elem).outerHeight()
										}, option.autoHeightSpeed, function () {
										control.children(':eq(' + next + ')', elem).fadeIn(option.fadeSpeed, option.fadeEasing);
									});
								}
								else {
									control.children(':eq(' + next + ')', elem).fadeIn(option.fadeSpeed, option.fadeEasing, function() {
										if ($.browser.msie) {
											$(this).get(0).style.removeAttribute('filter');
										}
								    });
								}
								option.animationComplete(next + 1);
								active = false;
							});
						}
					}
					else {
						if (iL['LTR'] == '1') {
							control.children(':eq(' + next + ')').css({
								left: position,
								display: 'block'
							});
						}
						else {
							control.children(':eq(' + next + ')').css({
								right: position,
								display: 'block'
							});
						}
						option.animationStart();
						if (iL['LTR'] == '1') {
							control.animate({
								left: direction
							}, option.slideSpeed, option.slideEasing, function() {
								control.css({
									left: -width
								});
								control.children(':eq(' + next + ')').css({
									left: width,
									zIndex: 5
								});
								control.children(':eq(' + prev + ')').css({
									left: width,
									display: 'none',
									zIndex: 0
								});
								option.animationComplete(next + 1);
								active = false;
							});
						}
						else {
							control.animate({
								right: direction
							}, option.slideSpeed, option.slideEasing, function() {
								control.css({
									right: -width
								});
								control.children(':eq(' + next + ')').css({
									right: width,
									zIndex: 5
								});
								control.children(':eq(' + prev + ')').css({
									right: width,
									display: 'none',
									zIndex: 0
								});
								option.animationComplete(next + 1);
								active = false;
							});
						}
					}
					if (option.pagination) {
						$('.' + option.paginationClass + ' li.current', elem).removeClass('current');
						$('.' + option.paginationClass + ' li:eq(' + next + ')', elem).addClass('current');
					}
				}
			}
			function stop() {
				clearInterval(elem.data('interval'));
			}
			function pause() {
				if (option.pause) {
					clearTimeout(elem.data('pause'));
					clearInterval(elem.data('interval'));
					pauseTimeout = setTimeout(function () {
						clearTimeout(elem.data('pause'));
						playInterval = setInterval(function () {
							animate("next", effect);
						}, option.play);
						elem.data('interval', playInterval);
					}, option.pause);
					elem.data('pause', pauseTimeout);
				}
				else {
					stop();
				}
			}
			$('.' + option.container, elem).css({
				display: 'block',
				overflow: 'hidden',
				position: 'relative',
			});

			if (total < 2) {
				return;
			}
			if (start < 0) {
				start = 0;
			}
			if (start > total) {
				start = total - 1;
			}
			if (option.start) {
				current = start;
			}
			if (option.randomize) {
				control.randomize();
			}

			control.children().css({
				//position: 'absolute',
				top: 0,
				left: control.children().outerWidth(),
				zIndex: 0,
				display: 'none'
			});
			if (option.preload) {
				$('.' + option.container, elem).css({
					background: 'url(' + option.preloadImage + ') no-repeat 50% 50%',
					height: '300px'
				});
				var img = control.find('img:eq(' + start + ')').attr('src') + '?' + (new Date()).getTime();
				if ($('img', elem).parent().attr('class') != 'slides_control') {
					imageParent = control.children(':eq(0)')[0].tagName.toLowerCase();
				}
				else {
					imageParent = control.find('img:eq(' + start + ')');
				}
				control.find('img:eq(' + start + ')').attr('src', img).load(function () {
					control.find(imageParent).fadeIn(option.fadeSpeed, option.fadeEasing, function() {
						$(this).css({
							zIndex: 5
						});
						elem.css({
							background: ''
						});
						loaded = true;
					});
				});
			}
			else {
				control.children(':eq(' + start + ')').fadeIn(option.fadeSpeed, option.fadeEasing, function () {
					loaded = true;
				});
			}
			if (option.bigTarget) {
				control.children().css({
					cursor: 'pointer'
				});
				control.children().click(function () {
					animate('next', effect);
					return false;
				});
			}
			if (option.hoverPause && option.play) {
				control.bind('mouseover', function () {
					stop();
				});
				control.bind('mouseleave', function () {
					pause();
				});
			}
			if (option.generateNextPrev) {
				$('.' + option.container, elem).after('<a href="#" class="' + option.prev + '" id="slider-prev">Prev</a>');
				$('.' + option.prev, elem).after('<a href="#" class="' + option.next + '" id="slider-next">Next</a>');
			}
			$('.' + option.next, elem).click(function (e) {
				e.preventDefault();
				if (option.play) {
					pause();
				}
				animate('next', effect);
			});
			$('.' + option.prev, elem).click(function (e) {
				e.preventDefault();
				if (option.play) {
					pause();
				}
				animate('prev', effect);
			});
			if (option.generatePagination) {
				elem.append('<ul class=' + option.paginationClass + '></ul>');
				control.children().each(function () {
					$('.' + option.paginationClass, elem).append('<li><a href="#' + number + '">' + (number + 1) + '</a></li>');
					number++;
				});
			}
			else {
				$('.' + option.paginationClass + ' li a', elem).each(function () {
					$(this).attr('href', '#' + number);
					number++;
				});
			}
			$('.' + option.paginationClass + ' li:eq(' + start + ')', elem).addClass('current');
			$('.' + option.paginationClass + ' li a', elem).click(function () {
				if (option.play) {
					pause();
				}
				clicked = $(this).attr('href').match('[^#/]+$');
				if (current != clicked) {
					animate('pagination', paginationEffect, clicked);
				}
				return false;
			});
			$('a.link', elem).click(function () {
				if (option.play) {
					pause();
				}
				clicked = $(this).attr('href').match('[^#/]+$') - 1;
				if (current != clicked) {
					animate('pagination', paginationEffect, clicked);
				}
				return false;
			});
			if (option.play) {
				playInterval = setInterval(function () {
					animate('next', effect);
				}, option.play);
				elem.data('interval', playInterval);
			}
			if (jQuery('.slides_container').length && jQuery('.slides_control').length && jQuery(window).width() < 1500) {
				ratio = jQuery(window).width() / 1500; // 0.336
				nh = 300 * ratio;
				if (nh > 0) {
					jQuery('.slides_container').css('max-height', nh);
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
		});
	};
	$.fn.slides.option =
	{
		preload: false,
		preloadImage: '/img/loading.gif',
		container: 'slides_container',
		generateNextPrev: false,
		next: 'next',
		prev: 'prev',
		pagination: true,
		generatePagination: true,
		paginationClass: 'pagination',
		fadeSpeed: 350,
		fadeEasing: '',
		slideSpeed: 350,
		slideEasing: '',
		start: 1,
		effect: 'slide',
		crossfade: false,
		randomize: false,
		play: 0,
		pause: 0,
		hoverPause: false,
		autoHeight: false,
		autoHeightSpeed: 350,
		bigTarget: false,
		animationStart: function () {},
		animationComplete: function () {}
	};
	$.fn.randomize = function (callback)
	{
		function randomizeOrder() {
			return (Math.round(Math.random()) - 0.5);
		}
		return ($(this).each(function () {
			var $this = $(this);
			var $children = $this.children();
			var childCount = $children.length;
			if (childCount > 1)
			{
				$children.hide();
				var indices = [];
				for (i = 0; i < childCount; i++)
				{
					indices[indices.length] = i;
				}
				indices = indices.sort(randomizeOrder);
				$.each(indices, function (j, k) {
					var $child = $children.eq(k);
					var $clone = $child.clone(true);
					$clone.show().appendTo($this);
					if (callback !== undefined) {
						callback($child, $clone);
					}
					$child.remove();
				});
			}
		}));
	};
})(jQuery);
