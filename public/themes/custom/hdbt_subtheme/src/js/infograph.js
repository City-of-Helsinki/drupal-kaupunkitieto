((Drupal, $) => {

  Drupal.behaviors.infographLinkBehavior = {
    attach() {
      $('.paragraph--type--infograph .infograph__value:contains("%")').html((_, html) =>
        html.split('%').join('<span class="small-char">%</span')
      );

      $('.infograph .infograph__item').on('click', (event) => {
        const target = $(event.currentTarget);
        event.preventDefault();
        const url = target.find('.infograph__value a')?.attr('href');
        if (url) {
          window.location.href = url;
        }
      });
    }
  };

  Drupal.behaviors.infographGroupBehavior = {
    attach(context, settings) {

      const resetSlider = () => {
        $('.paragraph--type--infograph-group').slick({
          prevArrow: '.arrow-left-wrapper span',
          nextArrow: '.arrow-right-wrapper span',
          autoplay: settings?.infograph_group?.autoplay,
          autoplaySpeed: settings?.infograph_group?.speed,
        });
      };

      const status = $('.pagingInfo');
      const slickElement = $('.paragraph--type--infograph-group');

      slickElement.on('init reInit afterChange', (event, slick, currentSlide) => {
        const i = (currentSlide || 0) + 1;
        status.text(`${i} / ${slick.slideCount}`);
      });

      resetSlider();

      $(window).on('resize orientationchange', () => {
        $('.slick-slider').slick('unslick');
        resetSlider();
      });
    }
  };

})(Drupal, jQuery);
