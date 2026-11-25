<?php

declare(strict_types=1);

namespace Drupal\kaupunkitieto_config\Entity;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Bundle class for article_series paragraph.
 */
class ArticleSeries extends Paragraph implements ParagraphInterface {

  /**
   * Get the list of articles in the selected series.
   *
   * @return array<string, mixed>
   *   Returns the article list render array.
   */
  public function getArticleList(): array {
    $seriesIds = [];
    foreach ($this->get('field_article_series') as $item) {
      $seriesIds[] = $item->get('target_id')->getValue();
    }

    // List all articles when no series is selected.
    return [
      '#type' => 'view',
      '#name' => 'article',
      '#display_id' => 'article_series',
      '#arguments' => $seriesIds ? [implode('+', $seriesIds)] : [],
    ];
  }

}
