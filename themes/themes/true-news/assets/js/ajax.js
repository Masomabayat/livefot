
function true_news_read_later_posts( cruurenclass = '' ){
    
    jQuery(document).ready( function($) {

        "use scrict";
        if( cruurenclass ){
            var ClickClass = '.'+cruurenclass+' .twp-pin-post';
        }else{
            var ClickClass = '.twp-pin-post';
        }

        $(ClickClass).click(function () {

            var cid = $(this).attr('pid');
            var ppcount = $('.twp-pin-posts-count').attr('twp-pp-count');
            var current = '.'+cid;
            var AddRemove;
            if( $(current).hasClass('twp-pp-active') ){
                ppcount = ppcount - 1;
                $(current).removeClass('twp-pp-active');
                AddRemove = 'remove';

            }else{
                ppcount++;
                $(current).addClass('twp-pp-active');
                AddRemove = 'add';
            }

            $('.twp-pin-posts-count').empty();
            $('.twp-pin-posts-count').attr('twp-pp-count',ppcount);
            $('.twp-pin-posts-count').html(ppcount);

            var pid = $(current).attr('post-id');
            var ajaxurl = true_news_ajax.ajax_url;
            var data = {
                'action': 'true_news_read_later_post_ajax',
                'pid': pid,
                'AddRemove': AddRemove,
                '_wpnonce': true_news_ajax.ajax_nonce,
            };
            $.post(ajaxurl, data, function (response) {
                $('.twp-read-later-notification').empty();
                $('.twp-read-later-notification').html(response);
                $('.twp-read-later-notification').fadeIn();
                setTimeout(function () { 
                    $('.twp-read-later-notification').fadeOut();
                }, 3000);

                $('#twp-close-popup').click(function(){
                    $('.twp-read-later-notification').fadeOut();
                });

            });
            
        });

    });
}

true_news_read_later_posts();

