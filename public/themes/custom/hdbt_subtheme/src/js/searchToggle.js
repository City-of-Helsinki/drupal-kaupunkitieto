/**
 * @file
 * Search toggle button.
 */
((Drupal) => {

  Drupal.behaviors.searchToggle = {
    attach: function attach() {
      // Header search form
      // Select the toggle button and the search form
      const searchToggleBtn = document.querySelector('.toggle-search-form');
      const searchForm = document.querySelector('.header-search');

      if (searchToggleBtn) {
        // Attach a click event listener to the toggle button
        searchToggleBtn.addEventListener('click', (e) => {
          e.preventDefault();
          // Toggle the display of the search form
          if (searchForm.style.display === 'none' || searchForm.style.display === '') {
            searchForm.style.display = 'block';
          } else {
            searchForm.style.display = 'none';
          }
        });
      }
    },
  };

})(Drupal);
