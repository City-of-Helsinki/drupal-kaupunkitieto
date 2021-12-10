/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  Drupal.behaviors.infographLinkBehavior = {
    attach: function (context, settings) {
      $(document).ready(function(){
        $('.infograph .infograph__item').on('click', function() {
          location.href = $(this).find('.infograph__value a').attr('href');
        });
      });
    }
  };


})(jQuery, Drupal);
