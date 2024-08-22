(function ($) {

  document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.querySelector('.language-switcher__toggle');
    // Bind event click event.
    toggleButton.addEventListener('click', () => {
      toggleMenu(toggleButton);
    });
    toggleButton.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        toggleMenu(toggleButton);
      }
    });

  });

  /**
   * Toggle the language menu.
   */
  function toggleMenu() {
    const menu = document.querySelector('.language-switcher');
    menu.classList.toggle('open');

    const links = document.querySelector('.language-switcher .language-links');
    if (menu.classList.contains('open')) {
      links.setAttribute('aria-expanded', true);
    }
    else {
      links.setAttribute('aria-expanded', false);
    }

  }

})(jQuery);
