<?php

declare(strict_types=1);

namespace Drupal\Tests\kaupunkitieto_search\ExistingSite;

use Drupal\Core\Url;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests the header search toggle button and form markup.
 *
 * The button only works when the block is placed in the header branding
 * region and hdbt_subtheme_preprocess_html() has added the #search toggle
 * target and enabled HDBT's search dropdown JavaScript.
 *
 * Read-only: the test only loads pages and submits the GET search form, and
 * doesn't change the site. Runs without a browser, so HDBT's toggle
 * JavaScript itself is not executed.
 */
#[Group('kaupunkitieto_search')]
final class HeaderSearchTest extends ExistingSiteBase {

  /**
   * Provides the site languages.
   *
   * @return array<string, array{string}>
   *   The language codes.
   */
  public static function languageProvider(): array {
    return [
      'fi' => ['fi'],
      'sv' => ['sv'],
      'en' => ['en'],
    ];
  }

  /**
   * Tests the header search markup in each language.
   */
  #[DataProvider('languageProvider')]
  public function testHeaderSearchMarkup(string $langcode): void {
    $this->drupalGetFrontPage($langcode);
    $assert = $this->assertSession();

    // The toggle button is in the header branding search slot.
    $assert->elementExists('css', '.header-branding .nav-toggle--search button.js-search-button[aria-controls="search-dropdown"][aria-expanded="false"]');

    // The dropdown holds the site search form in the current language.
    $assert->elementAttributeContains('css', '#search-dropdown.js-search-dropdown form[role="search"]', 'action', "/$langcode/haku");
    $assert->elementExists('css', '#search-dropdown input#search-form-header[name="search_api_fulltext"]');
    $assert->elementExists('css', '#search-dropdown button[type="submit"]');

    // HDBT's toggle JavaScript and CSS need the target as a direct child of
    // the body, before the page wrapper.
    $assert->elementsCount('css', 'body > #search.nav-toggle-target', 1);
    $settings = $this->getDrupalSettings();
    $this->assertTrue($settings['hdbt']['search_dropdown'] ?? NULL, 'HDBT search dropdown is enabled.');

    // The old top bar menu link toggle is gone.
    $assert->elementNotExists('css', '.toggle-search-form');
  }

  /**
   * Tests that submitting the header search form searches the site.
   *
   * The form is a plain GET form, so it works without JavaScript too.
   */
  #[DataProvider('languageProvider')]
  public function testHeaderSearchSubmit(string $langcode): void {
    $this->drupalGetFrontPage($langcode);

    $page = $this->getSession()->getPage();
    $form = $page->find('css', '#search-dropdown form');
    $this->assertNotNull($form, 'Header search form exists.');
    $form->fillField('search_api_fulltext', 'asuminen helsinki');
    $form->find('css', 'button[type="submit"]')?->press();

    $assert = $this->assertSession();
    $assert->statusCodeEquals(200);
    $url = parse_url($this->getSession()->getCurrentUrl());
    $this->assertIsArray($url);
    $this->assertSame("/$langcode/haku", $url['path'] ?? NULL);
    parse_str($url['query'] ?? '', $query);
    $this->assertSame('asuminen helsinki', $query['search_api_fulltext'] ?? NULL);

    // The search page shows the search with the submitted keywords.
    $assert->elementAttributeContains('css', 'body', 'class', 'site-search');
    $assert->elementAttributeContains('css', 'input#edit-search-api-fulltext', 'value', 'asuminen helsinki');
  }

  /**
   * Opens the front page in the given language.
   */
  private function drupalGetFrontPage(string $langcode): void {
    $this->drupalGet(Url::fromRoute('<front>'), [
      'language' => \Drupal::languageManager()->getLanguage($langcode),
    ]);
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Returns the drupalSettings of the current page.
   *
   * @return array<string, mixed>
   *   The settings.
   */
  private function getDrupalSettings(): array {
    $element = $this->getSession()->getPage()->find('css', 'script[data-drupal-selector="drupal-settings-json"]');
    $this->assertNotNull($element, 'drupalSettings are present.');
    $settings = json_decode($element->getHtml(), TRUE);
    $this->assertIsArray($settings);
    return $settings;
  }

}
