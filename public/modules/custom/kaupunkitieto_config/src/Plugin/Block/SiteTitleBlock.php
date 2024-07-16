<?php

declare(strict_types=1);

namespace Drupal\kaupunkitieto_config\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a site title block.
 *
 * @Block(
 *   id = "site_title_block",
 *   admin_label = @Translation("Site title"),
 * )
 */
class SiteTitleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() : array {
    return ['#markup' => ''];
  }

}