jQuery(document).ready( function($) {
    
    "use scrict";

    var n = window.MINIMAL_JS || {};
    n.SingleColGallery = function (gal_selector) {

        if ($.isArray(gal_selector)) {
            $.each(gal_selector, function (index, value) {
                $("#" + value).find('.gallery-columns-1, ul.wp-block-gallery.columns-1, .wp-block-gallery.columns-1 .blocks-gallery-grid').slick({
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
        } else {
            $("." + gal_selector).slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                dots: false,
                infinite: false,
                autoplay: false,
                nextArrow: '<i class="navcontrol-icon slide-next ion-ios-arrow-right"></i>',
                prevArrow: '<i class="navcontrol-icon slide-prev ion-ios-arrow-left"></i>'
            });
        }
    };

    n.trueMagnificPopup = function () {
        $('.entry-content .gallery, .widget .gallery, .blocks-gallery-item, .zoom-gallery').each(function () {
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


    var grid;
    var ajaxurl = true_news_ajax.ajax_url;
    var loading = true_news_ajax.loading;
    var loadmore = true_news_ajax.loadmore;
    var nomore = true_news_ajax.nomore;
    var page = 2;

    $('body').on('click', '.loadmore', function() {

        $(this).addClass('loading');
        $(this).html('<span class="ajax-loader"></span><span>'+loading+'</span>');
        var data = {
            'action': 'true_news_recommended_posts',
            'page': page,
            '_wpnonce': true_news_ajax.ajax_nonce,
        };
 
        $.post(ajaxurl, data, function(response) {
            
            if( response ){

                
                var content_join = response.data.content.join('');
                var content = $(content_join);
                var gal_selectors = [];
                $(content_join).find('.entry-gallery').each(function () {
                    gal_selectors.push($(this).closest('article').attr('id'));
                });

                content.hide();
                grid = $('.all-item-content');
                grid.append(content);
                grid.imagesLoaded( function() {

                    content.show();
                    n.SingleColGallery(gal_selectors);

                    var winwidth = $(window).width();
                    $(window).resize(function() {
                        winwidth = $(window).width();
                    });

                    if( winwidth > 990 ){
                        grid.masonry('appended', content).masonry();
                    }else{
                        grid.masonry('appended', content);
                    }
                });

                n.trueMagnificPopup();
            }
            
            page++;

            if( !$.trim(response) ){
                $('.loadmore').addClass('no-more-post');
                $('.loadmore').html(nomore);
            }else{
                $('.loadmore').html(loadmore);
            }

            true_news_tooltip( '.twp-current-recommended [data-ui-tooltip]' );
            true_news_read_later_posts('twp-current-recommended');
            $('.twp-current-recommended').each(function(){
                $(this).removeClass('twp-current-recommended');
            });
            $('.loadmore').removeClass('loading');

        });

    });


    $('body').on('click', '.tn-tab-title a', function() {

        $('.tn-infinity-scroll a').hide();
        var Hspaa = $(this).closest('.tn-tab-title').attr('hs-paa');
        var Hspd = $(this).closest('.tn-tab-title').attr('hs-pd');
        var Hspa = $(this).closest('.tn-tab-title').attr('hs-pa');

        page1 = 1;
        var catid = $(this).attr('cat-id');
        $('.tn-tab-title a').removeClass('tn-tab-active');
        $(this).addClass('tn-tab-active');
        $('.loadmore-tab').removeAttr('current-id');
        $('.loadmore-tab').attr('current-id',catid);
        $('.twp-masonary-layout').removeClass('tn-content-active');
        $('.'+catid+'-content').addClass('tn-content-active');
        $('.'+catid+'-current-tab').show();


        if( !$('.'+catid+'-content').hasClass('content-loded') ){

            $('.'+catid+'-content').addClass('content-loded');
            var data1 = {
                'action': 'true_news_recommended_posts_tab',
                '_wpnonce': true_news_ajax.ajax_nonce,
                'page': page1,
                'catid': catid,
                'Hspaa': Hspaa,
                'Hspd': Hspd,
                'Hspa': Hspa,
                //'first': true,
            };
            $.post(ajaxurl, data1, function(response) {

                if( response ){

                    var content_join = response.data.content.join('');
                    var content = $(content_join);

                    content.hide();
                    grid = $('.'+catid+'-content');
                    grid.append(content);
                    content.show();

                    if ( $(grid).length > 0 ) {

                        /*Default masonry animation*/
                        var hidden  = 'scale(0.5)';
                        var visible = 'scale(1)';
                        grid1 = $(grid).imagesLoaded(function () {
                            
                            $("."+catid+"-content .news-article-tab ul.wp-block-gallery.columns-1, ."+catid+"-content .news-article-tab .wp-block-gallery.columns-1 .blocks-gallery-grid, ."+catid+"-content .news-article-tab .gallery-columns-1").each(function () {
                                $(this).slick({
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
                            
                            $('.news-article-tab').each(function(){
                                $(this).removeClass('news-article-tab');
                            });

                            grid1.masonry({
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

                    

                }
                
                n.trueMagnificPopup();

                page1++;

                if( !$.trim(response) ){
                    $('.loadmore-tab').addClass('no-more-post');
                    $('.loadmore-tab').html(nomore);
                }else{
                    $('.loadmore-tab').html(loadmore);
                }

                $('.loadmore-tab').removeClass('loading');

            });

        }

    });

    $('body').on('click', '.loadmore-tab', function() {

        var current = $(this);
        var page2 = current.attr('paged');
        var catid = current.attr('current-id');
        var Hspaa = current.closest('.tn-tab-title').attr('hs-paa');
        var Hspd = current.closest('.tn-tab-title').attr('hs-pd');
        var Hspa = current.closest('.tn-tab-title').attr('hs-pa');
        
        current.addClass('loading-tab');
        current.html('<span class="ajax-loader-tab"></span><span>'+loading+'</span>');
        
        var data1 = {
            'action': 'true_news_recommended_posts_tab',
            '_wpnonce': true_news_ajax.ajax_nonce,
            'page': page2,
            'catid': catid,
            'Hspaa': Hspaa,
            'Hspd': Hspd,
            'Hspa': Hspa,
        };

        $.post(ajaxurl, data1, function(response) {

            if( response ){

                var content_join = response.data.content.join('');
                var content = $(content_join);
                console.log(content);

                content.hide();
                grid = $('.'+catid+'-content');
                grid.append(content);
                content.show();
                grid.imagesLoaded( function() {

                    $("."+catid+"-content .news-article-tab ul.wp-block-gallery.columns-1, ."+catid+"-content .news-article-tab .wp-block-gallery.columns-1 .blocks-gallery-grid, ."+catid+"-content .news-article-tab .gallery-columns-1").each(function () {
                        $(this).slick({
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
                    
                    $('.news-article-tab').each(function(){
                        $(this).removeClass('news-article-tab');
                    });

                    var winwidth = $(window).width();
                    $(window).resize(function() {
                        winwidth = $(window).width();
                    });

                    if( winwidth > 990 ){
                        grid.masonry('appended', content).masonry();
                    }else{
                        grid.masonry('appended', content);
                    }
                });

            }
            
            n.trueMagnificPopup();

            page2++;
            current.attr('paged',page2);
            if( !$.trim(response) ){
                $('.loadmore-tab').addClass('no-more-post');
                $('.loadmore-tab').html(nomore);
            }else{
                $('.loadmore-tab').html(loadmore);
            }

            $('.loadmore-tab').removeClass('loading');

        });

    });

    $('body').on('click', '.twp-recommended-loadmore', function() {
        
        var loading     = true_news_ajax.loading;
        var loadmore    = true_news_ajax.loadmore;
        var nomore      = true_news_ajax.nomore;
        var cat         = $(this).attr('data-cat');
        var repeat_time = $(this).attr('repeat-time');
        var paged = $(this).attr('paged');
        var date = $(this).attr('date');
        var author = $(this).attr('author');
        var author_avtar = $(this).attr('author-avtar');

        $('.ajax-added-posts').each(function(){
            $(this).removeClass('ajax-added-posts');
        });

        $(this).addClass('loading');
        $(this).html('<span class="ajax-loader"></span><span>'+loading+'</span>');

        var data = {
            'action': 'true_news_latest_posts',
            '_wpnonce': true_news_ajax.ajax_nonce,
            'page': paged,
            'category': cat,
            'date': date,
            'author': author,
            'author_avtar': author_avtar,
        };

        $.post( ajaxurl, data, function( response ) {

            $('.twp-latest-post-'+repeat_time+' .latest-blog-wrapper').append(response);

            paged++;
            $('.twp-latest-post-'+repeat_time+' .twp-recommended-loadmore').attr('paged',paged);

            if( !$.trim(response) ){

                $('.twp-latest-post-'+repeat_time+' .twp-recommended-loadmore').addClass('no-more-post');
                $('.twp-latest-post-'+repeat_time+' .twp-recommended-loadmore').html(nomore);

            }else{

                $('.twp-latest-post-'+repeat_time+' .twp-recommended-loadmore').html(loadmore);

            }

            $('.twp-latest-post-'+repeat_time+' .twp-recommended-loadmore').removeClass('loading');

            true_news_read_later_posts('twp-current-recommended');
            true_news_tooltip( '.twp-current-recommended [data-ui-tooltip]' );
            $('.twp-current-recommended').each(function(){
                $(this).removeClass('twp-current-recommended');
            });
        });

    });

    $('.twp-latest-tab li button').click(function () {
        var tabid = $(this).attr('id');
        var trending = $(this).attr('twp-trending-duration');
        $('.twp-latest-tab li button').removeClass('twp-tab-active');
        $(this).addClass('twp-tab-active');
        $('.twp-tab-contents').removeClass('twp-content-active');
        $('#' + tabid + '-content').addClass('twp-content-active');

        if (!$(this).hasClass('twp-content-loded')) {
            
            if (tabid == 'twp-popular-tab') {
                $('#twp-popular-tab-content').addClass('twp-content-loding');
            }
            if (tabid == 'twp-trending-tab') {
                $('#twp-trending-tab-content').addClass('twp-content-loding');
            }
            var ajaxurl = true_news_ajax.ajax_url;
            var data = {
                'action': 'true_news_get_posts_ajax',
                'tabid': tabid,
                'trending': trending,
                '_wpnonce': true_news_ajax.ajax_nonce,
            };
            $.post(ajaxurl, data, function (response) {

                if (tabid == 'twp-popular-tab') {
                    $('#twp-popular-tab-content').html(response);
                    $('#' + tabid).addClass('twp-content-loded');
                    $('#twp-popular-tab-content').removeClass('twp-content-loding');
                }
                if (tabid == 'twp-trending-tab') {
                    $('#twp-trending-tab-content').html(response);
                    $('#' + tabid).addClass('twp-content-loded');
                    $('#twp-trending-tab-content').removeClass('twp-content-loding');
                }

                true_news_tooltip( '.twp-current-recommended [data-ui-tooltip]' );
                true_news_read_later_posts('twp-current-recommended');
                $('.twp-current-recommended').each(function(){
                    $(this).removeClass('twp-current-recommended');
                });

            });
        }

    });

});


