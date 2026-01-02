((Drupal, once) => {
  Drupal.behaviors.searchToggle = {
    attach(context) {
      once('searchToggle', 'body', context).forEach(() => {
        // Header search form.
        // Select the toggle button and the search form.
        const searchFormOpenButton = document.querySelector('.toggle-search-form');
        const searchFormCloseButton = document.querySelector('.header_search_form__close');
        const searchForm = document.querySelector('.header-search');

        if (searchFormOpenButton && searchForm) {
          const toggleForm = (e) => {
            if (e) e.preventDefault();
            searchForm.style.display =
              searchForm.style.display === 'none' || !searchForm.style.display ? 'block' : 'none';
          };
          const closeForm = (e) => {
            if (!e || e.type === 'click' || e.key === 'Escape') {
              searchForm.style.display = 'none';
            }
          };

          // Remove any existing listeners first to avoid duplicates.
          searchFormOpenButton.removeEventListener('click', toggleForm);
          searchFormCloseButton?.removeEventListener('click', closeForm);
          document.removeEventListener('keydown', closeForm);

          // Add new listeners.
          searchFormOpenButton.addEventListener('click', toggleForm);
          if (searchFormCloseButton) {
            searchFormCloseButton.addEventListener('click', closeForm);
          }
          document.addEventListener('keydown', closeForm);
        }
      });
    },
  };
})(Drupal, once);
