<?php

declare(strict_types=1);

namespace Drupal\Tests\kaupunkitieto_config\ExistingSite;

use Drupal\kaupunkitieto_config\Entity\ArticleSeries;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\helfi_api_base\Functional\ExistingSiteTestBase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the article series paragraph.
 */
#[Group('kaupunkitieto_config')]
#[RunTestsInSeparateProcesses]
class ArticleSeriesTest extends ExistingSiteTestBase {

  /**
   * The article series terms keyed by name.
   *
   * @var \Drupal\taxonomy\TermInterface[]
   */
  protected array $series = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $vocabulary = Vocabulary::load('article_series');
    $this->assertInstanceOf(Vocabulary::class, $vocabulary, 'Article series vocabulary exists.');

    foreach (['first', 'second'] as $name) {
      $this->series[$name] = $this->createTerm($vocabulary, [
        'name' => $this->randomMachineName(),
        'langcode' => 'fi',
      ]);
    }

    $this->createNode([
      'type' => 'article',
      'title' => 'Article in first series',
      'langcode' => 'fi',
      'field_article_series' => $this->series['first']->id(),
    ]);
    $this->createNode([
      'type' => 'article',
      'title' => 'Article in second series',
      'langcode' => 'fi',
      'field_article_series' => $this->series['second']->id(),
    ]);
    $this->createNode([
      'type' => 'article',
      'title' => 'Article without series',
      'langcode' => 'fi',
    ]);
  }

  /**
   * Lists the articles of the selected series.
   *
   * @param string[] $seriesNames
   *   The selected series.
   * @param string[] $listed
   *   The article titles that are listed.
   * @param string[] $notListed
   *   The article titles that are not listed.
   */
  #[DataProvider('seriesProvider')]
  public function testListsArticlesOfSelectedSeries(array $seriesNames, array $listed, array $notListed): void {
    $seriesIds = [];
    foreach ($seriesNames as $name) {
      $seriesIds[] = $this->series[$name]->id();
    }

    $paragraph = Paragraph::create([
      'type' => 'article_series',
      'langcode' => 'fi',
      'field_title' => 'Series listing',
      'field_description' => 'Series description',
      'field_article_series' => $seriesIds,
    ]);
    $paragraph->save();
    $this->markEntityForCleanup($paragraph);
    $this->assertInstanceOf(ArticleSeries::class, $paragraph, 'Article series paragraph uses the article series bundle class.');

    $landingPage = $this->createNode([
      'type' => 'landing_page',
      'title' => $this->randomMachineName(),
      'langcode' => 'fi',
      'status' => 1,
      'field_content' => [$paragraph],
    ]);

    $this->drupalGet($landingPage->toUrl());

    $assert = $this->assertSession();
    $assert->elementTextContains('css', '.component--article-series .component__title', 'Series listing');
    $assert->elementTextContains('css', '.component--article-series .component__description', 'Series description');

    foreach ($listed as $title) {
      $assert->elementTextContains('css', '.views--article.views--article-series', $title);
    }
    foreach ($notListed as $title) {
      $assert->elementTextNotContains('css', '.views--article.views--article-series', $title);
    }
  }

  /**
   * Provides the selected series and the expected articles.
   *
   * @return array<string, array{string[], string[], string[]}>
   *   The selected series, listed articles and not listed articles.
   */
  public static function seriesProvider(): array {
    return [
      'first series' => [
        ['first'],
        ['Article in first series'],
        ['Article in second series', 'Article without series'],
      ],
      'second series' => [
        ['second'],
        ['Article in second series'],
        ['Article in first series', 'Article without series'],
      ],
      'both series' => [
        ['first', 'second'],
        ['Article in first series', 'Article in second series'],
        ['Article without series'],
      ],
      'no series' => [
        [],
        ['Article in first series', 'Article in second series', 'Article without series'],
        [],
      ],
    ];
  }

}
