((Drupal, $) => {
  Drupal.behaviors.infographLinkBehavior = {
    attach(context) {
      // Run only once for the full document.
      if (context !== document || window.infographLinkBehaviorInitialized) {
        return;
      }

      $('.paragraph--type--infograph .infograph__value:contains("%")').html((_, html) =>
        html.split('%').join('<span class="small-char">%</span'),
      );

      $('.infograph .infograph__item').on('click', (event) => {
        const target = $(event.currentTarget);
        event.preventDefault();
        const url = target.find('.infograph__value a')?.attr('href');
        if (url) {
          window.location.href = url;
        }
      });

      window.infographLinkBehaviorInitialized = true;
    },
  };

  Drupal.behaviors.infographGroupBehavior = {
    attach(context, settings) {
      // Run only once for the full document.
      if (context !== document || window.infographGroupBehaviorInitialized) {
        return;
      }

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

      slickElement.on('init reInit afterChange', (_event, slick, currentSlide) => {
        const i = (currentSlide || 0) + 1;
        status.text(`${i} / ${slick.slideCount}`);
      });

      resetSlider();

      $(window).on('resize orientationchange', () => {
        $('.slick-slider').slick('unslick');
        resetSlider();
      });

      window.infographGroupBehaviorInitialized = true;
    },
  };
})(Drupal, jQuery);
