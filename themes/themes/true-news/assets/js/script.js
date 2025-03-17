function true_news_tooltip( HoverContent ){
    jQuery(document).ready( function($) {
    "use strict";
        var tooltipObj = {
            init: function(){
                this.events();
            },
            events: function(){
                var _this = this;
                $( HoverContent ).on('mouseenter',function(i){
         
                    var $el = $(this),
                        text = $el.data("ui-tooltip");
                    _this.mouseenterEvent(i, text, $el);
                });
         
                $( HoverContent ).on('mouseleave click',function(i){
                    _this.mouseleaveEvent(i);
                });
            },
            mouseenterEvent: function(i, text, $el){
         
                if(typeof text != 'undefined'){
         
                    var tt      = $('.tooltipOuter').clone().addClass("temp"),
                        ttCon   = $('.tooltipInner').clone(),
                        offset  = $el.offset();
         
                    tt.empty()
                        .append(ttCon.html(text))
                        .appendTo("body");
         
                    //Calculate after append
                    var bWidth  = tt.width() > $el.width() ? tt.width() :  $el.width(),
                        lWidth  = tt.width() < $el.width() ? tt.width() :  $el.width(),
                        dWidth  = bWidth - lWidth,
                        topVal  = (offset.top - tt.height()) - 8,
                        leftVal = (offset.left - (dWidth / 2));
         
                    tt.css({
                        top:topVal,
                        left:leftVal
                    }).fadeIn("fast");
                }
            },
            mouseleaveEvent: function(i){
                $('.tooltipOuter.temp').remove();
            }
        };
        tooltipObj.init();
    });
}
true_news_tooltip('[data-ui-tooltip]');
(function (e) {
    "use strict";
    var n = window.TWP_JS || {};
    var b = 1170;
    n.true_news_MobileMenu = {
        init: function () {
            this.menuMobile();
            this.menufocus();
            this.submenutoggle();
            this.navMobilearrow();
        },
        menuMobile: function () {
            e('#toggle-target, .offcanvas-close').click(function () {
                e('body').toggleClass('offcanvas-menu-open');
                e('body').toggleClass('body-scroll-locked');
                e('#offcanvas-menu').toggleClass('mobile-menu-active');
            });

            e('#toggle-target').click(function () {
                setTimeout(function () {
                    e('.offcanvas-close').focus();
                }, 300);
                
            });

            e('.offcanvas-close').click(function () {
                
                setTimeout(function () {
                    e('#toggle-target').focus();
                }, 300);
            });

            e(document).keyup(function(j) {
                if (j.key === "Escape") { // escape key maps to keycode `27`
                    e('body').removeClass('offcanvas-menu-open');
                    e('body').removeClass('body-scroll-locked');
                    e('#offcanvas-menu').removeClass('mobile-menu-active');
                }
            });
        },
        menufocus: function () {
            
             e( 'input, a, button' ).on( 'focus', function() {
                if ( e( '#offcanvas-menu' ).hasClass( 'mobile-menu-active' ) ) {

                    if ( ! e( this ).parents( '.offcanvas-social' ).length && ! e( this ).parents( '.offcanvas-navigation' ).length && ! e( this ).parents( '.close-offcanvas-menu' ).length ) {
                        e('.offcanvas-close').focus();
                    }
                }
            } );

        },
        submenutoggle: function () {

             e('#offcanvas-menu .offcanvas-navigation').on('click', 'li .submenu-toggle', function (event) {
                event.preventDefault();
                var ethis = e(this),
                    eparent = ethis.closest('li'),
                    esub_menu = eparent.find('> .sub-menu');
                if (esub_menu.css('display') == 'none') {
                    esub_menu.slideDown('300');
                    ethis.addClass('active');
                } else {
                    esub_menu.slideUp('300');
                    ethis.removeClass('active');
                }
                return false;
            });

        },
        navMobilearrow: function () {
            if (e(window).width() > b) {
                var c = e(".header-affix").height();
                e(window).on("scroll", {
                    previousTop: 0
                }, function () {
                    var b = e(window).scrollTop();
                    b < this.previousTop ? b > 0 && e(".header-affix").hasClass("is-fixed") ? e(".header-affix").addClass("is-visible") : e(".header-affix").removeClass("is-visible is-fixed") : (e(".header-affix").removeClass("is-visible"), b > c && !e(".header-affix").hasClass("is-fixed") && e(".header-affix").addClass("is-fixed")), this.previousTop = b;

                    e(window).scroll(function(){
                        if( e('#masthead').hasClass('is-fixed') ){
                            e('.current-news-title').addClass('twp-read-slidein');
                        }else{
                            e('.current-news-title').removeClass('twp-read-slidein');
                        }
                    });
                });
            }
        }
    };
    n.true_news_Preloader = function () {
        e(".hover").mouseleave(
            function() {
                e(this).removeClass("hover");
            }
        );
        e(window).load(function () {
            e("body").addClass("page-loaded");
        });
        e('.twp-preloader-skip').on("click", function () {
            e('body').addClass('page-loaded');
            var time = 1000 * 60 * 60 * 24;
            var expires = new Date((new Date()).valueOf() + time);
            document.cookie = "twppreloader=true;expires=" + expires.toUTCString();
        });
    };
    n.true_news_Carousal = function () {
        e(".content-slider").each(function () {
            e(this).slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplaySpeed: 8000,
                infinite: true,
                prevArrow: e('.slide-prev-1'),
                nextArrow: e('.slide-next-1'),
            });
        });
        e(".twp-main-slider").each(function () {
            e('.twp-main-slider').on('init', function(event, slick){
                e('.slide-animated').addClass('slide-activate slide-fadeInUp');
            });
            e(this).slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplaySpeed: 8000,
                infinite: true,
                nextArrow: '<i class="slide-icon slide-next ion ion-ios-arrow-forward"></i>',
                prevArrow: '<i class="slide-icon slide-prev ion ion-ios-arrow-back"></i>',
            });
            e('.twp-main-slider').on('afterChange', function(event, slick, currentSlide) {
                e('.slide-animated').removeClass('off');
                e('.slide-animated').addClass('slide-activate slide-fadeInUp');
            });
            e('.twp-main-slider').on('beforeChange', function(event, slick, currentSlide) {
                e('.slide-animated').removeClass('slide-activate slide-fadeInUp');
                e('.slide-animated').addClass('off');
            });
        });
        e(".twp-slick-slider").each(function () {
            e(this).slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplaySpeed: 8000,
                infinite: true,
                nextArrow: '<i class="slide-icon slide-next ion ion-ios-arrow-forward"></i>',
                prevArrow: '<i class="slide-icon slide-prev ion ion-ios-arrow-back"></i>',
            });
        });
        e(".carousel-view").each(function () {
            e(this).slick({
                autoplay: true,
                slidesToShow: 5,
                slidesToScroll: 1,
                autoplaySpeed: 8000,
                infinite: true,
                prevArrow: e('.slide-prev-3'),
                nextArrow: e('.slide-next-3'),
                responsive: [
                    {
                        breakpoint: 1199,
                        settings: {
                            slidesToShow: 4
                        }
                    },
                    {
                        breakpoint: 991,
                        settings: {
                            slidesToShow: 3
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 2
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1
                        }
                    }
                ]
            });
        });
        e(".verticle-slider").each(function () {
            e(this).slick({
                autoplay: true,
                vertical: true,
                slidesToShow: 5,
                slidesToScroll: 1,
                verticalSwiping: true,
                autoplaySpeed: 10000,
                infinite: true,
                prevArrow: e('.slide-prev-2'),
                nextArrow: e('.slide-next-2'),
                responsive: [
                    {
                        breakpoint: 991,
                        settings: {
                            slidesToShow: 4
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 3
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 3
                        }
                    }
                ]
            });
        });
        e(".drawer-carousel").each(function () {
            e(this).slick({
                autoplay: true,
                accessibility: true,
                slidesToShow: 4,
                slidesToScroll: 1,
                autoplaySpeed: 8000,
                infinite: true,
                nextArrow: '<i class="slide-icon slide-icon-box slide-next ion-ios-arrow-round-forward"></i>',
                prevArrow: '<i class="slide-icon slide-icon-box slide-prev ion-ios-arrow-round-back"></i>',
                responsive: [
                    {
                        breakpoint: 991,
                        settings: {
                            slidesToShow: 3
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 2
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1
                        }
                    }
                ]
            });
        });
        e(".twp-marquee").slick({
            speed: 9000,
            autoplay: true,
            autoplaySpeed: 0,
            cssEase: 'linear',
            focusOnSelect: true,
            pauseOnHover:true,
            infinite: true,
            slidesToShow: 1,
            slidesToScroll: 1,
            variableWidth: true,
            arrows : false,
            dots: false,
        });
        e("ul.wp-block-gallery.columns-1, .wp-block-gallery.columns-1 .blocks-gallery-grid, .gallery-columns-1").each(function () {
            e(this).slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                fade: true,
                autoplay: false,
                autoplaySpeed: 8000,
                infinite: true,
                nextArrow: '<i class="slide-icon slide-next ion-ios-arrow-round-forward"></i>',
                prevArrow: '<i class="slide-icon slide-prev ion-ios-arrow-round-back"></i>',
                dots: false
            });
        });
    };
    n.true_news_Reveal = function () {
        e('#icon-search').on('click', function (event) {
            e('html').attr('style','overflow-y: scroll; position: fixed; width: 100%; left: 0px; top: 0px;');
            e('body').toggleClass('reveal-search');
            e('body').addClass('body-scroll-locked');

            setTimeout(function () { 
                e('.popup-search .search-field').focus();
            }, 300);
            
        });
        e('#search-closer').on('click', function (event) {
            e('html').attr('style','');
            e('body').removeClass('reveal-search');
            e('body').removeClass('body-scroll-locked');
            e('#icon-search').focus();
        });
        e(document).keyup(function(j) {

            if (j.key === "Escape") { // escape key maps to keycode `27`

                if ( e( 'body' ).hasClass( 'reveal-search' ) ) {
                        
                    e('body').removeClass('reveal-search');
                    e('body').removeClass('body-scroll-locked');
                    e('html').attr('style','');

                    setTimeout(function () { 
                        e('#icon-search').focus();
                    }, 300);

                }
            }
        });

        e( 'input, a, button' ).on( 'focus', function() {
            if ( e( 'body' ).hasClass( 'reveal-search' ) ) {

                if ( ! e( this ).parents( '.popup-search' ).length ) {
                    e('#search-closer').focus();
                }
            }
        } );

    };
    n.true_news_Stickyarea = function () {
        e('.widget-area').theiaStickySidebar({
            additionalMarginTop: 30
        });
    };
    n.true_news_DataBackground = function () {
        var pageSection = e(".data-bg");
        pageSection.each(function (indx) {
            if (e(this).attr("data-background")) {
                e(this).css("background-image", "url(" + e(this).data("background") + ")");
            }
        });
    };
    n.true_news_Overhead = function () {
        e('.offcanvas-trigger-btn').on('click', function () {
            e('#overhead-elements').addClass('visible');
            e('body').addClass('body-scroll-locked');
            e('#overhead-closer').focus();
            e('html').attr('style','overflow-y: scroll; position: fixed; width: 100%; left: 0px; top: 0px;');
        });
        e('#overhead-closer').on('click', function (event) {
            e('#overhead-elements').removeClass('visible');
            e('body').removeClass('body-scroll-locked');
            e('.offcanvas-trigger-btn').focus();
            e('html').attr('style','');
        });
        e(document).keyup(function(j) {
            if (j.key === "Escape") { // escape key maps to keycode `27`
                e('#overhead-elements').removeClass('visible');
                e('body').removeClass('body-scroll-locked');

                if( e( '#overhead-elements').hasClass('visible') ){
                    e('.offcanvas-trigger-btn').focus();
                }
                e('html').attr('style','');
            }
        });

        e( 'input, a, button' ).on( 'focus', function() {
            if ( e( '#overhead-elements' ).hasClass( 'visible' ) ) {

                if ( ! e( this ).parents( '#overhead-elements' ).length ) {
                    e( '#overhead-closer' ).focus();
                }
            }
        } );


    };
    n.true_news_MagnificPopup = function () {
        e('.entry-content .gallery, .widget .gallery, .wp-block-gallery, .zoom-gallery').each(function () {
            e(this).magnificPopup({
                delegate: 'a',
                type: 'image',
                closeOnContentClick: false,
                closeBtnInside: false,
                mainClass: 'mfp-with-zoom mfp-img-mobile',
                image: {
                    verticalFit: true,
                    titleSrc: function (item) {
                        return item.el.attr('title');
                    }
                },
                gallery: {
                    enabled: true
                },
                zoom: {
                    enabled: true,
                    duration: 300,
                    opener: function (element) {
                        return element.find('img');
                    }
                }
            });
        });
        e('.popup-video').each(function () {
            e(this).magnificPopup({
                type: 'iframe',
                mainClass: 'mfp-fade',
                preloader: true,
            });
        });
    };
    n.true_news_Scrollstatus = function () {
        if (e(window).scrollTop() > e(window).height() / 2) {
            e(".scroll-up").fadeIn(300);
        } else {
            e(".scroll-up").fadeOut(300);
        }
    };
    n.true_news_Scroller = function () {
        e(".scroll-up").on("click", function () {
            e("html, body").animate({
                scrollTop: 0
            }, 700);
            return false;
        });
    };
    n.true_news_CommentBox = function () {
        e(".twp-comment-toggle").on("click", function () {
            e('.twp-comment-area').slideToggle('slow');
            e(this).toggleClass('twp-comment-active');
        });
    };
    n.trur_news_tab_posts = function () {
        e('.twp-nav-tabs .tab').on('click', function (event) {
            var tabid = e(this).attr('tab-data');
            e(this).closest('.tabbed-container').find('.tab').removeClass('active');
            e(this).addClass('active');
            e(this).closest('.tabbed-container').find('.tab-content .tab-pane').removeClass('active');
            e(this).closest('.tabbed-container').find('.content-' + tabid).addClass('active');
        });
    };
    n.true_news_HeaderImage = function () {
        var adminbarheight = '';
        if ( e('body').hasClass('twp-has-header-image') ){
            adminbarheight = e('#wpadminbar').height();
        }
        if ( e('body').hasClass('twp-has-header-image') ){
            var ContainerHeight = e('.twp-header-image').height();
            if( ContainerHeight && ContainerHeight != 0 ){
                if ( e('body').hasClass('twp-has-header-image') ){
                    adminbarheight = e('#wpadminbar').height();
                }
                e('.twp-site-content').css( "margin-top", ContainerHeight-adminbarheight+'px' );
            }
            e(window).on('resize', function(){
                ContainerHeight = e('.twp-header-image').height();
                if( ContainerHeight && ContainerHeight != 0 ){
                    if ( e('body').hasClass('twp-has-header-image') ){
                        adminbarheight = e('#wpadminbar').height();
                    }
                    e('.twp-site-content').css( "margin-top", ContainerHeight-adminbarheight+'px' );
                }
            });
        }
    };
    n.true_news_TickeronScroll = function () {
        if (e(window).scrollTop() > e(window).height() / 2) {
            e(".recommendation-panel-content").addClass('active-scrollpanel').css({'opacity': 1});
        } else {
            e(".recommendation-panel-content").removeClass('active-scrollpanel').css({'opacity': 0});
        }
    };
    n.thre_news_masonary = function () {
        if ( e('.all-item-content').length > 0 ) {
            /*Default masonry animation*/
            var grid;
            var hidden = 'scale(0.5)';
            var visible = 'scale(1)';
            grid = e('.all-item-content').imagesLoaded(function () {
                grid.masonry({
                    itemSelector: '.twp-masonary-item',
                    hiddenStyle: {
                        transform: hidden,
                        opacity: 0
                    },
                    visibleStyle: {
                        transform: visible,
                        opacity: 1
                    }
                });
            });
        }
    };
    n.true_news_FooterTicker = function () {
        e('.recommendation-panel-content').each(function () {
            var post_bar = e(this);
            var post_button = e(this).siblings('.drawer-handle');
            if (post_bar.hasClass("recommendation-panel-content")) {
                e('html').animate({'padding-bottom': 110}, 200);
            }
            e(this).on('click', '.drawer-handle-close', function () {
                post_button.addClass('rec-panel-active');
                e('html').animate({'padding-bottom': 0}, 200);
                e('html').addClass('recommendation-panel-disabled');
            });
            post_button.on('click', function () {
                post_button.removeClass('rec-panel-active');
                e('html').animate({'padding-bottom': 110}, 200);
                e('html').removeClass('recommendation-panel-disabled');
            });
        });
    };
    
    e(document).ready(function () {
        n.thre_news_masonary();
        n.true_news_MobileMenu.init();
        n.true_news_Preloader();
        n.true_news_Carousal();
        n.true_news_Reveal();
        n.true_news_DataBackground();
        n.true_news_Overhead();
        n.true_news_MagnificPopup();
        n.true_news_Scroller();
        n.true_news_Stickyarea();
        n.trur_news_tab_posts();
        n.true_news_CommentBox();
        n.true_news_HeaderImage();
        n.true_news_FooterTicker();
        
    });
    e(window).scroll(function () {
        n.true_news_Scrollstatus();
        n.true_news_TickeronScroll();
    });
})(jQuery);