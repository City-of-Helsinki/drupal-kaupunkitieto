/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  Drupal.behaviors.infographLinkBehavior = {
    attach: function (context, settings) {
      $(document).ready(function(){
        $('.paragraph--type--infograph .infograph__value:contains("%")').html(function(_, html) {
          return html.split('%').join('<span class="small-char">%</span');
        });

        $('.infograph .infograph__item').on('click', function() {
          location.href = $(this).find('.infograph__value a').attr('href');
        });
      });
    }
  };

  Drupal.behaviors.infographGroupBehavior = {
    attach: function (context, settings) {
      $(document).ready(function(){

        var $status = $('.pagingInfo');
        var $slickElement = $('.paragraph--type--infograph-group');

        $slickElement.on('init reInit afterChange', function (event, slick, currentSlide, nextSlide) {
          //currentSlide is undefined on init -- set it to 0 in this case (currentSlide is 0 based)
          var i = (currentSlide ? currentSlide : 0) + 1;
          $status.text(i + '/' + slick.slideCount);
        });

        reSlick();

        $(window).on('resize orientationchange', function() {
          $('.slick-slider').slick('unslick');
          reSlick();
        });

        function reSlick() {
          $('.paragraph--type--infograph-group').slick({
            prevArrow: '.arrow-left-wrapper span',
            nextArrow: '.arrow-right-wrapper span',
            autoplay: settings.infograph_group.autoplay,
            autoplaySpeed: settings.infograph_group.speed,
          });
        }

      });
    }
  };


})(jQuery, Drupal);
