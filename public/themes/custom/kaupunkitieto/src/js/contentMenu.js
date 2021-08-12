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
  // Bind keydown event to select.
  const navigationSelect = document.querySelector('.content-menu__navigation-select');
  navigationSelect.addEventListener('change', (event) => {
      let id = event.target.value;
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
      if (link.nodeName == 'A') {
        link.setAttribute('aria-selected', 'true');
      }
      if (link.nodeName == 'OPTION') {
        link.setAttribute('selected', 'true');
      }
    }
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
      if (activeLink.nodeName == 'A') {
        activeLink.setAttribute('aria-selected', 'false');
      }
      if (activeLink.nodeName == 'OPTION') {
        activeLink.removeAttribute('selected');
      }
    }
    // Remove active class from items.
    for (let menuItem of menuItems) {
      if (!menuItem.classList.contains('.content-menu__item-' + currentId)) {
        menuItem.classList.remove('active');
      }
    }
  }

}
