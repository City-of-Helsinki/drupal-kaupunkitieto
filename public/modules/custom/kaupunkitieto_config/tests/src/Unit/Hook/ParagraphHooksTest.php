<?php

declare(strict_types=1);

namespace Drupal\Tests\kaupunkitieto_config\Unit\Hook;

use Drupal\helfi_platform_config\DTO\ParagraphTypeCollection;
use Drupal\kaupunkitieto_config\Entity\ArticleSeries;
use Drupal\kaupunkitieto_config\Hook\ParagraphHooks;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests paragraph hooks.
 */
#[Group('kaupunkitieto_config')]
class ParagraphHooksTest extends UnitTestCase {

  /**
   * Article series paragraph is enabled on landing page content.
   */
  public function testArticleSeriesIsEnabledOnLandingPage(): void {
    $this->assertEquals(
      [new ParagraphTypeCollection('node', 'landing_page', 'field_content', 'article_series', 18)],
      (new ParagraphHooks())->helfiParagraphTypes(),
      'Article series is enabled on landing page content.',
    );
  }

  /**
   * Article series paragraph uses the article series bundle class.
   */
  public function testArticleSeriesUsesBundleClass(): void {
    $bundles = [
      'paragraph' => [
        'article_series' => ['label' => 'Article series'],
        'text' => ['label' => 'Text'],
      ],
    ];

    (new ParagraphHooks())->entityBundleInfoAlter($bundles);

    $this->assertSame(ArticleSeries::class, $bundles['paragraph']['article_series']['class'], 'Article series uses the article series bundle class.');
    $this->assertArrayNotHasKey('class', $bundles['paragraph']['text'], 'Other paragraph types keep their class.');
  }

  /**
   * Missing article series bundle is not added.
   */
  public function testMissingArticleSeriesIsNotAdded(): void {
    $bundles = ['paragraph' => []];

    (new ParagraphHooks())->entityBundleInfoAlter($bundles);

    $this->assertSame(['paragraph' => []], $bundles, 'Missing article series bundle is not added.');
  }

}
