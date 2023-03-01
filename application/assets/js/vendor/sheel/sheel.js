/**
 * Created by mai100it on 08/10/2019.
 */
(function($){
    "use strict"; // Start of use strict

    //fixed image
    function detail_fixed(){
        //check if desktop
        if($(window).width()>767){
            //check if has class fixed
            if($('.detail-fixed-info').length>0){
                //top height of detail-fixed-info
                var ot = $('.detail-fixed-info').offset().top;
                //full height
                var sh = $('.detail-fixed-info').height();
                //menu height
                var h_menu = 0;
                //check if fixed header
                if($('.fixed-header').length>0)  h_menu = $('.fixed-header').height();
                //get height detail-info
                var height = $('.detail-info').map(function (){
                    return $(this).height();
                }).get();
                var dh = Math.max.apply(null, height);
                //get current position
                var st = $(window).scrollTop();
                var top = $(window).scrollTop() - ot + h_menu;
                top = top + 20;
                //if condition sastified, add style to class
                if(st>ot&&st<ot+sh-dh){
                    $('.detail-fixed-info').addClass('onscroll');
                    $('.detail-fixed-info.onscroll .detail-info').css('top',top+'px');
                }
                else if(st<ot){
                    $('.detail-info').css('top',0);
                }else{
                    $('.detail-fixed-info').removeClass('onscroll');
                }
            }
        }else{
            $('.detail-info').css('top',0);
        }
    }
    //fancybox image
    function fancybox_image(){
        if($('.fancybox-buttons').length>0){
            $('.fancybox-buttons').fancybox({
                openEffect  : 'elastic',
                closeEffect : 'elastic',

                prevEffect : 'none',
                nextEffect : 'none',

                closeBtn  : true,

                helpers : {
                    title : {
                        type : 'inside'
                    },
                    buttons	: {}
                },

                afterLoad : function() {
                    this.title = 'Image ' + (this.index + 1) + ' of ' + this.group.length + (this.title ? ' - ' + this.title : '');
                }
            });
        }
    }
    // login popup
    function login_popup(){
        $('.popup-form').find('input:not(.button)').on('focusin',function(){
            $(this).parent().addClass('input-focus');
        }).on('focusout',function(){
            $(this).parent().removeClass('input-focus');
        })
        $('.popup-form').find('input:not(.button)').each(function(){
            if($(this).val()) $(this).parent().addClass('has-value');
            else $(this).parent().removeClass('has-value');
        })
        $('.popup-form').find('input:not(.button)').on('keyup',function(){
            $(this).parent().removeClass('invalid');
            if($(this).val()) $(this).parent().addClass('has-value');
            else $(this).parent().removeClass('has-value');
        })
        $('.open-login-form,.login-popup,.register-popup,.lostpass-popup').on('click',function(e){
            if(!$(this).parents('.disable-popup').length > 0){
                e.preventDefault();
                $('.login-popup-content-wrap').fadeIn();
                if($(this).hasClass('register-popup')) $('.register-link').trigger('click');
                if($(this).hasClass('lostpass-popup')) $('.lostpass-link').trigger('click');
            }
        })
        $('.close-login-form,.popup-overlay').on('click',function(e){
            e.preventDefault();
            $('.login-popup-content-wrap').fadeOut();
        })
        $('.popup-redirect').on('click',function(e){
            e.preventDefault();
            var id = $(this).attr('href');
            $('.ms-default').fadeOut();
            $('.popup-form').removeClass('active');
            $(id).parents('.popup-form').addClass('active');
        })
        $('#login_error a').on('click',function(){
            $('.lostpass-link').trigger('click');
        })
    }

    // fix append css
    function fix_css_append(){
        var css_data = String($('#s7upf-theme-style-inline-css').html());
        $('#s7upf-theme-style-inline-css').remove();
        if(css_data) $('head').append('<'+'style id="s7upf-theme-style-inline-css">'+css_data+'</style>');
    }
    // Letter popup
    function letter_popup(){
        //Popup letter
        var content = $('#boxes-content').html();
        $('#boxes-content').html('');
        if(content) $('body').append('<div id="boxes">'+content+'</div>');
        if($('#boxes').html() != ''){
            var id = '#dialog';
            //Get the screen height and width
            var maskHeight = $(document).height();
            var maskWidth = $(window).width();

            //Set heigth and width to mask to fill up the whole screen
            $('#mask').css({'width':maskWidth,'height':maskHeight});

            //transition effect
            $('#mask').fadeIn(500);
            $('#mask').fadeTo("slow",0.6);

            //Get the window height and width
            var winH = $(window).height();
            var winW = $(window).width();

            //Set the popup window to center
            $(id).css('top',  winH/2-$(id).height()/2);
            $(id).css('left', winW/2-$(id).width()/2);

            //transition effect
            $(id).fadeIn(2000);

            //if close button is clicked
            $('.window .close-popup').on('click',function (e) {
                //Cancel the link behavior
                e.preventDefault();

                $('#mask').hide();
                $('.window').hide();
            });

            //if mask is clicked
            $('#mask').on('click',function () {
                $(this).hide();
                $('.window').hide();
            });
            $('.btn-close-popup').on('click',function () {
                $('#mask').hide();
                $('.window').hide();
            });
        }
        //End popup letter
    }

    /************** FUNCTION ****************/
    function tool_panel(){
        $('.dm-open').on('click',function(){
            $('#widget_indexdm').toggleClass('active');
            $('#indexdm_img').toggleClass('active');
            return false;
        })
        $('.dm-content .item-content').on('hover',function(){
            if(!$(this).hasClass('active')){
                $('.img-demo').removeClass('dm-scroll-img');
                setTimeout(function() {
                    $('.img-demo').addClass('dm-scroll-img');
                },20);
                $(this).parent().find('.item-content').removeClass('active');
                $(this).addClass('active');
            }
            $('#indexdm_img').addClass('active');
            var img_src = $(this).find('img').attr('data-src');
            $('.img-demo').css('display','block');
            $('.img-demo').css('background-image','url('+img_src+')');
        });
        $('.img-demo').mouseenter(function(){
            $(this).addClass('pause');
        }).mouseleave(function(){
            $(this).removeClass('pause');
        });
        var default_data = $('#s7upf-theme-style-inline-css').html();
        $('.dm-color').on('click',function(){
            $(this).parent().find('.dm-color').removeClass('active');
            $(this).addClass('active');
            var color,color2,rgb,rgb2;
            var data = $('.get-data-css').attr('data-css');
            var sep = new RegExp('##', 'gi');
            data = data.replace(sep,'"', -1);
            // Color 1
            var color_old = $('.get-data-css').attr('data-color');
            var rgb_old = $('.get-data-css').attr('data-rgb');
            var color_df = $('.get-data-css').attr('data-colordf');
            var rgb_df = $('.get-data-css').attr('data-rgbdf');
            if($(this).attr('data-color')) $('.get-data-css').attr('data-color',$(this).attr('data-color'));
            if($(this).attr('data-rgb')) $('.get-data-css').attr('data-rgb',$(this).attr('data-rgb'));
            color = $('.get-data-css').attr('data-color');
            rgb = $('.get-data-css').attr('data-rgb');

            // Color 2
            var color2_old = $('.get-data-css').attr('data-color2');
            var rgb2_old = $('.get-data-css').attr('data-rgb2');
            var color2_df = $('.get-data-css').attr('data-color2df');
            var rgb2_df = $('.get-data-css').attr('data-rgb2df');
            if($(this).attr('data-color2')) $('.get-data-css').attr('data-color2',$(this).attr('data-color2'));
            if($(this).attr('data-rgb2')) $('.get-data-css').attr('data-rgb2',$(this).attr('data-rgb2'));
            color2 = $('.get-data-css').attr('data-color2');
            rgb2 = $('.get-data-css').attr('data-rgb2');
            if(color && color2){
                // Color 1
                color_df = new RegExp(color_df, 'gi');
                rgb_df = new RegExp(rgb_df, 'gi');
                data = data.replace(color_df,color, -1);
                data = data.replace(rgb_df,rgb, -1);

                // Color 2
                color2_df = new RegExp(color2_df, 'gi');
                rgb2_df = new RegExp(rgb2_df, 'gi');
                data = data.replace(color2_df,color2, -1);
                data = data.replace(rgb2_df,rgb2, -1);

                if($('#s7upf-theme-style-inline-css').length > 0) $('#s7upf-theme-style-inline-css').html(data);
                else $('head').append('<'+'style id="s7upf-theme-style-inline-css">'+data+'</style>');
            }
            else $('#s7upf-theme-style-inline-css').html(default_data);
            return false;
        })
    }
    function auto_width_megamenu(){
        if($(window).width()>1170){
            var full_width = parseInt($('.container').innerWidth());
            if($('nav.main-nav').length > 0){
                var main_menu_width = parseInt($('nav.main-nav').innerWidth());
                var main_menu_left = parseInt($('nav.main-nav').offset().left);
                $('nav.main-nav > ul > li.has-mega-menu').each(function(){
                    if($(this).find('.mega-menu').length > 0){
                        var mega_menu_width = parseInt($(this).find('.mega-menu').innerWidth());
                        var li_width = parseInt($(this).innerWidth());
                        var seff = $(this);
                        if($('.rtl').length > 0){
                            setTimeout(function() {
                                main_menu_left = parseInt($(window).width() - (seff.parents('nav.main-nav').offset().left + seff.parents('nav.main-nav').outerWidth()));
                                var mega_menu_left = $(window).width() - (seff.find('.mega-menu').offset().left + seff.find('.mega-menu').outerWidth());
                                var li_left = $(window).width() - (seff.offset().left + seff.outerWidth());
                                var pos = li_left - mega_menu_left - mega_menu_width/2 + li_width/2;
                                var pos2 = pos + mega_menu_left + mega_menu_width - main_menu_left - main_menu_width;
                                if(pos2 > 0 ) pos = pos - pos2;
                                if(pos > 0 ) $(this).find('.mega-menu').css('right',pos);
                                else{
                                    pos  = $(window).width() - ($('.container').offset().left + $('.container').outerWidth()) - main_menu_left + (full_width - mega_menu_width)/2;
                                    seff.find('.mega-menu').css('right',pos);
                                }
                            }, 2000);
                        }
                        else{

                            var mega_menu_left = $(this).find('.mega-menu').offset().left;
                            var li_left = $(this).offset().left;
                            var pos = li_left - mega_menu_left - mega_menu_width/2 + li_width/2;
                            var pos2 = pos + mega_menu_left + mega_menu_width - main_menu_left - main_menu_width;
                            if(pos2 > 0 ) pos = pos - pos2;
                            if(pos > 0 ) $(this).find('.mega-menu').css('left',pos);
                            else{
                                pos  = $('.container').offset().left  - main_menu_left + (full_width - mega_menu_width)/2;
                                seff.find('.mega-menu').css('left',pos);
                            }
                        }
                    }
                })
            }
        }
    }
    //Detail Gallery
    function parallax_slider(){
        if($('.parallax-slider').length>0){
            var ot = $('.parallax-slider').offset().top;
            var sh = $('.parallax-slider').height();
            var st = $(window).scrollTop();
            var top = (($(window).scrollTop() - ot) * 0.5) + 'px';
            if(st>ot&&st<ot+sh){
                $('.parallax-slider .item-slider').css({
                    'background-position': 'center ' + top
                });
            }else{
                $('.parallax-slider .item-slider').css({
                    'background-position': 'center 0'
                });
            }
        }
    }
    function detail_gallery(){
        if($('.detail-gallery').length>0){
            $('.detail-gallery').each(function(){
                var data=$(this).find(".carousel").data();
                var seff = $(this);
                if($(this).find(".carousel").length>0){
                    $(this).find(".carousel").jCarouselLite({
                        btnNext: $(this).find(".gallery-control .next"),
                        btnPrev: $(this).find(".gallery-control .prev"),
                        speed: 800,
                        visible:data.visible,
                        vertical:data.vertical,
                    });
                }
                //Elevate Zoom
                $.removeData($('.detail-gallery .mid img'), 'elevateZoom');//remove zoom instance from image
                $('.zoomContainer').remove();
                if($(window).width()>=768){
                    $(this).find('.zoom-style1 .mid img').elevateZoom();
                    $(this).find('.zoom-style2 .mid img').elevateZoom({
                        scrollZoom : true
                    });
                    $(this).find('.zoom-style3 .mid img').elevateZoom({
                        zoomType: "lens",
                        lensShape: "square",
                        lensSize: 150,
                        borderSize:1,
                        containLensZoom:true,
                        responsive:true
                    });
                    $(this).find('.zoom-style4 .mid img').elevateZoom({
                        zoomType: "inner",
                        cursor: "crosshair",
                        zoomWindowFadeIn: 500,
                        zoomWindowFadeOut: 750
                    });
                }

                $(this).find(".carousel a").on('click',function(event) {
                    event.preventDefault();
                    $(this).parents('.detail-gallery').find(".carousel a").removeClass('active');
                    $(this).addClass('active');
                    var z_url =  $(this).find('img').attr("data-src");
                    var srcset =  $(this).find('img').attr("data-srcset");
                    $(this).parents('.detail-gallery').find(".mid img").attr("src", z_url);
                    $(this).parents('.detail-gallery').find(".mid img").attr("srcset", srcset);
                    $('.zoomWindow,.zoomLens').css('background-image','url("'+z_url+'")');
                    $.removeData($('.detail-gallery .mid img'), 'elevateZoom');//remove zoom instance from image
                    $('.zoomContainer').remove();
                    if($(window).width()>=768){
                        $(this).parents('.detail-gallery').find('.zoom-style1 .mid img').elevateZoom();
                        $(this).parents('.detail-gallery').find('.zoom-style2 .mid img').elevateZoom({
                            scrollZoom : true
                        });
                        $(this).parents('.detail-gallery').find('.zoom-style3 .mid img').elevateZoom({
                            zoomType: "lens",
                            lensShape: "square",
                            lensSize: 150,
                            borderSize:1,
                            containLensZoom:true,
                            responsive:true
                        });
                        $(this).parents('.detail-gallery').find('.zoom-style4 .mid img').elevateZoom({
                            zoomType: "inner",
                            cursor: "crosshair",
                            zoomWindowFadeIn: 500,
                            zoomWindowFadeOut: 750
                        });
                    }
                });
                $('input[name="variation_id"]').on('change',function(){
                    var z_url =  seff.find('.mid img').attr("src");
                    $('.zoomWindow,.zoomLens').css('background-image','url("'+z_url+'")');
                    $.removeData($('.detail-gallery .mid img'), 'elevateZoom');//remove zoom instance from image
                    $('.zoomContainer').remove();
                    $('.detail-gallery').find('.zoom-style1 .mid img').elevateZoom();
                    $('.detail-gallery').find('.zoom-style2 .mid img').elevateZoom({
                        scrollZoom : true
                    });
                    $('.detail-gallery').find('.zoom-style3 .mid img').elevateZoom({
                        zoomType: "lens",
                        lensShape: "square",
                        lensSize: 150,
                        borderSize:1,
                        containLensZoom:true,
                        responsive:true
                    });
                    $('.detail-gallery').find('.zoom-style4 .mid img').elevateZoom({
                        zoomType: "inner",
                        cursor: "crosshair",
                        zoomWindowFadeIn: 500,
                        zoomWindowFadeOut: 750
                    });
                })
                $('.image-lightbox').on('click',function(event){
                    event.preventDefault();
                    var gallerys = $(this).attr('data-gallery');
                    var gallerys_array = gallerys.split(',');
                    var data = [];
                    if(gallerys != ''){
                        for (var i = 0; i < gallerys_array.length; i++) {
                            if(gallerys_array[i] != ''){
                                data[i] = {};
                                data[i].href = gallerys_array[i];
                            }
                        };
                    }
                    $.fancybox.open(data);
                })
            });
        }
    }

    // Menu fixed
    function fixed_header(){
        var menu_element;
        menu_element = $('.main-nav:not(.menu-fixed-content)').closest('.vc_row');
        if($('.menu-sticky-on').length > 0 && $(window).width()>1024){
            var menu_class = $('.main-nav').attr('class');
            var header_height = $("#header").height()+100;
            var ht = header_height + 150;
            var st = $(window).scrollTop();

            if(!menu_element.hasClass('header-fixed') && menu_element.attr('data-vc-stretch-content') == 'true') menu_element.addClass('header-fixed');
            if(st>header_height){
                if(menu_element.attr('data-vc-stretch-content') == 'true'){
                    if(st > ht) menu_element.addClass('active');
                    else menu_element.removeClass('active');
                    menu_element.addClass('fixed-header');
                    $('body').addClass('menu-on-fixed');
                }
                else{
                    if(st > ht) menu_element.parent().parent().addClass('active');
                    else menu_element.parent().parent().removeClass('active');
                    if(!menu_element.parent().parent().hasClass('fixed-header')){
                        menu_element.wrap( "<div class='menu-fixed-content fixed-header "+menu_class+"'><div class='container'></div></div>" );
                    }
                    $('body').removeClass('menu-on-fixed');
                }
            }else{
                menu_element.removeClass('active');
                if(menu_element.attr('data-vc-stretch-content') == 'true') menu_element.removeClass('fixed-header');
                else{
                    if(menu_element.parent().parent().hasClass('fixed-header')){
                        menu_element.unwrap();
                        menu_element.unwrap();
                    }
                }
                $('body').removeClass('menu-on-fixed');
            }
        }
        else{
            menu_element.removeClass('active');
            if(menu_element.attr('data-vc-stretch-content') == 'true') menu_element.removeClass('fixed-header');
            else{
                if(menu_element.parent().parent().hasClass('fixed-header')){
                    menu_element.unwrap();
                    menu_element.unwrap();
                }
            }
        }
    }
    //Menu Responsive
    function fix_click_menu(){
        if($(window).width()<=1170){
            if($('.btn-toggle-mobile-menu').length>0){
                return false;
            }
            else $('.main-nav li.children,.main-nav li.has-mega-menu').append('<span class="btn-toggle-mobile-menu"></span>');
        }
        else{
            $('.btn-toggle-mobile-menu').remove();
            $('.main-nav .sub-menu,.main-nav .mega-menu').slideDown('fast');
        }
    }
    function rep_menu(){
        $('.toggle-mobile-menu').on('click',function(event){
            event.preventDefault();
            $(this).parents('nav.main-nav').toggleClass('active');
            $(this).prev().slideToggle('fast');
        });
        $('nav.main-nav').on('click','.btn-toggle-mobile-menu',function(event){
            $(this).toggleClass('active');
            $(this).prev().stop(true,false).slideToggle('fast');
        });
        $('nav.main-nav').on('click','.menu-item > a[href="#"]',function(event){
            event.preventDefault();
            $(this).toggleClass('active');
            $(this).next().stop(true,false).slideToggle('fast');
        });
    }

    function background(){
        $('.bg-slider .item-slider').each(function(){
            $(this).find('.banner-thumb a img').css('height',$(this).find('.banner-thumb a img').attr('height'));
            var src=$(this).find('.banner-thumb a img').attr('src');
            $(this).css('background-image','url("'+src+'")');
        });
    }

    function fix_variable_product(){
        //Fix product variable thumb
        $('body .variations_form select').live('change',function(){
            var id = $('input[name="variation_id"]').val();
            if(id){
                $('.product-gallery #bx-pager').find('a[data-variation_id="'+id+'"]').trigger( 'click' );
            }
        })
        // variable product
        if($('.wrap-attr-product1.special').length > 0){
            $('.attr-filter ul li a').live('click',function(){
                event.preventDefault();
                $(this).parents('ul').find('li').removeClass('active');
                $(this).parent().addClass('active');
                var attribute = $(this).parent().attr('data-attribute');
                var id = $(this).parents('ul').attr('data-attribute-id');
                $('#'+id).val(attribute);
                $('#'+id).trigger( 'change' );
                $('#'+id).trigger( 'focusin' );
                return false;
            })
            $('.attr-hover-box').on('hover',function(){
                var seff = $(this);
                var old_html = $(this).find('ul').html();
                var current_val = $(this).find('ul li.active').attr('data-attribute');
                $(this).next().find('select').trigger( 'focusin' );
                var content = '';
                $(this).next().find('select').find('option').each(function(){
                    var val = $(this).attr('value');
                    var title = $(this).html();
                    var el_class = '';
                    if(current_val == val) el_class = ' class="active"';
                    if(val != ''){
                        content += '<li'+el_class+' data-attribute="'+val+'"><a href="#" class="bgcolor-'+val+'"><span></span>'+title+'</a></li>';
                    }
                })
                if(old_html != content) $(this).find('ul').html(content);
            })
            $('body .reset_variations').live('click',function(){
                $('.attr-hover-box').each(function(){
                    var seff = $(this);
                    var old_html = $(this).find('ul').html();
                    var current_val = $(this).find('ul li.active').attr('data-attribute');
                    $(this).next().find('select').trigger( 'focusin' );
                    var content = '';
                    $(this).next().find('select').find('option').each(function(){
                        var val = $(this).attr('value');
                        var title = $(this).html();
                        var el_class = '';
                        if(current_val == val) el_class = ' class="active"';
                        if(val != ''){
                            content += '<li'+el_class+' data-attribute="'+val+'"><a href="#" class="bgcolor-'+val+'"><span></span>'+title+'</a></li>';
                        }
                    })
                    if(old_html != content) $(this).find('ul').html(content);
                    $(this).find('ul li').removeClass('active');
                })
            })
        }
        //end
    }

    function afterAction(){
        this.$elem.find('.owl-item').each(function(){
            var check = $(this).hasClass('active');
            if(check==true){
                $(this).find('.animated').each(function(){
                    var anime = $(this).attr('data-animated');
                    $(this).addClass(anime);
                });
            }else{
                $(this).find('.animated').each(function(){
                    var anime = $(this).attr('data-animated');
                    $(this).removeClass(anime);
                });
            }
        });
        var owl = this;
        var visible = this.owl.visibleItems;
        var first_item = visible[0];
        var last_item = visible[visible.length-1];
        this.$elem.find('.owl-item').removeClass('first-item');
        this.$elem.find('.owl-item').removeClass('last-item');
        this.$elem.find('.owl-item').eq(first_item).addClass('first-item');
        this.$elem.find('.owl-item').eq(last_item).addClass('last-item');
    }
    function s7upf_qty_click(){
        //QUANTITY CLICK
        $("body").on("click",".detail-qty .qty-up",function(){
            var min = $(this).prev().attr("min");
            var max = $(this).prev().attr("max");
            var step = $(this).prev().attr("step");
            if(step === undefined) step = 1;
            if(max !==undefined && Number($(this).prev().val())< Number(max) || max === undefined || max === ''){
                if(step!='') $(this).prev().val(Number($(this).prev().val())+Number(step));
            }
            $( 'div.woocommerce form .button[name="update_cart"]' ).prop( 'disabled', false );
            return false;
        })
        $("body").on("click",".detail-qty .qty-down",function(){
            var min = $(this).next().attr("min");
            var max = $(this).next().attr("max");
            var step = $(this).next().attr("step");
            if(step === undefined) step = 1;
            if(Number($(this).next().val()) > Number(min)){
                if(min !==undefined && $(this).next().val()>min || min === undefined || min === ''){
                    if(step!='') $(this).next().val(Number($(this).next().val())-Number(step));
                }
            }
            $( 'div.woocommerce form .button[name="update_cart"]' ).prop( 'disabled', false );
            return false;
        })
        $("body").on("keyup change","input.qty-val",function(){
            $( 'div.woocommerce form .button[name="update_cart"]' ).prop( 'disabled', false );
        })
        //END
    }

    function s7upf_owl_slider(){
        //Carousel Slider
        if($('.sv-slider').length>0){
            $('.sv-slider').each(function(){
                var seff = $(this);
                var item = seff.attr('data-item');
                var speed = seff.attr('data-speed');
                var itemres = seff.attr('data-itemres');
                var animation = seff.attr('data-animation');
                var nav = seff.attr('data-navigation');
                var pag = seff.attr('data-pagination');
                var text_prev = seff.attr('data-prev');
                var text_next = seff.attr('data-next');
                var pagination = false, navigation= false, singleItem = false;
                var autoplay;
                if(speed != '') autoplay = speed;
                else autoplay = false;
                // Navigation
                if(nav) navigation = true;
                if(pag) pagination = true;
                if(animation != ''){
                    singleItem = true;
                    item = '1';
                }
                else animation = false;
                var prev_text = '<i class="fa fa-angle-left" aria-hidden="true"></i>';
                var next_text = '<i class="fa fa-angle-right" aria-hidden="true"></i>';
                if(text_prev) prev_text = text_prev;
                if(text_next) next_text = text_next;
                if(itemres == '' || itemres === undefined){
                    if(item == '1') itemres = '0:1,480:1,768:1,1200:1';
                    if(item == '2') itemres = '0:1,480:1,768:2,1200:2';
                    if(item == '3') itemres = '0:1,480:2,768:2,992:3';
                    if(item == '4') itemres = '0:1,480:2,840:3,1200:4';
                    if(item >= '5') itemres = '0:1,480:2,768:3,1024:4,1200:'+item;
                }
                itemres = itemres.split(',');
                var i;
                for (i = 0; i < itemres.length; i++) {
                    itemres[i] =  $.map(itemres[i].split(':'), function(value){
                        return parseInt(value, 10);
                    });
                }
                seff.owlCarousel({
                    items: item,
                    itemsCustom: itemres,
                    autoPlay:autoplay,
                    pagination: pagination,
                    navigation: navigation,
                    navigationText:[prev_text,next_text],
                    singleItem : singleItem,
                    beforeInit:background,
                    addClassActive : true,
                    autoplayHoverPause: true,
                    afterAction: afterAction,
                    touchDrag: true,
                    transitionStyle : animation
                });
            });
        }
    }

    function s7upf_all_slider(seff,number){
        if(!seff) seff = $('.smart-slider');
        if(!number) number = '';
        //Carousel Slider
        if(seff.length>0){
            seff.each(function(){
                var seff = $(this);
                var item = seff.attr('data-item'+number);
                var speed = seff.attr('data-speed');
                var itemres = seff.attr('data-itemres'+number);
                var text_prev = seff.attr('data-prev');
                var text_next = seff.attr('data-next');
                var nav = seff.attr('data-navigation');
                var pag = seff.attr('data-pagination');
                var animation = seff.attr('data-animation');
                var autoplay;
                var pagination = false, navigation= false, singleItem = false;
                if(animation != '' && animation !== undefined){
                    singleItem = true;
                    item = '1';
                }
                else animation = false;
                if(speed === undefined) speed = '';
                if(speed != '') autoplay = speed;
                else autoplay = false;
                if(item == '' || item === undefined) item = 1;
                if(itemres === undefined) itemres = '';
                var prev_text = '<i class="fa fa-angle-left" aria-hidden="true"></i>';
                var next_text = '<i class="fa fa-angle-right" aria-hidden="true"></i>';
                if(text_prev) prev_text = text_prev;
                if(text_next) next_text = text_next;
                if(nav) navigation = true;
                if(pag) pagination = true;
                // Item responsive
                if(itemres == '' || itemres === undefined){
                    if(item == '1') itemres = '0:1,480:1,768:1,1200:1';
                    if(item == '2') itemres = '0:1,480:1,768:2,1200:2';
                    if(item == '3') itemres = '0:1,480:2,768:2,992:3';
                    if(item == '4') itemres = '0:1,480:2,840:3,1200:4';
                    if(item >= '5') itemres = '0:1,480:2,768:3,1200:'+item;
                }
                itemres = itemres.split(',');
                var i;
                for (i = 0; i < itemres.length; i++) {
                    itemres[i] =  $.map(itemres[i].split(':'), function(value){
                        return parseInt(value, 10);
                    });
                }
                seff.owlCarousel({
                    items: item,
                    itemsCustom: itemres,
                    autoPlay:autoplay,
                    pagination: pagination,
                    navigation: navigation,
                    navigationText:[prev_text,next_text],
                    addClassActive : true,
                    touchDrag: true,
                    singleItem : singleItem,
                    transitionStyle : animation,
                    afterAction: afterAction,
                    stopOnHover: true,
                });
            });
        }
    }

    /* Search icon click */

    function s7upf_search_icon(){
        $('.s-icon').on("click",function(event){
            event.preventDefault();
            $('.search-form-wrap,.close-searchform').addClass('active');
            $('.header-page .icon .search-form input[type="text"]').toggleClass('active');
            s7up_fixed_zindex();

        });
        $('.close-searchform').on("click",function(event){
            event.preventDefault();
            $('.search-form-wrap').removeClass('active');
            s7up_remove_fixed_zindex();
        });
    }
    function s7upf_contdown(){
        $(".CountDownTimer").each(function () {
            $(this).TimeCircles();
        })
    }
    function s7upf_toggleclass() {
        $(".hidden-sidebar >  a").on("click",function (event) {
            event.preventDefault();
            /* show hidden sidebar */
            $('body').addClass('overlay');
            $('.hidden-sidebar-content').addClass('active');
        });

        $('.close-hidden-sidebar').on("click",function (event) {
            event.preventDefault();
            /* remove class active hidden sidebar */
            $('body').removeClass('overlay');
            $('.hidden-sidebar-content').removeClass('active');
        });

    }
    function s7upf_play_video(){

        var video = $('.banner-video').find('video').get(0);
        if(video != undefined){

            $('.banner-video .video-button').on('click',function (e) {
                e.preventDefault();
                if(video.paused){
                    video.play();
                    $('.banner-video').addClass('clicked');
                }
                else{
                    video.pause();
                    $('.banner-video').removeClass('clicked');
                }
            });
        }
    }

    function s7up_progess_bar(){
        $('.s7upf-progess-bar').each(function(){
            var lstbar = $(this).find('.single-bar');
            var lstdata = [];
            lstbar.each(function(element) {
                lstdata.push($(this).attr('data-value'));
            });
            var i = 0;
            var element_position = $('.s7upf-progess-bar').offset().top-400;
            console.log(element_position);
            $(window).on('scroll', function() {
                var y_scroll_pos = window.pageYOffset;
                var scroll_pos_e = element_position;
                if(y_scroll_pos > scroll_pos_e) {
                    lstbar.each(function(element) {
                        $(this).css('width',lstdata[i]+'%');
                        i++;
                    });
                }
            });
        });
    }
    /* toggle accordition tab */
    function accordition_tab(){

        if($('.toggle-tab').length>0){
            $('.toggle-tab').each(function(){
                $(this).find('.item-toggle-tab.active .toggle-tab-content').slideDown();
                $(this).find('.toggle-tab-title').on('click',function(event){
                    if($(this).next().length>0){
                        event.preventDefault();
                        var self = $(this);
                        self.parent().siblings().removeClass('active');
                        self.parent().toggleClass('active');
                        self.parents('.toggle-tab').find('.toggle-tab-content').slideUp();
                        self.next().stop(true,false).slideToggle();
                    }
                });
            });
        }
    }
    function s7up_fixed_zindex(){
        /* fix z-index home 4-5 */
        $('.header4-row2').css('z-index','1004');
        $('.header5-row2').css('z-index','1004');
    }
    function s7up_remove_fixed_zindex(){
        $('.header4-row2').css('z-index','2');
        $('.header5-row2').css('z-index','1');
    }
    function s7upf_responsive(){
        /* Remove inntertext deparment header home 1 */

        if($(window).width() <= 1024){
            if(('.hd5-department > a.title14').length >0){
                $('.hd5-department > a.title14').text("");
            }
        }
        else{
            if(('.hd5-department > a.title14').length >0){
                $('.hd5-department > a.title14').text("SHOP DEPARTMENTS");
            }
        }

        /* Response header home 1 */

        if($(window).width() <= 767){
            if($('.hd5-row-3 .hd5-group-link').length > 0){
                return false;
            }
            else {
                $('.hd5-row-3').append($('.hd5-group-link'));
            }
        }
        if($(window).width() > 767){
            if($('.hd5-row-3 .hd5-group-link').length > 0){
                var _this = $('.hd5-row-3 .hd5-group-link');
                $('.hd5-row-2').append($('.hd5-group-link'));
            }
        }

        /* add search icon to home 2 */
        if($(window).width() < 1024){
            if($('.home2-s-icon').length > 0 ){
                return false;
            }else{
                $('.header-2-menu .col-search-cart > .vc_column-inner > .wpb_wrapper').append('<a href="#" class="home2-s-icon"><i class="fa fa-search"></i></a>');
            }
            $('.home2-s-icon').on('click',function (event) {
                event.preventDefault();
                $('.header-2-menu .col-sm-3 .block-search-element').toggleClass('active');
            })
        }
        if($(window).width() >= 1024){
            if($(".home2-s-icon").length > 0){
                var _this = $(".home2-s-icon");
                _this.remove();
            }
        }

        /* click to icon header home 2 */

        if($(window).width()< 1200){

            $('.hm2-topbar-des:last-child .icon-wrap').on('click',function (event) {
                event.preventDefault();
                $(this).parents().find('.hm2-topbar-des:last-child .content-wrap').toggleClass('active');
            })
        }

        /* Add search icon to topbar home 5 */
        if($(window).width() < 1024){
            if($('.home5-s-icon').length > 0 ){
                return false;
            }else {
                $('.header5-row2 .hd5-r2-col3 .vc_column-inner .wpb_wrapper').append('<a href="#" class="home5-s-icon"><i class="fa fa-search"></i></a>');
            }
            $('.home5-s-icon').on('click',function (event) {
                event.preventDefault();
                $('.header5-row2 .hd5-r2-col3 .block-search-element').toggleClass('active');
            })
        }

        if($(window).width()>=1024){
            if($(".home5-s-icon").length > 0 ){
                var _this = $(".home5-s-icon");
                _this.remove();
            }
        }

        /* add logo to topbar home 4 */

        if($(window).width() <= 991){
            if($('.header4-row1 .menu-col-1').length > 0){
                return false;
            }else{
                $('.header4-row1').append($('.header4-row2 .menu-col-1'));
            }
        }

        if($(window).width() > 991){
            if($('.header4-row1 .menu-col-1').length > 0){
                var _this = $('.header4-row1 .menu-col-1');
                $('.header4-row2').prepend($('.menu-col-1'));
            }
        }
    }

    /************ END FUNCTION **************/
    $(document).ready(function(){
        //Menu Responsive
        //init check scroll
        fancybox_image();
        letter_popup();
        parallax_slider();
        fix_click_menu();
        rep_menu();
        s7upf_qty_click();
        detail_gallery();
        tool_panel();
        s7upf_search_icon();
        s7upf_contdown();
        s7upf_play_video();
        s7up_progess_bar();
        s7upf_responsive();
        s7upf_toggleclass();
        accordition_tab();

        // Filter click
        $('.btn-filter').on('click',function(){
            $(this).parents('.filter-product').toggleClass('active');
            return false;
        })
        //Filter Price
        if($('.range-filter').length>0){
            $('.range-filter').each(function(){
                var self = $(this);
                var min_price = Number(self.find('.slider-range').attr( 'data-min' ));
                var max_price = Number(self.find('.slider-range').attr( 'data-max' ));
                self.find( ".slider-range" ).slider({
                    range: true,
                    min: min_price,
                    max: max_price,
                    values: [ min_price, max_price ],
                    slide: function( event, ui ) {
                        self.find( '.element-get-min' ).html(ui.values[ 0 ]);
                        self.find( '.element-get-max' ).html(ui.values[ 1 ]);
                    }
                });
            });
        }
        //fix row bg
        $('.fix-row-bg').each(function(){
            var row_class = $(this).attr('class');
            row_class = row_class.replace('vc_row wpb_row','');
            $(this).removeClass(row_class);
            $(this).removeClass('fix-row-bg');
            $(this).wrap('<div class="wrap-vc-row'+row_class+'"></div>');
        })
        //Cat search
        $('.select-cat-search').on('click',function(event){
            event.preventDefault();
            $(this).parents('ul').find('li').removeClass('active');
            $(this).parent().addClass('active');
            var x = $(this).attr('data-filter');
            if(x){
                x = x.replace('.','');
                $('.cat-value').val(x);
            }
            else $('.cat-value').val('');
            $('.current-search-cat').text($(this).text());
        });
        // aside-box cart
        $('.close-minicart').on('click',function(event){
            $('body').removeClass('overlay');
            $('.mini-cart-content').removeClass('active');
            s7up_remove_fixed_zindex();
        });
        $('.mini-cart-box.aside-box .mini-cart-link').on('click',function(event){
            event.preventDefault();
            event.stopPropagation();
            $('body').addClass('overlay');
            $(this).next().addClass('active');
            s7up_fixed_zindex();
        });
        // icon cart
        $('.mini-cart-box.aside-box .minicart-icon').on('click',function(event){
            event.preventDefault();
            event.stopPropagation();
            $('body').addClass('overlay');
            $(this).next().addClass('active');
            s7up_fixed_zindex();
        });
        //Count item cart
        if($(".get-cart-number").length){
            var count_cart_item = $(".get-cart-number").val();
            $(".set-cart-number").html(count_cart_item);
        }
        //Fix mailchimp
        $('.sv-mailchimp-form').each(function(){
            var placeholder = $(this).attr('data-placeholder');
            var submit = $(this).attr('data-submit');
            if(placeholder) $(this).find('input[name="EMAIL"]').attr('placeholder',placeholder);
            if(submit) $(this).find('input[type="submit"]').val(submit);
        })
        //Back To Top
        $('.scroll-top').on('click',function(event){
            event.preventDefault();
            $('html, body').animate({scrollTop:0}, 'slow');
        });


    });


    $(window).load(function(){
        s7upf_owl_slider();
        s7upf_all_slider();
        auto_width_megamenu();
        fix_css_append();
        login_popup();
//Filter Price
        if($('.range-filter').length>0){
            $('.range-filter').each(function(){
                $(this).find( ".slider-range" ).slider({
                    range: true,
                    min: 0,
                    max: 8000,
                    values: [ 0, 6000 ],
                    slide: function( event, ui ) {
                        $(this).parents('.range-filter').find( ".amount" ).html('<span class="min-price">$'+ui.values[ 0 ]+'</span>' + ' - ' + '<span class="max-price">$' + ui.values[ 1 ]+'</span>');
                    }
                });
                $(this).find( ".amount" ).html('<span class="min-price">$' +$(this).find( ".slider-range" ).slider( "values", 0 )+'</span>' + ' - ' + '<span class="max-price">$' +$(this).find( ".slider-range" ).slider( "values", 1 )+'</span>');
            });
        }
        //List Item Masonry
        if($('.blog-grid-view.list-masonry .list-post-wrap').length>0){
            var $content = $('.blog-grid-view.list-masonry .list-post-wrap');
            $content.imagesLoaded( function() {
                $content.masonry();
            });
        }
        if($('.product-grid-view.list-masonry .list-product-wrap').length>0){
            var $content2 = $('.product-grid-view.list-masonry .list-product-wrap');
            $content2.imagesLoaded( function() {
                $content.masonry();
            });
        }


        //Pre Load
        $('body').removeClass('preload');
        // Fix height slider
        $('.banner-slider .banner-info').each(function(){
            if($(this).find('.slider-content-text').length > 0){
                var height_content = $(this).find('.slider-content-text')["0"].clientHeight;
                if($(this).find('.countdown-wrap').length > 0){
                    var height_countdown = $(this).find('.countdown-wrap')["0"].clientHeight;
                    var countdown_margin_top = parseInt($(this).find('.countdown-wrap').css("margin-top"));
                    height_content = height_content + height_countdown + countdown_margin_top;
                }
                $(this).css('height',height_content);
            }
        })
        // menu fixed onload
        $("#header").css('min-height','');
        if($(window).width()>1170){
            $("#header").css('min-height',$("#header").height());
            fixed_header();
        }
        else{
            $("#header").css('min-height','');
        }
        //menu fix
        if($(window).width() >= 768){
            var c_width = $(window).width();
            $('.main-nav ul ul ul.sub-menu').each(function(){
                var left = $(this).offset().left;
                if(c_width - left < 180){
                    $(this).css({"left": "-100%"})
                }
                if(left < 180){
                    $(this).css({"left": "100%"})
                }
            })
        }
        //Menu mobi set left
        if($(window).width()<=1170 && $('nav.main-nav').length>0){
            var check_drive =15;
            if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                check_drive=0;
            }
            var left =($(window).width() - ( $('nav.main-nav').offset().left +  $('nav.main-nav').outerWidth()));
            $('nav.main-nav .menu-main-menu').css({'right':-(left+check_drive)});

        }else  $('nav.main-nav .menu-main-menu').css({'right':''});
    });// End load

    /* ---------------------------------------------
     Scripts resize
     --------------------------------------------- */
    var w_width = $(window).width();
    $(window).resize(function(){
        auto_width_megamenu();
        fix_click_menu();

        s7upf_responsive();

        if($('#dialog').length > 0){
            // popup resize
            var id = '#dialog';
            //Get the screen height and width
            var maskHeight = $(document).height();
            var maskWidth = $(window).width();

            //Set heigth and width to mask to fill up the whole screen
            $('#mask').css({'width':maskWidth,'height':maskHeight});

            //Get the window height and width
            var winH = $(window).height();
            var winW = $(window).width();

            //Set the popup window to center
            $(id).css('top',  winH/2-$(id).height()/2);
            $(id).css('left', winW/2-$(id).width()/2);
        }
        $("#header").css('min-height','');
        //Menu mobi set left
        if($(window).width()<=1170 && $('nav.main-nav').length>0){
            var check_drive =15;
            if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                check_drive=0;
            }
            setTimeout(function() {
                var left =($(window).width() - ( $('nav.main-nav').offset().left +  $('nav.main-nav').outerWidth()));
                $('nav.main-nav .menu-main-menu').css({'right':-(left+check_drive)});
            },300)
        }else  $('nav.main-nav .menu-main-menu').css({'right':''});
        //End menu mobi set left
    });

    jQuery(window).scroll(function(){
        fixed_header();
        parallax_slider();
        if($(window).width()>1170){
            $("#header").css('min-height',$("#header").height());
            fixed_header();
        }
        else{
            $("#header").css('min-height','');
        }
        //Scroll Top
        if($(this).scrollTop()>$(this).height()){
            $('.scroll-top').addClass('active');
        }else{
            $('.scroll-top').removeClass('active');
        }
        //Menu mobi set left
        if($(window).width()<=1170 && $('nav.main-nav').length>0){
            var check_drive =15;
            if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                check_drive=0;
            }
            var left =($(window).width() - ( $('nav.main-nav').offset().left +  $('nav.main-nav').outerWidth()));
            $('nav.main-nav .menu-main-menu').css({'right':-(left+check_drive)});

        }else  $('nav.main-nav .menu-main-menu').css({'right':''});
        //End menu mobi set left
    });// End Scroll

    $.fn.tawcvs_variation_swatches_form = function () {
        return this.each( function() {
            var $form = $( this ),
                clicked = null,
                selected = [];

            $form
                .addClass( 'swatches-support' )
                .on( 'click', '.swatch', function ( e ) {
                    e.preventDefault();
                    var $el = $( this ),
                        $select = $el.closest( '.value' ).find( 'select' ),
                        attribute_name = $select.data( 'attribute_name' ) || $select.attr( 'name' ),
                        value = $el.data( 'value' );

                    $select.trigger( 'focusin' );

                    // Check if this combination is available
                    if ( ! $select.find( 'option[value="' + value + '"]' ).length ) {
                        $el.siblings( '.swatch' ).removeClass( 'selected' );
                        $select.val( '' ).change();
                        $form.trigger( 'tawcvs_no_matching_variations', [$el] );
                        return;
                    }

                    clicked = attribute_name;

                    if ( selected.indexOf( attribute_name ) === -1 ) {
                        selected.push(attribute_name);
                    }

                    if ( $el.hasClass( 'selected' ) ) {
                        $select.val( '' );
                        $el.removeClass( 'selected' );

                        delete selected[selected.indexOf(attribute_name)];
                    } else {
                        $el.addClass( 'selected' ).siblings( '.selected' ).removeClass( 'selected' );
                        $select.val( value );
                    }

                    $select.change();
                } )
                .on( 'click', '.reset_variations', function () {
                    $( this ).closest( '.variations_form' ).find( '.swatch.selected' ).removeClass( 'selected' );
                    selected = [];
                } )
                .on( 'tawcvs_no_matching_variations', function() {
                    window.alert( wc_add_to_cart_variation_params.i18n_no_matching_variations_text );
                } );
        } );
    };

    $( function () {
        $( '.variations_form' ).tawcvs_variation_swatches_form();
        $( document.body ).trigger( 'tawcvs_initialized' );
    } );

})(jQuery);