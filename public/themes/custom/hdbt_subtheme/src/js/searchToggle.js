((Drupal) => {
  Drupal.behaviors.searchToggle = {
    attach() {
      // Header search form
      // Select the toggle button and the search form
      const searchFormOpenButton = document.querySelector('.toggle-search-form');
      const searchFormCloseButton = document.querySelector('.header_search_form__close');
      const searchForm = document.querySelector('.header-search');

      if (searchFormOpenButton) {
        // Attach a click event listener to the toggle button
        searchFormOpenButton.addEventListener('click', (e) => {
          e.preventDefault();
          // Toggle the display of the search form
          if (searchForm.style.display === 'none' || searchForm.style.display === '') {
            searchForm.style.display = 'block';
          } else {
            searchForm.style.display = 'none';
          }
        });
      }
      if (searchFormCloseButton) {
        const closeForm = (evt) => {
          if (evt.type === 'click' || evt.key === 'Escape') {
            searchForm.style.display = 'none';
          }
        };

        // Attach a click event listener to the toggle button
        searchFormCloseButton.addEventListener('click', closeForm, this);
        document.addEventListener('keydown', closeForm, this);
      }
    },
  };

})(Drupal);
