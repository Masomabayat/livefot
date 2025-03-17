(function (e) {
    "use strict";
    var n = window.AFTHRAMPES_JS || {};
    n.stickyMenu = function () {
        e(window).scrollTop() > 350 ? e("#masthead").addClass("nav-affix") : e("#masthead").removeClass("nav-affix")
    },
        n.mobileMenu = {
            init: function () {
                this.toggleMenu(), this.menuMobile(), this.menuArrow()
            },
            toggleMenu: function () {
                e('#masthead').on('click', '.toggle-menu', function (event) {
                    var ethis = e('.main-navigation .menu .menu-mobile');
                    if (ethis.css('display') == 'block') {
                        ethis.slideUp('300');
                    } else {
                        ethis.slideDown('300');
                    }
                    e('.ham').toggleClass('exit');
                    e('body.aft-sticky-header').toggleClass('aft-sticky-header-revealed');
                });

                e('#masthead .main-navigation ').on('click', '.menu-mobile a button', function (event) {
                    event.preventDefault();
                    var ethis = e(this),
                        eparent = ethis.closest('li');

                    if (eparent.find('> .children').length) {
                        var esub_menu = eparent.find('> .children');
                    } else {
                        var esub_menu = eparent.find('> .sub-menu');
                    }

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
            menuMobile: function () {
                if (e('.main-navigation .menu > ul').length) {

                    var ethis = e('.main-navigation .menu > ul'),
                        eparent = ethis.closest('.main-navigation'),
                        pointbreak = eparent.data('epointbreak'),
                        window_width = window.innerWidth;

                    if (typeof pointbreak == 'undefined') {
                        pointbreak = 991;
                    }

                    if (pointbreak >= window_width) {
                        ethis.addClass('menu-mobile').removeClass('menu-desktop');
                        e('.main-navigation .toggle-menu').css('display', 'block');
                    } else {
                        ethis.addClass('menu-desktop').removeClass('menu-mobile').css('display', '');
                        e('.main-navigation .toggle-menu').css('display', 'none');
                    }
                }
            },
            menuArrow: function () {
                if (e('#masthead .main-navigation div.menu > ul').length) {
                    e('#masthead .main-navigation div.menu > ul .sub-menu').parent('li').find('> a').append('<button class="fa fa-angle-down">');
                    e('#masthead .main-navigation div.menu > ul .children').parent('li').find('> a').append('<button class="fa fa-angle-down">');
                }
            }
        },


        n.DataBackground = function () {
            var pageSection = e(".data-bg");
            pageSection.each(function (indx) {
                if (e(this).attr("data-background")) {
                    e(this).css("background-image", "url(" + e(this).data("background") + ")");
                }
            });

            e('.bg-image').each(function () {
                var src = e(this).children('img').attr('src');
                e(this).css('background-image', 'url(' + src + ')').children('img').hide();
            });
        },

        n.setInstaHeight = function () {
            e('.insta-slider-block').each(function () {
                var img_width = e(this).find('.insta-item .af-insta-height').eq(0).innerWidth();

                e(this).find('.insta-item .af-insta-height').css('height', img_width);
            });
        },


        /* Slick Slider */
        n.SlickCarousel = function () {



            e(".full-slider-mode").slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 12000,
                infinite: true,
                nextArrow: '<span class="slide-icon slide-icon-1 slide-next icon-right fas fa-angle-right"></span>',
                prevArrow: '<span class="slide-icon slide-icon-1 slide-prev icon-left fas fa-angle-left"></span>',
                appendArrows: e('.af-main-navcontrols'),
                rtl: rtl_slick()

            });

            function rtl_slick(){
                if (e('body').hasClass("rtl")) {
                    return true;
                } else {
                    return false;
                }}


            e(".posts-slider").slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 10000,
                infinite: true,
                nextArrow: '<i class="slide-icon slide-icon-1 slide-next icon-right fas fa-angle-right"></i>',
                prevArrow: '<i class="slide-icon slide-icon-1 slide-prev icon-left fas fa-angle-left"></i>',
                rtl: rtl_slick()
            });

            e(".classic-mode .trending-posts-carousel").not('.slick-initialized').slick({
                autoplay: true,
                vertical: true,
                slidesToShow: 5,
                slidesToScroll: 1,
                verticalSwiping: true,
                autoplaySpeed: 10000,
                infinite: true,
                nextArrow: '<i class="slide-icon slide-icon-1  slide-next fas fa-angle-down"></i>',
                prevArrow: '<i class="slide-icon slide-icon-1 slide-prev fas fa-angle-up"></i>',
                appendArrows: e('.af-trending-navcontrols'),
                responsive: [
                    {
                        breakpoint: 1834,
                        settings: {
                            slidesToShow: 5
                        }
                    },
                    {
                        breakpoint: 991,
                        settings: {
                            slidesToShow: 5
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

            e("#aft-trending-story-five .trending-posts-carousel").not('.slick-initialized').slick({
                autoplay: true,
                vertical: true,
                slidesToShow: 5,
                slidesToScroll: 1,
                verticalSwiping: true,
                autoplaySpeed: 10000,
                infinite: true,
                nextArrow: '<i class="slide-icon slide-icon-1  slide-next fas fa-angle-down"></i>',
                prevArrow: '<i class="slide-icon slide-icon-1 slide-prev fas fa-angle-up"></i>',
                appendArrows: e('.af-trending-navcontrols'),
                responsive: [
                    {
                        breakpoint: 1834,
                        settings: {
                            slidesToShow: 5
                        }
                    },
                    {
                        breakpoint: 991,
                        settings: {
                            slidesToShow: 5
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

            e(".trending-posts-carousel").not('.slick-initialized').slick({
                autoplay: true,
                vertical: true,
                slidesToShow: 4,
                slidesToScroll: 1,
                verticalSwiping: true,
                autoplaySpeed: 10000,
                infinite: true,
                nextArrow: '<i class="slide-icon slide-icon-1  slide-next fas fa-angle-down"></i>',
                prevArrow: '<i class="slide-icon slide-icon-1 slide-prev fas fa-angle-up"></i>',
                appendArrows: e('.af-trending-navcontrols'),
                responsive: [
                {
                        breakpoint: 1834,
                        settings: {
                            slidesToShow: 4
                        }
                    },
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



            e(".trending-posts-vertical-carousel").slick({
                autoplay: true,
                vertical: true,
                slidesToShow: 5,
                slidesToScroll: 1,
                verticalSwiping: true,
                autoplaySpeed: 10000,
                infinite: true,
                nextArrow: '<i class="slide-icon slide-icon-1  slide-next fas fa-angle-down"></i>',
                prevArrow: '<i class="slide-icon slide-icon-1 slide-prev fas fa-angle-up"></i>'
            });

            e("#primary .posts-carousel").slick({
                slidesToShow: 3,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 10000,
                infinite: true,
                rtl: rtl_slick(),
                nextArrow: '<i class="slide-icon slide-icon-1 slide-next icon-right fas fa-angle-right"></i>',
                prevArrow: '<i class="slide-icon slide-icon-1 slide-prev icon-left fas fa-angle-left"></i>',
                responsive: [
                    {
                        breakpoint: 991,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2
                        }
                    },

                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1
                        }
                    }
                ]
            });

             e("#secondary .posts-carousel").slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 10000,
                infinite: true,
                 rtl: rtl_slick(),
                nextArrow: '<i class="slide-icon slide-icon-1 slide-next icon-right fas fa-angle-right"></i>',
                prevArrow: '<i class="slide-icon slide-icon-1 slide-prev icon-left fas fa-angle-left"></i>',
                
            });

            e(".gallery-columns-1").slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                fade: true,
                autoplay: true,
                autoplaySpeed: 10000,
                infinite: true,
                nextArrow: '<i class="slide-icon slide-icon-1 slide-next fa fa-arrow-right"></i>',
                prevArrow: '<i class="slide-icon slide-icon-1 slide-prev fa fa-arrow-left"></i>',
                dots: true
            });



        },

        n.Preloader = function () {
            e(window).on('load', function () {
                e('#loader-wrapper').fadeOut();
                e('#af-preloader').delay(500).fadeOut('slow');

            });
        },

        n.Search = function () {
            e(window).on('load', function () {
                e(".af-search-click").on('click', function(){
                    e("#af-search-wrap").toggleClass("af-search-toggle");
                });
            });
        },

        n.searchReveal = function () {
            e(window).on('load', function () {
            jQuery('.search-icon').on('click', function (event) {
                event.preventDefault();
                jQuery('.search-overlay').toggleClass('reveal-search');                
            });

            });
            

        },

        n.Offcanvas = function () {
            e('.offcanvas-nav').sidr({
                side: 'left'
            });

            e('.sidr-class-sidr-button-close').on('click', function () {
                e.sidr('close', 'sidr');
            });
        },

        // SHOW/HIDE SCROLL UP //
        n.show_hide_scroll_top = function () {
            if (e(window).scrollTop() > e(window).height() / 2) {
                e("#scroll-up").fadeIn(300);
            } else {
                e("#scroll-up").fadeOut(300);
            }
        },

        n.scroll_up = function () {
            e("#scroll-up").on("click", function () {
                e("html, body").animate({
                    scrollTop: 0
                }, 800);
                return false;
            });
        },



        n.MagnificPopup = function () {
            e('div.zoom-gallery').magnificPopup({
                delegate: 'a.insta-hover',
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

            e('.gallery').each(function() {
                e(this).magnificPopup({
                    delegate: 'a',
                    type: 'image',
                    gallery: {
                        enabled:true
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


            e('.wp-block-gallery').each(function () {
                e(this).magnificPopup({
                    delegate: 'a',
                    type: 'image',
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

        },


        n.jQueryMarqueeRight = function () {
            e('.marquee.flash-slide-right').marquee({
                //duration in milliseconds of the marquee
                speed: 80000,
                //gap in pixels between the tickers
                gap: 0,
                //time in milliseconds before the marquee will start animating
                delayBeforeStart: 0,
                //'left' or 'right'
                //direction: 'right',
                //true or false - should the marquee be duplicated to show an effect of continues flow
                duplicated: true,
                pauseOnHover: true,
                startVisible: true
            });
        },

        n.jQueryMarquee = function () {
            e('.marquee.flash-slide-left').marquee({
                //duration in milliseconds of the marquee
                speed: 80000,
                //gap in pixels between the tickers
                gap: 0,
                //time in milliseconds before the marquee will start animating
                delayBeforeStart: 0,
                //'left' or 'right'
                //direction: 'left',
                //true or false - should the marquee be duplicated to show an effect of continues flow
                duplicated: true,
                pauseOnHover: true,
                startVisible: true
            });
        },



        n.VideoSlider = function () {
            
            e( '.video-slider' ).sliderPro({
                width: '100%',
                height: 460,
                arrows: true,
                buttons: false,
                fullScreen: false,
                thumbnailWidth: 160,
                thumbnailHeight: 112,
                thumbnailsPosition: 'right',
                autoplay: false,
                fadeArrows: false,
                loop: true,
                breakpoints: {
                    1920: {
                        thumbnailWidth: 180,
                        thumbnailHeight: 100,
                        height: 510,
                        width: 906,
                    },
                    1600: {
                        thumbnailWidth: 140,
                        thumbnailHeight: 80,
                        height: 390,
                        width: 700,
                    },
                    1366: {
                        thumbnailWidth: 140,
                        thumbnailHeight: 80,
                        height: 442,
                        width: 786,
                    },
                    1024: {
                        thumbnailWidth: 142,
                        thumbnailHeight: 80,
                        height: 295,
                        width: 523,
                    },
                    800: {
                        height: 423,
                        thumbnailsPosition: 'bottom',
                        thumbnailWidth: 142,
                        thumbnailHeight: 80
                    },
                    500: {
                        height: 270,
                        thumbnailsPosition: 'bottom',
                        thumbnailWidth: 142,
                        thumbnailHeight: 80
                    },
                    425: {
                        height: 230,
                        thumbnailsPosition: 'bottom',
                        thumbnailWidth: 142,
                        thumbnailHeight: 80
                    },
                    375: {
                        height: 200,
                        thumbnailsPosition: 'bottom',
                        thumbnailWidth: 142,
                        thumbnailHeight: 80
                    }
                }

            });
            jQuery( '.sp-next-arrow' ).addClass( 'fas fa-angle-right' );
            jQuery( '.sp-previous-arrow' ).addClass( 'fas fa-angle-left' );
        },

        n.VideoSliderHorizontal = function () {

            e( '.video-slider-horizontal' ).sliderPro({
                width: '100%',
                height: 460,
                arrows: true,
                buttons: false,
                fullScreen: false,
                thumbnailWidth: 160,
                thumbnailHeight: 112,
                thumbnailsPosition: 'bottom',
                autoplay: false,
                fadeArrows: false,
                loop: true,
                breakpoints: {
                    1920: {
                        thumbnailWidth: 200,
                        thumbnailHeight: 100,
                        height: 624,
                    },
                    1600: {
                        thumbnailWidth: 142,
                        thumbnailHeight: 80,
                        height: 467,
                    },
                    1366: {
                        thumbnailWidth: 142,
                        thumbnailHeight: 110,
                        height: 506,
                    },
                    1024: {
                        thumbnailWidth: 142,
                        thumbnailHeight: 110,
                        height: 376,
                    },
                    800: {
                        height: 423,
                        thumbnailsPosition: 'bottom',
                        thumbnailWidth: 142,
                        thumbnailHeight: 110
                    },
                    500: {
                        height: 239,
                        thumbnailsPosition: 'bottom',
                        thumbnailWidth: 142,
                        thumbnailHeight: 110
                    },
                    375: {
                        height: 199,
                        thumbnailsPosition: 'bottom',
                        thumbnailWidth: 142,
                        thumbnailHeight: 110
                    }
                }

            });
            jQuery( '.sp-next-arrow' ).addClass( 'fas fa-angle-right' );
            jQuery( '.sp-previous-arrow' ).addClass( 'fas fa-angle-left' );
        },

        e(function () {
            n.mobileMenu.init(), n.DataBackground(), n.setInstaHeight(), n.MagnificPopup(), n.jQueryMarquee(),n.jQueryMarqueeRight(),n.VideoSlider(), n.VideoSliderHorizontal(), n.SlickCarousel(), n.Offcanvas(), n.scroll_up();
        }), e(window).on('scroll', function () {
        n.stickyMenu(), n.show_hide_scroll_top();
    }), e(window).on('resize', function () {
        n.mobileMenu.menuMobile();
    }), e(window).on('load', function () {
        e('#loader-wrapper').fadeOut();
        e('#af-preloader').delay(500).fadeOut('slow');

        e(".af-search-click").on('click', function(){
            e("#af-search-wrap").toggleClass("af-search-toggle");
        });

        e('.search-icon').on('click', function (event) {
            event.preventDefault();
            e('.search-overlay').toggleClass('reveal-search');
        });


    })
})(jQuery);