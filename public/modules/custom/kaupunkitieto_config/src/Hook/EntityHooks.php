<?php

declare(strict_types=1);

namespace Drupal\kaupunkitieto_config\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\kaupunkitieto_config\Plugin\Validation\Constraint\EmbedConstraints;

/**
 * Entity hooks.
 */
class EntityHooks {

  /**
   * Implements hook_entity_bundle_field_info_alter().
   *
   * @param \Drupal\Core\Field\FieldConfigInterface[] $fields
   *   The bundle field definitions.
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   * @param string $bundle
   *   The bundle.
   */
  #[Hook('entity_bundle_field_info_alter')]
  public function entityBundleFieldInfoAlter(array &$fields, EntityTypeInterface $entity_type, string $bundle): void {
    if ($entity_type->id() !== 'paragraph' || $bundle !== 'embed') {
      return;
    }

    // Add constraints to embed paragraph fields.
    foreach ($fields as $name => $field) {
      if (array_key_exists($name, EmbedConstraints::EMBED_FIELDS)) {
        $field->addConstraint('EmbedConstraints', []);
      }
    }
  }

}
