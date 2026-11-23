<?php

declare(strict_types=1);

namespace Drupal\kaupunkitieto_config\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Table of contents hooks.
 */
class TocHooks {

  /**
   * Implements hook_helfi_toc_forms_alter().
   *
   * @param string[] $whitelisted_forms
   *   The form IDs that allow table of contents.
   */
  #[Hook('helfi_toc_forms_alter')]
  public function helfiTocFormsAlter(array &$whitelisted_forms): void {
    // Allow table of contents on article nodes.
    $whitelisted_forms[] = 'node_article_form';
    $whitelisted_forms[] = 'node_article_edit_form';
  }

}
