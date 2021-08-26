(function ($) {

  document.addEventListener('DOMContentLoaded', function () {
    console.log('lesgo');

    const toggleButton = document.querySelector('.language-switcher__toggle .icon');
    // Bind event click event.
    toggleButton.addEventListener('click', () => {
      toggleMenu();
    });

  });

  /**
   * Toggle the language menu.
   */
  function toggleMenu() {
    console.log('click');

    const menu = document.querySelector('.language-switcher');
    menu.classList.toggle('open');
  }

})(jQuery);
