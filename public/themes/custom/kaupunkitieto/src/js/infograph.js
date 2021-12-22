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


})(jQuery, Drupal);
