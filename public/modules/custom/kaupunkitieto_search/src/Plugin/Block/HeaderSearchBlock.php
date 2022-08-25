<?php

/**
 * @file
 * Contains \Drupal\kaupunkitieto_search\Plugin\Block\HeaderSearchBlock.
 */

namespace Drupal\kaupunkitieto_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Header Search block
 *
 * @Block(
 *   id = "kaupunkitieto_search_header_search_block",
 *   admin_label = @Translation("Kaupunkitieto Header Search"),
 * )
 */
class HeaderSearchBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();

    switch ($langcode) {
      case "fi":
        $search_page_path = "/fi/haku";
        break;
      case "sv":
        $search_page_path = "/sv/haku";
        break;
      case "en":
        $search_page_path = "/en/haku";
        break;
      default:
        $search_page_path = "/fi/haku";
    }

    $build = [
      '#theme' => 'header_search',
      '#search_page_path' => $search_page_path
    ];

    return $build;
  }
}
