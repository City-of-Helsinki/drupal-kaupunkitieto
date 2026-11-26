<?php

declare(strict_types=1);

namespace Drupal\kaupunkitieto_config\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Form hooks.
 */
class FormHooks {

  /**
   * Implements hook_form_BASE_FORM_ID_alter().
   *
   * @param array<string, mixed> $form
   *   The form.
   */
  #[Hook('form_node_form_alter')]
  public function formNodeFormAlter(array &$form): void {
    // Attach content liftup library to node forms.
    $form['#attached']['library'][] = 'kaupunkitieto_config/admin-content-liftup';
  }

}
