<?php

declare(strict_types=1);

namespace Drupal\kaupunkitieto_config\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\helfi_platform_config\DTO\ParagraphTypeCollection;
use Drupal\kaupunkitieto_config\Entity\ArticleSeries;

/**
 * Paragraph hooks.
 */
class ParagraphHooks {

  /**
   * Implements hook_helfi_paragraph_types().
   *
   * @return \Drupal\helfi_platform_config\DTO\ParagraphTypeCollection[]
   *   Returns the enabled paragraph types.
   */
  #[Hook('helfi_paragraph_types')]
  public function helfiParagraphTypes(): array {
    return [
      new ParagraphTypeCollection('node', 'landing_page', 'field_content', 'article_series', 18),
    ];
  }

  /**
   * Implements hook_entity_bundle_info_alter().
   *
   * @param array<string, mixed> $bundles
   *   The entity bundles.
   */
  #[Hook('entity_bundle_info_alter')]
  public function entityBundleInfoAlter(array &$bundles): void {
    if (isset($bundles['paragraph']['article_series'])) {
      $bundles['paragraph']['article_series']['class'] = ArticleSeries::class;
    }
  }

}
