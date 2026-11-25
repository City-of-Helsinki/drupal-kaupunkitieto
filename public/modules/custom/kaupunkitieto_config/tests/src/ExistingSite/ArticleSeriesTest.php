<?php

declare(strict_types=1);

namespace Drupal\Tests\kaupunkitieto_config\ExistingSite;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\helfi_api_base\Functional\ExistingSiteTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the article series paragraph.
 */
#[Group('kaupunkitieto_config')]
#[RunTestsInSeparateProcesses]
class ArticleSeriesTest extends ExistingSiteTestBase {

  /**
   * Only articles of the selected series are listed.
   */
  public function testListsArticlesOfSelectedSeries(): void {
    $vocabulary = Vocabulary::load('article_series');
    $series = $this->createTerm($vocabulary, ['name' => 'Test series', 'langcode' => 'fi']);
    $otherSeries = $this->createTerm($vocabulary, ['name' => 'Other test series', 'langcode' => 'fi']);

    $this->createNode([
      'type' => 'article',
      'title' => 'Article in series',
      'langcode' => 'fi',
      'field_article_series' => $series->id(),
    ]);
    $this->createNode([
      'type' => 'article',
      'title' => 'Article in other series',
      'langcode' => 'fi',
      'field_article_series' => $otherSeries->id(),
    ]);
    $this->createNode([
      'type' => 'article',
      'title' => 'Article outside series',
      'langcode' => 'fi',
    ]);

    $paragraph = Paragraph::create([
      'type' => 'article_series',
      'langcode' => 'fi',
      'field_title' => 'Series listing',
      'field_description' => 'Series description',
      'field_article_series' => [$series->id(), $otherSeries->id()],
    ]);
    $paragraph->save();
    $this->markEntityForCleanup($paragraph);

    $landingPage = $this->createNode([
      'type' => 'landing_page',
      'title' => 'Landing page',
      'langcode' => 'fi',
      'field_content' => [$paragraph],
    ]);

    $this->drupalGet($landingPage->toUrl());

    $assert = $this->assertSession();
    $assert->elementTextContains('css', '.component--article-series .component__title', 'Series listing');
    $assert->elementTextContains('css', '.component--article-series .component__description', 'Series description');
    $assert->elementTextContains('css', '.views--article.views--article-series', 'Article in series');
    $assert->elementTextContains('css', '.views--article.views--article-series', 'Article in other series');
    $assert->elementTextNotContains('css', '.views--article.views--article-series', 'Article outside series');
  }

}
