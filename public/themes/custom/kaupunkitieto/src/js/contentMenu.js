document.addEventListener('DOMContentLoaded', function () {

  const navigationLinks = document.getElementsByClassName('content-menu__navigation-link');
  // Bind event for navigation links.
  for (let navigationLink of navigationLinks) {
    navigationLink.addEventListener('click', () => {
      openItem(navigationLink);
    });
    navigationLink.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        openItem(navigationLink);
      }
    });
  }

});

/**
 * Active the selected content menu item.
 */
function openItem(link) {

  // Already active? Nothing to do here.
  if (!link.classList.contains('active')) {
    let id = link.dataset.id;
    closeItems(id);
    let currentItem = document.querySelector('.content-menu__item-' + id);
    currentItem.classList.add('active');
    link.classList.add('active');
    link.setAttribute('aria-selected', 'true');
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
