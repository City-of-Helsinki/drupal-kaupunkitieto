<?php

declare(strict_types=1);

namespace Drupal\Tests\kaupunkitieto_config\Unit\Entity;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\kaupunkitieto_config\Entity\ArticleSeries;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the article series paragraph bundle class.
 */
#[Group('kaupunkitieto_config')]
class ArticleSeriesTest extends UnitTestCase {

  /**
   * Article list filters the articles by the selected series.
   *
   * @param int[] $seriesIds
   *   The selected series IDs.
   * @param string[] $arguments
   *   The expected view arguments.
   */
  #[DataProvider('seriesProvider')]
  public function testArticleListFiltersBySelectedSeries(array $seriesIds, array $arguments): void {
    $items = [];
    foreach ($seriesIds as $seriesId) {
      $targetId = $this->createMock(TypedDataInterface::class);
      $targetId->method('getValue')->willReturn($seriesId);

      $item = $this->createMock(FieldItemInterface::class);
      $item->method('get')->with('target_id')->willReturn($targetId);
      $items[] = $item;
    }

    $paragraph = $this->getMockBuilder(ArticleSeries::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['get'])
      ->getMock();
    $paragraph->method('get')->with('field_article_series')->willReturn($items);

    $this->assertSame(
      [
        '#type' => 'view',
        '#name' => 'article',
        '#display_id' => 'article_series',
        '#arguments' => $arguments,
      ],
      $paragraph->getArticleList(),
      'Article list uses the article series display with the selected series.',
    );
  }

  /**
   * Provides the selected series and the expected view arguments.
   *
   * @return array<string, array{int[], string[]}>
   *   The selected series IDs and the view arguments.
   */
  public static function seriesProvider(): array {
    return [
      'no series' => [[], []],
      'one series' => [[1], ['1']],
      'two series' => [[1, 2], ['1+2']],
    ];
  }

}
