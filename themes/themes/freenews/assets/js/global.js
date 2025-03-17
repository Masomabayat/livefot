jQuery(document).ready(function($) {

  jQuery('.main-navigation .menu-toggle').click(function() {
    jQuery('.main-navigation ul.nav-menu, .main-navigation .menu > ul').slideToggle();
  });

  //responsive sub menu toggle

  jQuery('.menu-item-has-children > a, .page_item_has_children > a').after('<button class="dropdown-toggle" aria-expanded="false"> <i class="fa fa-chevron-down"></i> </button>');

  jQuery('.main-navigation button.dropdown-toggle').click(function() {
    jQuery(this).toggleClass('active');
    jQuery(this).parent().find('.sub-menu').first().slideToggle();
  });

  //mobile sub menu
  jQuery('.dropdown-toggle').on("click", function(e) {
    e.preventDefault();
    jQuery(this).attr('aria-expanded', function(index, attr) {
      return attr == 'true' ? 'false' : 'true';
    });
  });

  //Touch on focus
  jQuery("body").click(function(){
    jQuery("#primary-menu li").removeClass("focus");
  });

  /* Secondary Menu on Mobile */
  jQuery('.secondary-navigation .secondary-menu-toggle').click(function() {
    jQuery('.secondary-navigation ul.secondary-menu').slideToggle();
  });

  jQuery(document).ready(function($){ $('.secondary-navigation .menu-item-has-children').hover(
        function(){$(this).addClass("dropdown-children");},
        function(){$(this).removeClass("dropdown-children");}
      );

    $('.secondary-navigation .menu-item-has-children a').on('focus blur',
      function(){$(this).parents().toggleClass("dropdown-children");}
    );
  });
  
  
  //sticky sidebar
  jQuery('.left-widget-area, .right-widget-area, .widget-area')
    .theiaStickySidebar({
      additionalMarginTop: 140,
      'minWidth': 960,
    });


  /*Scroll to Top*/
  jQuery(document).ready(function() {
    // Start Scroll To Top
    var btn = jQuery('.back-to-top');
    jQuery(window).scroll(function() {
      if ($(this).scrollTop() >= 300) {
        btn.show();
        btn.addClass('active');
      } else {
        btn.hide();
        btn.removeClass('active');
      }
    });

    btn.click(function() {
      jQuery('html, body').animate({
        scrollTop: 0
      }, 600);
      return false;
    });
    // End Scroll To Top
  });

  /*Time*/
  var myVar = setInterval(function() {
    myTimer();
  }, 100);

  function myTimer() {
    var d = new Date();
    document.getElementById("time").innerHTML = d.toLocaleTimeString();
  }

});