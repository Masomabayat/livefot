
jQuery(document).ready(function ($) {

    var ajaxurl = true_news_pagination.ajax_url;
    
    function true_news_is_on_screen(elem) {
        
        if ($(elem)[0]){

            var twpwindow = jQuery(window);
            var viewport_top = twpwindow.scrollTop();
            var viewport_height = twpwindow.height();
            var viewport_bottom = viewport_top + viewport_height;
            var twpelem = jQuery(elem);
            var top = twpelem.offset().top;
            var height = twpelem.height();
            var bottom = top + height;
            return (top >= viewport_top && top < viewport_bottom) ||
                (bottom > viewport_top && bottom <= viewport_bottom) ||
                (height > viewport_height && top <= viewport_top && bottom >= viewport_bottom);
        }
    }

    function true_news_magnific_popup () {
        $('.widget .gallery, .entry-content .gallery, .wp-block-gallery').each(function () {
            $(this).magnificPopup({
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
    };

    var n = window.TWP_JS || {};
    var paged = parseInt(true_news_pagination.paged) + 1;
    var maxpage = true_news_pagination.maxpage;
    var nextLink = true_news_pagination.nextLink;
    var loadmore = true_news_pagination.loadmore;
    var loading = true_news_pagination.loading;
    var nomore = true_news_pagination.nomore;
    var pagination_layout = true_news_pagination.pagination_layout;

    $('.twp-loading-button').click(function () {
        if ((!$('.twp-no-posts').hasClass('twp-no-posts'))) {

            $('.twp-loading-button').text(loading);
            $('.twp-loging-status').addClass('twp-ajax-loading');
            $('.twp-loaded-content').load(nextLink + ' .post', function () {
                var newlink = nextLink.substring(0, nextLink.length - 2);
                paged++;
                nextLink = newlink + paged + '/';
                if (paged > maxpage) {
                    $('.twp-loading-button').addClass('twp-no-posts');
                    $('.twp-loading-button').text(nomore);
                } else {
                    $('.twp-loading-button').text(loadmore);
                }
                var lodedContent = $('.twp-loaded-content').html();
                $('.twp-loaded-content').html('');
                $('.content-area article.post:last').after(lodedContent);
                $('.twp-loging-status').removeClass('twp-ajax-loading');

            });
            
        }
    });

    if (pagination_layout == 'auto-load') {
        $(window).scroll(function () {

            if (!$('.true-news-auto-pagination').hasClass('twp-ajax-loading') && !$('.true-news-auto-pagination').hasClass('twp-no-posts') && maxpage > 1 && true_news_is_on_screen('.true-news-auto-pagination')) {
                $('.true-news-auto-pagination').addClass('twp-ajax-loading');
                $('.true-news-auto-pagination').text(loading);
                $('.twp-loaded-content').load(nextLink + ' .post', function () {
                    var newlink = nextLink.substring(0, nextLink.length - 2);
                    paged++;
                    nextLink = newlink + paged + '/';
                    if (paged > maxpage) {
                        $('.true-news-auto-pagination').addClass('twp-no-posts');
                        $('.true-news-auto-pagination').text(nomore);
                    } else {
                        $('.true-news-auto-pagination').removeClass('twp-ajax-loading');
                        $('.true-news-auto-pagination').text(loadmore);
                    }
                    var lodedContent = $('.twp-loaded-content').html();
                    $('.twp-loaded-content').html('');
                    $('.content-area article.post:last').after(lodedContent);
                    console.log(lodedContent);
                    $('.true-news-auto-pagination').removeClass('twp-ajax-loading');
                    

                });
            }

        });
    }

    $(window).scroll(function () {

        if ( !$('.twp-single-infinity').hasClass('twp-single-loading') && $('.twp-single-infinity').attr('loop-count') <= 3 && true_news_is_on_screen('.twp-single-infinity')) {

            $('.twp-single-infinity').addClass('twp-single-loading');
            var loopcount = $('.twp-single-infinity').attr('loop-count');
            var postid = $('.twp-single-infinity').attr('next-post');

            var data = {
                'action': 'true_news_single_infinity',
                '_wpnonce': true_news_pagination.ajax_nonce,
                'postid': postid,
            };
     
            $.post(ajaxurl, data, function(response) {

                if( response ){
                    var content = response.data.content.join('');
                    var content = $(content);
                    $('.twp-single-infinity').before(content);
                    var newpostid = response.data.postid.join('');
                    var newpostid = $(newpostid);
                    $('.twp-single-infinity').attr('next-post',newpostid['selector']);

                    if( $('body').hasClass('booster-extension') ){
                        likedislike('after-load-ajax');
                        booster_extension_post_reaction('after-load-ajax');
                    }
                    
                    $('article#post-'+postid+' ul.wp-block-gallery.columns-1, article#post-'+postid+' .wp-block-gallery.columns-1 .blocks-gallery-grid, article#post-'+postid+' .gallery-columns-1').each(function () {
                        $(this).slick({
                            slidesToShow: 1,
                            slidesToScroll: 1,
                            fade: true,
                            autoplay: true,
                            autoplaySpeed: 8000,
                            infinite: true,
                            nextArrow: '<i class="slide-icon slide-next ion-ios-arrow-round-forward"></i>',
                            prevArrow: '<i class="slide-icon slide-prev ion-ios-arrow-round-back"></i>',
                            dots: false
                        });
                    });
                    true_news_magnific_popup();
                    $('article').each(function(){
                        $(this).removeClass('after-load-ajax');
                    });
                }

                $('.twp-single-infinity').removeClass('twp-single-loading');
                loopcount++;
                $('.twp-single-infinity').attr('loop-count',loopcount);

            });

        }

    });
    
});