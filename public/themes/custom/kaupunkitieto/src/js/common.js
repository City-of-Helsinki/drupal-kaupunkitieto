/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  Drupal.behaviors.themeCommon = {
    attach: function attach() {
      // Header search form
       const $searchToggleBtn = $('.toggle-search-form');
       const searchForm = $('.header-search');
       if ($searchToggleBtn.length) {
         $searchToggleBtn.on('click', function(e) {
           e.preventDefault()
           $(searchForm).toggle();
         });
       }
    },
  };

})(jQuery, Drupal);
