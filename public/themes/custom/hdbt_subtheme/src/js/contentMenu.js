((Drupal, $) => {
  // Close (all) active content menu items.
  const closeItems = (currentId) => {
    const activeLinks = document.querySelectorAll('.content-menu__navigation-link.active');
    const menuItems = document.querySelectorAll('.content-menu__item');

    if (activeLinks) {
      // Remove active class from links.
      [...activeLinks].forEach((activeLink) => {
        activeLink.classList.remove('active');
        activeLink.setAttribute('aria-selected', 'false');
      });
      // Remove active class from items.
      [...menuItems].forEach((menuItem) => {
        if (!menuItem.classList.contains(`.content-menu__item-${currentId}`)) {
          menuItem.classList.remove('active');
        }
      });
    }
  };

  // Active the selected content menu item.
  const openItem = (id) => {
    const links = document.querySelectorAll(`.content-menu__navigation-link[data-id="${id}"]`);
    const navigationSelect = document.querySelector('.content-menu__navigation-select');

    // Already active? Nothing to do here.
    if (!links[0].classList.contains('active')) {
      closeItems(id);
      const currentItem = document.querySelector(`.content-menu__item-${id}`);
      currentItem.classList.add('active');

      navigationSelect.value = id;

      [...links].forEach((link) => {
        link.classList.add('active');
        link.setAttribute('aria-selected', 'true');
      });
      $('.content-menu__navigation-select').trigger('change');
    }
  };

  // Attach table of contents.
  Drupal.behaviors.contentMenu = {
    attach() {
      const navigationLinks = document.getElementsByClassName('content-menu__navigation-link');

      // Bind event for navigation links.
      [...navigationLinks].forEach((navigationLink) => {
        navigationLink.addEventListener('click', () => {
          openItem(navigationLink.dataset.id);
        });
        navigationLink.addEventListener('keydown', (event) => {
          if (event.key === 'Enter') {
            openItem(navigationLink.dataset.id);
          }
        });
      });

      // Select2 dropdown.
      $('.content-menu__navigation-select')
        .select2({ minimumResultsForSearch: 20 })
        .on('select2:select', () => {
          openItem($('.content-menu__navigation-select option:selected').attr('data-id'));
        });
    },
  };
})(Drupal, jQuery);
