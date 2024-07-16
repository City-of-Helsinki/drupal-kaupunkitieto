(function ($) {
  /**
   * Function that shows and hides fields on the Content liftup Item paragraph
   * based on the active design.
   *
   * @param design
   * @param node
   * @param media
   */
  function toggleFields(design, node, media) {
    // Get the selected design.
    let selectedDesign = design.val();

    // Content: Content field is shown and media shown.
    if (selectedDesign === 'node') {
      node.show();
      media.hide();
    }

    // Media: Media field is shown and content hidden.
    else if (selectedDesign === 'media') {
      node.hide();
      media.show();
    }

  }

  /**
   * The function that goes through each paragraph that is type Content liftup
   * and toggles the fields using the designSelection function.
   */
  function loopThroughParagraphs() {
    // Find all the Content liftup paragraphs.
    let paragraphs = $('.paragraph-type--content-liftup-item');

    // Go through each paragraph.
    paragraphs.each(function () {
      // Find the design for the paragraph in question.
      let design = $(this).find(
        '.field--name-field-content-liftup-item-design select'
      );

      // Find the content and media of the Content liftup child paragraphs.
      let node = $(this).find('.field--name-field-content-liftup-item-node');
      let media = $(this).find('.field--name-field-content-liftup-item-media');

      // Run the toggle function for fields
      toggleFields(design, node, media);

      // On design value change, run the toggle function again for the fields.
      design.change(function () {
        toggleFields(design, node, media);
      });
    });
  }

  /**
   * Each time ajax is run on the node-edit form we need to also trigger the
   * toggle for the fields according to the design.
   */
  $(document).ajaxComplete(() => {
    loopThroughParagraphs();
  });

  /**
   * When the dom is loaded show & hide the elements according to the design.
   */
  $(document).ready(function () {
    loopThroughParagraphs();
  });
})(jQuery);
