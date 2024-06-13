(function ($) {

  document.addEventListener('DOMContentLoaded', function () {

    const navigationLinks = document.getElementsByClassName('content-menu__navigation-link');
    // Bind event for navigation links.
    for (let navigationLink of navigationLinks) {
      navigationLink.addEventListener('click', () => {
        let id = navigationLink.dataset.id;
        openItem(id);
      });
      navigationLink.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
          let id = navigationLink.dataset.id;
          openItem(id);
        }
      });
    }

    // Select2 dropdown.
    $('.content-menu__navigation-select').select2({
      minimumResultsForSearch: 20,
    });
    $('.content-menu__navigation-select').on('select2:select', function () {
      let id = $('.content-menu__navigation-select option:selected').attr('data-id')
      openItem(id);
    });

  });

  /**
   * Active the selected content menu item.
   */
  function openItem(id) {
    let links = document.querySelectorAll('.content-menu__navigation-link[data-id="' + id + '"]');
    const navigationSelect = document.querySelector('.content-menu__navigation-select');

    // Already active? Nothing to do here.
    if (!links[0].classList.contains('active')) {
      closeItems(id);
      let currentItem = document.querySelector('.content-menu__item-' + id);
      currentItem.classList.add('active');

      navigationSelect.value = id;
      for (let link of links) {
        link.classList.add('active');
        link.setAttribute('aria-selected', 'true');
      }
      $('.content-menu__navigation-select').trigger('change');
    }

  }

  /**
   * Close (all) active content menu items.
   *
   * @param currentId
   */
  function closeItems(currentId) {

    let activeLinks = document.querySelectorAll('.content-menu__navigation-link.active');
    const menuItems = document.querySelectorAll('.content-menu__item');

    if (activeLinks) {
      // Remove active class from links.
      for (let activeLink of activeLinks) {
        activeLink.classList.remove('active');
        activeLink.setAttribute('aria-selected', 'false');
      }
      // Remove active class from items.
      for (let menuItem of menuItems) {
        if (!menuItem.classList.contains('.content-menu__item-' + currentId)) {
          menuItem.classList.remove('active');
        }
      }
    }

  }

})(jQuery);
